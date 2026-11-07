<?php

namespace App\Http\Controllers;

use App\Events\SesiMejaBerubah;
use App\Events\StatusPesananBerubah;
use App\Models\Pembayaran;
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

        return view('kasir.pembayaran.bayar', compact('pesanan', 'kembalian'));
    }

    public function cari(Request $request): View
    {
        $kode = trim((string) ($request->input('kode_pembayaran') ?? $request->input('kode_pesanan')));

        if ($kode === '') {
            return view('kasir.pembayaran.cari', ['error' => 'Masukkan kode meja, nama pemesan, kode pesanan, atau kode pembayaran.']);
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
                return view('kasir.pembayaran.cari', ['error' => 'Pesanan ini sudah dibayar', 'pesanan' => $pesanan]);
            }

            return view('kasir.pembayaran.cari', compact('pesanan'));
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
                return view('kasir.pembayaran.cari', ['error' => 'Pesanan ini sudah dibayar', 'pesanan' => $pesanan]);
            }

            return view('kasir.pembayaran.cari', compact('pesanan'));
        }

        if ($daftarPesanan->count() > 1) {
            return view('kasir.pembayaran.cari', compact('daftarPesanan', 'kode'));
        }

        return view('kasir.pembayaran.cari', ['error' => 'Pesanan tidak ditemukan', 'kode' => $kode]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_pesanan' => ['required', 'exists:pesanan,kode_pesanan'],
            'metode_pembayaran' => ['required', 'in:tunai,qris'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'selesaikan_sesi' => ['nullable', 'boolean'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $data['kode_pesanan'])->firstOrFail();

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return back()->withErrors(['kode_pesanan' => 'Pesanan ini sudah dibayar.']);
        }

        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return back()->withErrors(['kode_pesanan' => 'Pesanan ini sudah dibatalkan.']);
        }

        if ($data['jumlah_bayar'] < (float) $pesanan->total_bayar) {
            return back()->withErrors(['jumlah_bayar' => 'Uang yang dibayar kurang dari total tagihan.'])->withInput();
        }

        $midtrans = app(MidtransService::class);
        $statusMidtrans = null;

        if ($data['metode_pembayaran'] === 'qris') {


            $statusMidtrans = $midtrans->getTransactionStatus($pesanan->kode_pesanan);

            if (! $midtrans->verifikasiQris($pesanan, $statusMidtrans)) {
                return back()->withErrors([
                    'metode_pembayaran' => 'Pembayaran QRIS belum terverifikasi oleh Midtrans. Minta tamu menyelesaikan pembayaran terlebih dahulu.',
                ])->withInput();
            }
        }

        $transactionId = filled(data_get($statusMidtrans, 'transaction_id'))
            ? trim((string) data_get($statusMidtrans, 'transaction_id'))
            : null;

        $jumlahBayar = $data['metode_pembayaran'] === 'qris'
            ? (float) $pesanan->total_bayar
            : (float) $data['jumlah_bayar'];
        $kembalian = $data['metode_pembayaran'] === 'qris'
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
                ]);



                $terkunci->update([
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'metode_pembayaran' => $data['metode_pembayaran'],
                    'kasir_id' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $exception) {
            return back()->withErrors(['kode_pesanan' => $exception->getMessage()])->withInput();
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

        return redirect()
            ->route('kasir.dashboard', $pesanan->meja ? ['meja' => $pesanan->meja->kode_meja] : [])
            ->with('sukses', 'Pembayaran lunas. Cetak struk dapat dilakukan dari tombol Cetak Struk.');
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
}
