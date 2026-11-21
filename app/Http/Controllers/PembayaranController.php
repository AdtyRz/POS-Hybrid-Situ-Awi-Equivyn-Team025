<?php

namespace App\Http\Controllers;

use App\Events\SesiMejaBerubah;
use App\Events\StatusPesananBerubah;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Services\MidtransService;
use App\Services\StokService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class PembayaranController extends Controller
{
    public function create(Request $request, string $kode): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        $jumlahBayar = (float) $request->old('jumlah_bayar', 0);
        $kembalian = 0;

        if ($jumlahBayar > 0) {
            $kembalian = max(0, $jumlahBayar - (float) $pesanan->total_bayar);
        }

        return view('kasir.pembayaran.bayar', [
            'pesanan' => $pesanan,
            'kembalian' => $kembalian,
            'transfer' => $this->pengaturanTransfer(),
        ]);
    }

    public function cari(Request $request): View
    {
        $kode = trim((string) ($request->input('kode_pembayaran') ?? $request->input('kode_pesanan')));

        if ($kode === '') {
            return $this->tampilCari(['error' => 'Masukkan kode meja, nama pemesan, kode pesanan, atau kode pembayaran.']);
        }

        $kunci = mb_strtolower($kode);

        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where(function ($q) use ($kunci) {
                $q->whereRaw('LOWER(kode_pembayaran) = ?', [$kunci])
                    ->orWhereRaw('LOWER(kode_pesanan) = ?', [$kunci]);
            })
            ->first();

        if ($pesanan) {
            if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                return $this->tampilCari(['error' => 'Pesanan ini sudah dibayar', 'pesanan' => $pesanan]);
            }

            return $this->tampilCari(compact('pesanan'));
        }

        $pola = '%' . $kunci . '%';

        $daftarPesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where(function ($q) use ($pola) {
                $q->whereRaw('LOWER(nama_pelanggan) LIKE ?', [$pola])
                    ->orWhereHas('meja', fn ($meja) => $meja->whereRaw('LOWER(kode_meja) LIKE ?', [$pola]));
            })
            ->whereIn('status_pembayaran', [
                Pesanan::PAYMENT_BELUM_BAYAR,
                Pesanan::PAYMENT_PENDING_PEMBAYARAN,
                Pesanan::PAYMENT_PENDING_VERIFIKASI,
            ])
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->orderByDesc('waktu_pesan')
            ->limit(30)
            ->get();

        if ($daftarPesanan->count() === 1) {
            $pesanan = $daftarPesanan->first();

            if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                return $this->tampilCari(['error' => 'Pesanan ini sudah dibayar', 'pesanan' => $pesanan]);
            }

            return $this->tampilCari(compact('pesanan'));
        }

        if ($daftarPesanan->count() > 1) {
            return $this->tampilCari(compact('daftarPesanan', 'kode'));
        }

        return $this->tampilCari(['error' => 'Pesanan tidak ditemukan', 'kode' => $kode]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'kode_pesanan' => ['required', 'exists:pesanan,kode_pesanan'],
            'metode_pembayaran' => ['required', 'in:tunai,qris,transfer'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'selesaikan_sesi' => ['nullable', 'boolean'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $data['kode_pesanan'])->firstOrFail();

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return $this->galatStore($request, 'kode_pesanan', 'Pesanan ini sudah dibayar.');
        }

        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return $this->galatStore($request, 'kode_pesanan', 'Pesanan ini sudah dibatalkan.');
        }

        if ($data['metode_pembayaran'] === 'transfer' && $pesanan->metode_pembayaran !== 'transfer') {
            return $this->galatStore($request, 'metode_pembayaran', 'Pesanan ini tidak memakai metode transfer.');
        }

        if ($data['metode_pembayaran'] !== 'transfer' && $pesanan->metode_pembayaran === 'transfer') {
            return $this->galatStore($request, 'metode_pembayaran', 'Pembayaran transfer wajib dikonfirmasi dengan metode transfer.');
        }

        if ($data['metode_pembayaran'] !== 'transfer' && $data['jumlah_bayar'] < (float) $pesanan->total_bayar) {
            return $this->galatStore($request, 'jumlah_bayar', 'Uang yang dibayar kurang dari total tagihan.');
        }

        $midtrans = app(MidtransService::class);
        $statusMidtrans = null;

        if ($data['metode_pembayaran'] === 'qris') {


            $statusMidtrans = $midtrans->getTransactionStatus($pesanan->kode_pesanan);

            if (! $midtrans->verifikasiQris($pesanan, $statusMidtrans)) {
                return $this->galatStore(
                    $request,
                    'metode_pembayaran',
                    'Pembayaran QRIS belum terverifikasi oleh Midtrans. Minta tamu menyelesaikan pembayaran terlebih dahulu.'
                );
            }
        }

        $transactionId = filled(data_get($statusMidtrans, 'transaction_id'))
            ? trim((string) data_get($statusMidtrans, 'transaction_id'))
            : null;

        $jumlahBayar = in_array($data['metode_pembayaran'], ['qris', 'transfer'], true)
            ? (float) $pesanan->total_bayar
            : (float) $data['jumlah_bayar'];
        $kembalian = in_array($data['metode_pembayaran'], ['qris', 'transfer'], true)
            ? 0
            : $jumlahBayar - (float) $pesanan->total_bayar;

        try {
            DB::transaction(function () use ($pesanan, $data, $jumlahBayar, $kembalian, $transactionId) {
                $terkunci = Pesanan::query()->whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

                if ($terkunci->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                    throw new RuntimeException('Pesanan ini sudah dibayar.');
                }

                if ($terkunci->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
                    throw new RuntimeException('Pesanan ini sudah dibatalkan.');
                }

                app(StokService::class)->potongSetelahPembayaran($terkunci, auth()->id());

                Pembayaran::create([
                    'pesanan_id' => $terkunci->id,
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'status_pembayaran' => 'lunas',
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'transaction_id' => $data['metode_pembayaran'] === 'qris' ? $transactionId : null,
                    'order_id' => $data['metode_pembayaran'] === 'qris' ? $terkunci->kode_pesanan : null,
                    'waktu_bayar' => now(),
                ]);



                $terkunci->update([
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'kasir_id' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $exception) {
            return $this->galatStore($request, 'kode_pesanan', $exception->getMessage());
        }

        $pesanan->refresh();

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja->kode_meja ?? null,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));



        if ($request->boolean('selesaikan_sesi') && $pesanan->meja) {
            $meja = $pesanan->meja;

            try {
                SesiMeja::putuskanSesi($meja);
            } catch (\RuntimeException $exception) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()
                        ->json([
                            'success' => false,
                            'message' => $exception->getMessage(),
                            'errors' => ['sesi' => [$exception->getMessage()]],
                        ], 422)
                        ->header('Cache-Control', 'no-store, max-age=0');
                }

                return redirect()
                    ->route('kasir.dashboard', ['meja' => $meja->kode_meja])
                    ->with('error', $exception->getMessage());
            }

            Realtime::emit(new SesiMejaBerubah(
                $meja->kode_meja,
                $meja->id,
                'selesai',
            ));
        }

        $pesanSukses = $data['metode_pembayaran'] === 'transfer'
            ? 'Transfer dikonfirmasi. Pesanan lunas dan diteruskan ke dapur.'
            : 'Pembayaran lunas. Cetak struk dapat dilakukan dari tombol Cetak Struk.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()
                ->json([
                    'success' => true,
                    'message' => $pesanSukses,
                    'kode_pesanan' => $pesanan->kode_pesanan,
                    'meja' => $pesanan->meja?->kode_meja,
                    'status_pesanan' => $pesanan->status_pesanan,
                    'status_pembayaran' => $pesanan->status_pembayaran,
                    'metode_pembayaran' => $pesanan->metode_pembayaran,
                ])
                ->header('Cache-Control', 'no-store, max-age=0');
        }

        return redirect()
            ->route('kasir.dashboard', $pesanan->meja ? ['meja' => $pesanan->meja->kode_meja] : [])
            ->with('sukses', $pesanSukses);
    }

    private function galatStore(Request $request, string $field, string $pesan): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()
                ->json([
                    'success' => false,
                    'message' => $pesan,
                    'errors' => [$field => [$pesan]],
                ], 422)
                ->header('Cache-Control', 'no-store, max-age=0');
        }

        return back()->withErrors([$field => $pesan])->withInput();
    }





    public function tandaiStruk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meja_id' => ['required', 'integer', 'exists:meja_makan,id'],
            'kode_pesanan' => ['required', 'array', 'min:1'],
            'kode_pesanan.*' => ['string', 'max:30'],
        ]);

        $query = Pesanan::query()
            ->where('meja_id', $data['meja_id'])
            ->whereIn('kode_pesanan', $data['kode_pesanan'])
            ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->whereNull('printed_at');

        $jumlah = (clone $query)->update([
            'printed_at' => now(),
            'updated_at' => now(),
        ]);



        Pesanan::query()
            ->where('meja_id', $data['meja_id'])
            ->whereIn('kode_pesanan', $data['kode_pesanan'])
            ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->whereNull('kasir_id')
            ->update([
                'kasir_id' => auth()->id(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'jumlah' => $jumlah,
        ]);
    }

    private function tampilCari(array $data = []): View
    {
        return view('kasir.pembayaran.cari', array_merge([
            'transfer' => $this->pengaturanTransfer(),
        ], $data));
    }

    private function pengaturanTransfer(): array
    {
        return [
            'bank' => (string) Pengaturan::nilai('transfer_bank', 'BCA'),
            'nomor' => (string) Pengaturan::nilai('transfer_nomor_rekening', ''),
            'atas_nama' => (string) Pengaturan::nilai('transfer_atas_nama', 'Saung Situ Awi'),
        ];
    }
}
