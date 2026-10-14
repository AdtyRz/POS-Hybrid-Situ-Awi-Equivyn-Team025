<?php

namespace App\Http\Controllers;

use App\Events\PanggilanPelayan;
use App\Events\PesananBaru;
use App\Events\SesiMejaBerubah;
use App\Events\StatusPesananBerubah;
use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\StokMutasi;
use App\Services\MidtransService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Str;

class EMenuController extends Controller
{
    public function beranda(): View
    {
        return view('emenu.beranda');
    }

    public function aplikasi(string $meja): View
    {
        $mejaMakan = $this->cariMeja($meja);

        $menuJson = Menu::orderBy('nama_menu')->get()->map(function ($menu) {
            return [
                'id' => $menu->id,
                'name' => $menu->nama_menu,
                'price' => (float) $menu->harga,
                'desc' => $menu->deskripsi ?? '',
                'category' => $menu->kategori_id,
                'image' => $menu->foto_menu ? asset('storage/' . $menu->foto_menu) : '',
                'isSoldOut' => $menu->stok_menu <= 0,
                'badge' => $menu->stok_menu <= 0 ? 'Stok Habis' : '',
                'badgeType' => $menu->stok_menu <= 0 ? 'soldout' : '',
            ];
        })->values();

        $kategoriJson = collect([['id' => 'semua', 'name' => 'Semua']])->concat(
            Kategori::orderBy('id')->get()->map(function ($kategori) {
                return ['id' => $kategori->id, 'name' => $kategori->nama_kategori];
            })
        )->values();

        $inisial = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $mejaMakan->kode_meja), 0, 2));

        $riwayat = session('riwayat_meja_' . $mejaMakan->id, []);

        return view('pelanggan.index', [
            'meja' => $mejaMakan,
            'menuJson' => $menuJson,
            'kategoriJson' => $kategoriJson,
            'orderRef' => '#' . $inisial . '-' . $mejaMakan->id . '04',
            'punyaRiwayat' => count($riwayat) > 0,
        ]);
    }

    public function checkout(Request $request, string $meja): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        $data = $request->validate([
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['integer', 'min:0', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
            'metode' => ['required', 'in:tunai,qris'],
        ]);

        $dipilih = collect($data['jumlah'])->filter(fn ($jumlah) => $jumlah > 0);

        if ($dipilih->isEmpty()) {
            return back()->withErrors(['jumlah' => 'Pilih minimal satu menu.'])->withInput();
        }

        $daftarMenu = Menu::whereIn('id', $dipilih->keys())->get()->keyBy('id');

        foreach ($dipilih as $id => $jumlah) {
            if (! isset($daftarMenu[$id]) || $daftarMenu[$id]->stok_menu < $jumlah) {
                $nama = isset($daftarMenu[$id]) ? $daftarMenu[$id]->nama_menu : 'menu';
                return back()->withErrors(['jumlah' => 'Stok ' . $nama . ' tidak cukup.'])->withInput();
            }
        }

        $pesanan = DB::transaction(function () use ($mejaMakan, $daftarMenu, $dipilih, $data) {
            $subtotal = 0;

            foreach ($dipilih as $id => $jumlah) {
                $subtotal += $daftarMenu[$id]->harga * $jumlah;
            }

            $totalBayar = $subtotal + round($subtotal * 0.1);
            $kodePesanan = 'SW-' . now()->format('YmdHis') . '-' . $mejaMakan->id;
            $kodePembayaran = 'PAY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));

            // Determine initial status based on payment method
            if ($data['metode'] === 'qris') {
                // QRIS: Auto-verify via callback, status pending verification
                $statusPesanan = Pesanan::STATUS_MENUNGGU_VERIFIKASI;
                $statusPembayaran = Pesanan::PAYMENT_PENDING_VERIFIKASI;
                $metodePembayaran = 'qris';
            } else {
                // Cash: Pay first at kasir, generate payment code
                $statusPesanan = Pesanan::STATUS_MENUNGGU_PEMBAYARAN;
                $statusPembayaran = Pesanan::PAYMENT_PENDING_PEMBAYARAN;
                $metodePembayaran = 'tunai';
            }

            $pesanan = Pesanan::create([
                'kode_pesanan' => $kodePesanan,
                'kode_pembayaran' => $kodePembayaran,
                'meja_id' => $mejaMakan->id,
                'total_bayar' => $totalBayar,
                'metode_pembayaran' => $metodePembayaran,
                'status_pesanan' => $statusPesanan,
                'status_pembayaran' => $statusPembayaran,
            ]);

            foreach ($dipilih as $id => $jumlah) {
                $menu = $daftarMenu[$id];

                $pesanan->detailPesanan()->create([
                    'menu_id' => $menu->id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $menu->harga * $jumlah,
                    'catatan' => $data['catatan'][$id] ?? null,
                    'status_item' => 'menunggu',
                ]);

                $menu->decrement('stok_menu', $jumlah);

                StokMutasi::create([
                    'menu_id' => $menu->id,
                    'pesanan_id' => $pesanan->id,
                    'jenis' => 'penjualan',
                    'jumlah_delta' => -$jumlah,
                    'stok_akhir' => $menu->fresh()->stok_menu,
                ]);
            }

            $mejaMakan->update(['status_meja' => 'terisi']);

            return $pesanan;
        });

        $request->session()->push('riwayat_meja_' . $mejaMakan->id, $pesanan->kode_pesanan);

        // Redirect based on payment method
        if ($data['metode'] === 'qris') {
            // QRIS: Show waiting for verification page, will auto-redirect after callback
            return redirect()->route('emenu.verifikasi', $pesanan->kode_pesanan);
        } else {
            // Cash: Show payment code page for customer to give to kasir
            return redirect()->route('emenu.bayar-tunai', $pesanan->kode_pesanan);
        }
    }

    /**
     * Tampilkan form checkout (GET).
     * Route: GET /meja/{meja}/checkout
     */
    public function checkoutForm(string $meja): View
    {
        $mejaMakan = $this->cariMeja($meja);
        return view('emenu.checkout', ['meja' => $mejaMakan]);
    }

    public function status(string $kode_pesanan): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where('kode_pesanan', $kode_pesanan)
            ->firstOrFail();

        $tahap = [
            'menunggu' => 1,
            'diproses' => 2,
            'siap' => 3,
            'diantar' => 4,
            'selesai' => 5,
        ][$pesanan->status_pesanan] ?? 0;

        // Bila pelanggan membuka halaman status tanpa membayar di halaman verifikasi,
        // tampilkan QRIS di sini agar tetap bisa membayar. QR disembunyikan begitu lunas.
        $qris = null;
        $qrisTtl = (int) config('midtrans.qris_expiry_minutes', 5) * 60;

        if ($pesanan->metode_pembayaran === 'qris'
            && $pesanan->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR
            && $pesanan->status_pesanan !== 'dibatalkan') {
            $qris = app(MidtransService::class)->createQrisCharge($pesanan);
        }

        return view('emenu.status', compact('pesanan', 'tahap', 'qris', 'qrisTtl'));
    }

    public function pelacakan(string $meja): View|RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        if (! $this->sesiTerverifikasi($mejaMakan)) {
            return $this->kunciRedirect($mejaMakan);
        }

        $kodeRiwayat = array_reverse(session('riwayat_meja_' . $mejaMakan->id, []));

        $daftarPesanan = Pesanan::with(['detailPesanan.menu'])
            ->where('meja_id', $mejaMakan->id)
            ->whereIn('kode_pesanan', $kodeRiwayat)
            ->get()
            ->sortBy(fn ($pesanan) => array_search($pesanan->kode_pesanan, $kodeRiwayat))
            ->values();

        return view('emenu.pelacakan', [
            'meja' => $mejaMakan,
            'daftarPesanan' => $daftarPesanan,
            'tahapStatus' => [
                'menunggu' => 1,
                'menunggu_verifikasi' => 1,
                'menunggu_pembayaran' => 1,
                'diproses' => 2,
                'siap' => 3,
                'diantar' => 4,
                'selesai' => 5,
                'dibatalkan' => 0,
            ],
        ]);
    }

    public function verifikasi(string $kode_pesanan): View|RedirectResponse
    {
        $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode_pesanan)->firstOrFail();

        // Kalau sudah lunas (mis. halaman di-refresh), langsung ke status pesanan.
        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return redirect()->route('emenu.status', $pesanan->kode_pesanan);
        }

        // Generate QRIS QR Code via Midtrans untuk ditampilkan di halaman pelanggan.
        $qris = app(MidtransService::class)->createQrisCharge($pesanan);

        // Perkiraan masa berlaku QR (untuk countdown di UI). Refresh sesungguhnya
        // terjadi saat Midtrans menandai transaksi expire.
        $qrisTtl = (int) config('midtrans.qris_expiry_minutes', 5) * 60;

        return view('emenu.verifikasi', compact('pesanan', 'qris', 'qrisTtl'));
    }

    /**
     * Cek status pembayaran QRIS dari Midtrans (dipanggil polling oleh halaman pelanggan).
     * Bila sukses, pesanan otomatis jadi lunas dan masuk dapur.
     */
    public function qrisStatus(string $kode_pesanan): JsonResponse
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode_pesanan)->firstOrFail();

        if ($pesanan->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR) {
            $midtrans = app(MidtransService::class);
            $status = $midtrans->getTransactionStatus($kode_pesanan);

            $transactionStatus = strtolower($status->transaction_status ?? '');
            $fraudStatus = strtolower($status->fraud_status ?? '');

            $sukses = $transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

            if ($sukses) {
                DB::transaction(function () use ($pesanan, $status) {
                    $pesanan->update([
                        'status_pesanan' => Pesanan::STATUS_DIPROSES,
                        'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                        'metode_pembayaran' => 'qris',
                    ]);

                    if (! $pesanan->pembayaran) {
                        $pesanan->pembayaran()->create([
                            'metode_pembayaran' => 'qris',
                            'status_pembayaran' => 'lunas',
                            'jumlah_bayar' => (float) $pesanan->total_bayar,
                            'kembalian' => 0,
                            'transaction_id' => $status->transaction_id ?? null,
                            'order_id' => $pesanan->kode_pesanan,
                        ]);
                    }
                });

                $pesanan->refresh();
                Realtime::emit(new StatusPesananBerubah([
                    'kode_pesanan' => $pesanan->kode_pesanan,
                    'meja' => $pesanan->meja->kode_meja ?? null,
                    'status_pesanan' => $pesanan->status_pesanan,
                    'status_pembayaran' => $pesanan->status_pembayaran,
                    'metode_pembayaran' => $pesanan->metode_pembayaran,
                ]));
            } elseif ($transactionStatus === 'expire') {
                // QR kedaluwarsa → buat QR baru. Order_id yang sudah expire boleh dipakai ulang.
                $qris = $midtrans->createQrisCharge($pesanan, true);
                $pesanan->refresh();

                return response()->json([
                    'lunas' => false,
                    'expired' => true,
                    'status_pesanan' => $pesanan->status_pesanan,
                    'status_pembayaran' => $pesanan->status_pembayaran,
                    'qr_url' => $qris['qr_url'] ?? null,
                    'expiry_time' => $qris['expiry_time'] ?? null,
                ]);
            }

            $pesanan->refresh();
        }

        return response()->json([
            'lunas' => $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
        ]);
    }

    public function bayarTunai(string $kode_pesanan): View
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode_pesanan)->firstOrFail();

        return view('emenu.bayar-tunai', compact('pesanan'));
    }

    public function qrisCallback(Request $request): JsonResponse
    {
        // Simulate Midtrans callback for QRIS payment
        $kodePesanan = $request->input('kode_pesanan') ?? $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');

        $pesanan = Pesanan::where('kode_pesanan', $kodePesanan)->first();

        if (! $pesanan) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return response()->json(['status' => 'success', 'message' => 'Pembayaran sudah diproses']);
        }

        // Simulate successful payment
        if (in_array($transactionStatus, ['settlement', 'capture']) || $request->has('test_success')) {
            DB::transaction(function () use ($pesanan) {
                $pesanan->update([
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'metode_pembayaran' => 'qris',
                ]);

                // Create payment record
                \App\Models\Pembayaran::create([
                    'pesanan_id' => $pesanan->id,
                    'metode_pembayaran' => 'qris',
                    'status_pembayaran' => 'lunas',
                    'jumlah_bayar' => $pesanan->total_bayar,
                    'kembalian' => 0,
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Pembayaran QRIS berhasil']);
        }

        return response()->json(['status' => 'pending', 'message' => 'Menunggu pembayaran']);
    }

    public function panggil(string $meja): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        LogPanggilPelayan::create([
            'meja_id' => $mejaMakan->id,
            'status_panggilan' => 'menunggu',
        ]);

        return back()->with('panggilan_terkirim', true);
    }

    private function cariMeja(string $meja): MejaMakan
    {
        if (is_numeric($meja)) {
            return MejaMakan::findOrFail($meja);
        }

        return MejaMakan::where('kode_meja', $meja)->firstOrFail();
    }
}
