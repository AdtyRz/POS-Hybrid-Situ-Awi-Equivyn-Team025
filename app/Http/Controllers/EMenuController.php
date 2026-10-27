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
use App\Models\Pengaturan;
use App\Models\SesiMeja;
use App\Services\MidtransService;
use App\Services\PesananService;
use App\Services\StokService;
use App\Support\AksesPesanan;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EMenuController extends Controller
{
    public function beranda(): View
    {
        return view('emenu.beranda');
    }

    /**
     * Halaman kunci: pengunjung memasukkan kode unik yang tampil di LCD meja.
     */
    public function kunci(string $meja): View
    {
        $mejaMakan = $this->cariMeja($meja);
        $sesi = SesiMeja::aktifUntuk($mejaMakan);

        return view('emenu.kunci', [
            'meja' => $mejaMakan,
            'panjangKode' => strlen($sesi->kode_unik),
        ]);
    }

    /**
     * Validasi kode unik sesi. Bila benar, tandai sesi sebagai terverifikasi
     * di session pelanggan lalu arahkan ke halaman e-menu.
     */
    public function buka(Request $request, string $meja): RedirectResponse|JsonResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        $data = $request->validate([
            'kode_unik' => ['required', 'string'],
        ]);

        $sesi = SesiMeja::aktifUntuk($mejaMakan);

        if (! hash_equals($sesi->kode_unik, trim($data['kode_unik']))) {
            $pesan = 'Kode tidak sesuai dengan yang tampil di layar meja.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $pesan,
                    'errors' => ['kode_unik' => [$pesan]],
                ], 422);
            }

            return back()->withErrors([
                'kode_unik' => $pesan,
            ]);
        }

        $kunciSesi = 'sesi_meja_' . $mejaMakan->id;
        $sesiSebelumnya = $request->session()->get($kunciSesi);

        // Jangan bawa riwayat tamu sebelumnya ke sesi baru.
        if ($sesiSebelumnya !== null && (int) $sesiSebelumnya !== (int) $sesi->id) {
            $request->session()->forget('riwayat_meja_' . $mejaMakan->id);
        }

        // Cegah session fixation setelah pelanggan membuktikan kode saung.
        $request->session()->regenerate();
        $request->session()->put($kunciSesi, $sesi->id);

        $tujuan = route('emenu.meja', $mejaMakan->kode_meja);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $tujuan,
            ]);
        }

        return redirect()->to($tujuan);
    }

    public function aplikasi(string $meja): View|RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        if (! $this->sesiTerverifikasi($mejaMakan)) {
            return $this->kunciRedirect($mejaMakan);
        }

        $sesi = SesiMeja::aktifUntuk($mejaMakan);

        $menuJson = Menu::where('is_aktif', true)->orderBy('nama_menu')->get()->map(function ($menu) {
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
            Kategori::untukMenu()->orderBy('id')->get()->map(function ($kategori) {
                return ['id' => $kategori->id, 'name' => $kategori->nama_kategori];
            })
        )->values();

        $inisial = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $mejaMakan->kode_meja), 0, 2));

        $riwayat = session('riwayat_meja_' . $mejaMakan->id, []);

        return view('emenu.aplikasi', [
            'meja' => $mejaMakan,
            'menuJson' => $menuJson,
            'kategoriJson' => $kategoriJson,
            'orderRef' => '#' . $inisial . '-' . $mejaMakan->id . '04',
            'punyaRiwayat' => count($riwayat) > 0,
            'namaPelanggan' => (string) ($sesi->nama_pelanggan ?? ''),
        ]);
    }

    public function checkout(Request $request, string $meja): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        if (! $this->sesiTerverifikasi($mejaMakan)) {
            return $this->kunciRedirect($mejaMakan);
        }

        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $namaTersimpan = trim((string) $sesi->nama_pelanggan);

        $data = $request->validate([
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['integer', 'min:0', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
            'metode' => ['required', 'in:tunai,qris'],
            'nama_pelanggan' => [$namaTersimpan !== '' ? 'nullable' : 'required', 'string', 'max:100'],
        ], [
            'nama_pelanggan.required' => 'Nama pemesan wajib diisi sebelum melanjutkan.',
            'nama_pelanggan.max' => 'Nama pemesan maksimal 100 karakter.',
        ]);

        $namaPelanggan = $namaTersimpan !== ''
            ? $namaTersimpan
            : trim((string) ($data['nama_pelanggan'] ?? ''));

        $dipilih = collect($data['jumlah'])->filter(fn ($jumlah) => $jumlah > 0);

        if ($dipilih->isEmpty()) {
            return back()->withErrors(['jumlah' => 'Pilih minimal satu menu.'])->withInput();
        }

        try {
            $pesanan = app(PesananService::class)->buat(
                $mejaMakan,
                $dipilih->all(),
                $data['catatan'] ?? [],
                ['asal' => 'emenu', 'metode' => $data['metode'], 'nama_pelanggan' => $namaPelanggan],
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['jumlah' => $exception->getMessage()])->withInput();
        }

        if ($namaTersimpan === '') {
            $sesi->update(['nama_pelanggan' => $namaPelanggan]);
        }

        $request->session()->push('riwayat_meja_' . $mejaMakan->id, $pesanan->kode_pesanan);

        // Broadcast realtime: pesanan baru masuk ke KDS, kasir, dan pelayan.
        Realtime::emit(new PesananBaru([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $mejaMakan->kode_meja,
            'meja_id' => $mejaMakan->id,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'total_bayar' => (float) $pesanan->total_bayar,
        ]));

        // Redirect based on payment method
        if ($data['metode'] === 'qris') {
            // QRIS: Show waiting for verification page, will auto-redirect after callback
            return redirect()->route('emenu.verifikasi', $pesanan->kode_pesanan);
        } else {
            // Cash: Show payment code page for customer to give to kasir
            return redirect()->route('emenu.bayar-tunai', $pesanan->kode_pesanan);
        }
    }

    public function status(Request $request, string $kode_pesanan): View|RedirectResponse
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where('kode_pesanan', $kode_pesanan)
            ->firstOrFail();

        if (! $this->pesananBolehDiakses($request, $pesanan)) {
            return $this->kunciRedirect($pesanan->meja, 'Sesi meja sudah berakhir. Masukkan kembali kode saung untuk membuka pesanan.');
        }

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
        $qrisTtl = max(5, min(60, (int) Pengaturan::nilai('qris_expiry_minutes'))) * 60;

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

        // Hanya tampilkan pesanan yang dibuat pada sesi meja yang sedang aktif.
        // Riwayat dari tamu sebelumnya tidak boleh ikut terbawa.
        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $kodeRiwayat = array_reverse(session('riwayat_meja_' . $mejaMakan->id, []));

        $daftarPesanan = Pesanan::with(['detailPesanan.menu'])
            ->where('meja_id', $mejaMakan->id)
            ->where('waktu_pesan', '>=', $sesi->dibuka_pada)
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

    public function verifikasi(Request $request, string $kode_pesanan): View|RedirectResponse
    {
        $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode_pesanan)->firstOrFail();

        if (! $this->pesananBolehDiakses($request, $pesanan)) {
            return $this->kunciRedirect($pesanan->meja, 'Sesi meja sudah berakhir. Masukkan kembali kode saung untuk membuka pesanan.');
        }

        // Kalau sudah lunas (mis. halaman di-refresh), langsung ke status pesanan.
        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return redirect()->route('emenu.status', $pesanan->kode_pesanan);
        }

        // Generate QRIS QR Code via Midtrans untuk ditampilkan di halaman pelanggan.
        $qris = app(MidtransService::class)->createQrisCharge($pesanan);

        // Perkiraan masa berlaku QR (untuk countdown di UI). Refresh sesungguhnya
        // terjadi saat Midtrans menandai transaksi expire.
        $qrisTtl = max(5, min(60, (int) Pengaturan::nilai('qris_expiry_minutes'))) * 60;

        return view('emenu.verifikasi', compact('pesanan', 'qris', 'qrisTtl'));
    }

    /**
     * Cek status pembayaran QRIS dari Midtrans (dipanggil polling oleh halaman pelanggan).
     * Bila sukses, pesanan otomatis jadi lunas dan masuk dapur.
     */
    public function qrisStatus(Request $request, string $kode_pesanan): JsonResponse
    {
        $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode_pesanan)->firstOrFail();

        if (! $this->pesananBolehDiakses($request, $pesanan)) {
            return response()->json([
                'lunas' => false,
                'message' => 'Sesi meja sudah berakhir. Silakan buka kembali pesanan dari meja.',
            ], 403);
        }

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

                    app(StokService::class)->potongSetelahPembayaran($pesanan, null);
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

    public function bayarTunai(Request $request, string $kode_pesanan): View|RedirectResponse
    {
        $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode_pesanan)->firstOrFail();

        if (! $this->pesananBolehDiakses($request, $pesanan)) {
            return $this->kunciRedirect($pesanan->meja, 'Sesi meja sudah berakhir. Masukkan kembali kode saung untuk membuka pesanan.');
        }

        return view('emenu.bayar-tunai', compact('pesanan'));
    }

    public function panggil(Request $request, string $meja): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        if (! $this->sesiTerverifikasi($mejaMakan)) {
            return $this->kunciRedirect($mejaMakan, 'Masukkan kode saung terlebih dahulu sebelum memanggil pelayan.');
        }

        $detikCooldown = max(10, (int) Pengaturan::nilai('anti_spam_cooldown', 5));

        $panggilanAktif = LogPanggilPelayan::query()
            ->where('meja_id', $mejaMakan->id)
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->where('waktu_panggil', '>=', now()->subSeconds($detikCooldown))
            ->exists();

        if ($panggilanAktif) {
            return back()->withErrors([
                'panggilan' => 'Panggilan sebelumnya masih diproses. Mohon tunggu sebentar.',
            ]);
        }

        $panggilan = LogPanggilPelayan::create([
            'meja_id' => $mejaMakan->id,
            'status_panggilan' => 'menunggu',
        ]);

        // Broadcast realtime: panggilan pelayan baru.
        Realtime::emit(new PanggilanPelayan([
            'id' => $panggilan->id,
            'meja_id' => $mejaMakan->id,
            'meja' => $mejaMakan->kode_meja,
            'area' => $mejaMakan->area,
            'status_panggilan' => 'menunggu',
        ]));

        return back()->with('panggilan_terkirim', true);
    }

    private function cariMeja(string $meja): MejaMakan
    {
        if (is_numeric($meja)) {
            return MejaMakan::findOrFail($meja);
        }

        return MejaMakan::where('kode_meja', $meja)->firstOrFail();
    }

    /**
     * Cek apakah pengunjung sudah memasukkan kode unik untuk sesi yang sedang aktif.
     * Bila sesi ditutup kasir, sesi aktif berganti sehingga otomatis terkunci lagi.
     */
    private function sesiTerverifikasi(MejaMakan $meja): bool
    {
        $sesiId = session('sesi_meja_' . $meja->id);

        if (! $sesiId) {
            return false;
        }

        $aktif = SesiMeja::aktifUntuk($meja);

        return (int) $sesiId === $aktif->id;
    }

    private function pesananBolehDiakses(Request $request, Pesanan $pesanan): bool
    {
        if (! $pesanan->meja) {
            return false;
        }

        return AksesPesanan::sesiCookieValid($request, $pesanan);
    }

    private function kunciRedirect(MejaMakan $meja, ?string $pesan = null): RedirectResponse
    {
        $redirect = redirect()->route('emenu.kunci', $meja->kode_meja);

        if ($pesan !== null) {
            $redirect->withErrors(['kode_unik' => $pesan]);
        }

        return $redirect;
    }
}
