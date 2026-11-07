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
use App\Support\AksesPesanan;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class EMenuController extends Controller
{
    private const MENIT_URL_STATUS = 120;

    public function beranda(): View
    {
        return view('pelanggan.beranda.index');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_unik' => ['required', 'string', 'max:10'],
        ], [
            'kode_unik.required' => 'Masukkan kode kunci login meja.',
            'kode_unik.max' => 'Kode kunci terlalu panjang.',
        ]);

        $sesi = SesiMeja::query()
            ->with('meja')
            ->where('status', 'aktif')
            ->where('kode_unik', trim($data['kode_unik']))
            ->latest('id')
            ->first();

        if (! $sesi || ! $sesi->meja) {
            return $this->kodeKunciDitolak();
        }

        $sesiAktif = SesiMeja::aktifUntuk($sesi->meja);

        if ((int) $sesiAktif->id !== (int) $sesi->id) {
            return $this->kodeKunciDitolak();
        }

        $mejaTerbuka = $this->mejaTerbuka($request);

        if ($mejaTerbuka && (int) $mejaTerbuka->id !== (int) $sesi->meja_id) {
            return $this->kodeKunciDitolak($this->pesanMejaLain($mejaTerbuka));
        }

        $this->tandaiSesiTerverifikasi($request, $sesi->meja, $sesiAktif);

        return redirect()->to(route('emenu.meja', $sesi->meja->kode_meja));
    }




    public function kunci(Request $request, string $meja): View|RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);
        $mejaTerbuka = $this->mejaTerbuka($request);

        if ($mejaTerbuka && (int) $mejaTerbuka->id !== (int) $mejaMakan->id) {
            return $this->kodeKunciDitolak($this->pesanMejaLain($mejaTerbuka));
        }

        $sesi = SesiMeja::aktifUntuk($mejaMakan);

        return view('pelanggan.beranda.kunci', [
            'meja' => $mejaMakan,
            'panjangKode' => strlen($sesi->kode_unik),
        ]);
    }





    public function buka(Request $request, string $meja): RedirectResponse|JsonResponse
    {
        $mejaMakan = $this->cariMeja($meja);
        $mejaTerbuka = $this->mejaTerbuka($request);

        if ($mejaTerbuka && (int) $mejaTerbuka->id !== (int) $mejaMakan->id) {
            return $this->tolakMejaLain($request, $mejaTerbuka);
        }

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

        $this->tandaiSesiTerverifikasi($request, $mejaMakan, $sesi);

        $tujuan = route('emenu.meja', $mejaMakan->kode_meja);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $tujuan,
            ]);
        }

        return redirect()->to($tujuan);
    }






    public function keluar(Request $request): RedirectResponse
    {
        foreach (array_keys($request->session()->all()) as $kunci) {
            if (preg_match('/^(sesi_meja|riwayat_meja)_\d+$/', (string) $kunci)) {
                $request->session()->forget($kunci);
            }
        }

        return redirect()->route('emenu.index');
    }

    public function aplikasi(Request $request, string $meja): View|RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);
        $mejaTerbuka = $this->mejaTerbuka($request);

        if ($mejaTerbuka && (int) $mejaTerbuka->id !== (int) $mejaMakan->id) {
            return $this->kodeKunciDitolak($this->pesanMejaLain($mejaTerbuka));
        }

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
                'image' => $menu->foto_menu ? Storage::disk()->url($menu->foto_menu) : '',
                'stock' => max(0, (int) $menu->stok_menu),
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




        $adaPesananSesi = Pesanan::query()
            ->where('meja_id', $mejaMakan->id)
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->where('waktu_pesan', '>=', $sesi->dibuka_pada)
            ->exists();

        return view('pelanggan.menu.index', [
            'meja' => $mejaMakan,
            'menuJson' => $menuJson,
            'kategoriJson' => $kategoriJson,
            'orderRef' => '#' . $inisial . '-' . $mejaMakan->id . '04',
            'punyaRiwayat' => count($riwayat) > 0 || $adaPesananSesi,
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


        Realtime::emit(new PesananBaru([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $mejaMakan->kode_meja,
            'meja_id' => $mejaMakan->id,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'total_bayar' => (float) $pesanan->total_bayar,
        ]));


        if ($data['metode'] === 'qris') {

            return redirect()->route('emenu.verifikasi', $pesanan->kode_pesanan);
        } else {

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



        $qris = null;
        $midtrans = app(MidtransService::class);
        $qrisTtl = $midtrans->qrisExpiryMinutes() * 60;

        if ($pesanan->metode_pembayaran === 'qris'
            && $pesanan->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR
            && $pesanan->status_pesanan !== 'dibatalkan') {
            $qris = $midtrans->createQrisCharge($pesanan);
        }

        return view('pelanggan.pesanan.status', [
            'pesanan' => $pesanan,
            'tahap' => $tahap,
            'qris' => $qris,
            'qrisTtl' => $qrisTtl,
            'qrisStatusUrl' => URL::temporarySignedRoute(
                'emenu.qris.status',
                now()->addMinutes(self::MENIT_URL_STATUS),
                ['kode_pesanan' => $pesanan->kode_pesanan],
            ),
        ]);
    }

    public function pelacakan(string $meja): View|RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        if (! $this->sesiTerverifikasi($mejaMakan)) {
            return $this->kunciRedirect($mejaMakan);
        }





        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $kodeRiwayat = array_reverse(session('riwayat_meja_' . $mejaMakan->id, []));

        $daftarPesanan = Pesanan::with(['detailPesanan.menu'])
            ->where('meja_id', $mejaMakan->id)
            ->where('waktu_pesan', '>=', $sesi->dibuka_pada)
            ->when($kodeRiwayat !== [], fn ($query) => $query->whereIn('kode_pesanan', $kodeRiwayat))
            ->get();

        if ($kodeRiwayat !== []) {
            $daftarPesanan = $daftarPesanan
                ->sortBy(fn ($pesanan) => array_search($pesanan->kode_pesanan, $kodeRiwayat))
                ->values();
        } else {
            $daftarPesanan = $daftarPesanan->sortByDesc('waktu_pesan')->values();
        }

        return view('pelanggan.pesanan.pelacakan', [
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


        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return redirect()->to($this->urlStatusPesanan($pesanan, true));
        }

        $midtrans = app(MidtransService::class);
        $qris = $midtrans->createQrisCharge($pesanan);
        $qrisTtl = $midtrans->qrisExpiryMinutes() * 60;
        $urlStatus = $this->urlStatusPesanan($pesanan, true);
        $urlQrisStatus = URL::temporarySignedRoute(
            'emenu.qris.status',
            now()->addMinutes(self::MENIT_URL_STATUS),
            ['kode_pesanan' => $pesanan->kode_pesanan],
        );

        return view('pelanggan.pembayaran.verifikasi', compact('pesanan', 'qris', 'qrisTtl', 'urlStatus', 'urlQrisStatus'));
    }





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

            if ($midtrans->verifikasiQris($pesanan, $status)) {
                try {
                    app(PesananService::class)->lunasi(
                        $pesanan,
                        'qris',
                        null,
                        $status->transaction_id ?? null,
                    );
                } catch (\RuntimeException $exception) {
                    $pesanan->refresh();

                    return response()->json([
                        'lunas' => false,
                        'status_pesanan' => $pesanan->status_pesanan,
                        'status_pembayaran' => $pesanan->status_pembayaran,
                        'message' => $exception->getMessage(),
                    ], 422)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                }

                $pesanan->refresh();
                Realtime::emit(new StatusPesananBerubah([
                    'kode_pesanan' => $pesanan->kode_pesanan,
                    'meja' => $pesanan->meja->kode_meja ?? null,
                    'status_pesanan' => $pesanan->status_pesanan,
                    'status_pembayaran' => $pesanan->status_pembayaran,
                    'metode_pembayaran' => $pesanan->metode_pembayaran,
                ]));
            } elseif ($transactionStatus === 'expire') {

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

        $lunas = $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR;

        return response()->json([
            'lunas' => $lunas,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'redirect' => $this->urlStatusPesanan($pesanan, $lunas),
        ]);
    }

    public function bayarTunai(Request $request, string $kode_pesanan): View|RedirectResponse
    {
        $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode_pesanan)->firstOrFail();




        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return redirect()->to($this->urlStatusPesanan($pesanan, true));
        }

        if (! $this->pesananBolehDiakses($request, $pesanan)) {
            return $this->kunciRedirect($pesanan->meja, 'Sesi meja sudah berakhir. Masukkan kembali kode saung untuk membuka pesanan.');
        }

        $urlStatus = $this->urlStatusPesanan($pesanan, true);

        return view('pelanggan.pembayaran.tunai', compact('pesanan', 'urlStatus'));
    }

    public function tunaiStatus(Request $request, string $kode_pesanan): JsonResponse
    {
        $pesanan = Pesanan::with('meja')->where('kode_pesanan', $kode_pesanan)->firstOrFail();

        $lunas = $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR;



        if (! $lunas && ! $this->pesananBolehDiakses($request, $pesanan)) {
            return response()->json([
                'lunas' => false,
                'message' => 'Sesi meja sudah berakhir. Silakan buka kembali pesanan dari meja.',
            ], 403);
        }

        return response()->json([
            'lunas' => $lunas,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'redirect' => $this->urlStatusPesanan($pesanan, $lunas),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function urlStatusPesanan(Pesanan $pesanan, bool $sementara = false): string
    {
        if ($sementara) {
            return URL::temporarySignedRoute(
                'emenu.status',
                now()->addMinutes(self::MENIT_URL_STATUS),
                ['kode_pesanan' => $pesanan->kode_pesanan],
            );
        }

        return route('emenu.status', ['kode_pesanan' => $pesanan->kode_pesanan]);
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


        Realtime::emit(new PanggilanPelayan([
            'id' => $panggilan->id,
            'meja_id' => $mejaMakan->id,
            'meja' => $mejaMakan->kode_meja,
            'area' => $mejaMakan->area,
            'status_panggilan' => 'menunggu',
        ]));

        return back()->with('panggilan_terkirim', true);
    }

    private function kodeKunciDitolak(?string $pesan = null): RedirectResponse
    {
        return redirect()->route('emenu.index')
            ->withErrors(['kode_unik' => $pesan ?? 'Kode kunci tidak valid atau sesi meja sudah berakhir.'])
            ->withInput();
    }






    private function mejaTerbuka(Request $request): ?MejaMakan
    {
        foreach ($request->session()->all() as $kunci => $sesiId) {
            if (! preg_match('/^sesi_meja_(\d+)$/', (string) $kunci, $cocok)) {
                continue;
            }

            $meja = MejaMakan::find((int) $cocok[1]);

            if (! $meja) {
                continue;
            }

            $aktif = SesiMeja::aktifUntuk($meja);

            if ((int) $sesiId === (int) $aktif->id) {
                return $meja;
            }
        }

        return null;
    }

    private function pesanMejaLain(MejaMakan $meja): string
    {
        return 'Anda masih terhubung ke '.$meja->kode_meja
            .'. Keluar dari sesi meja tersebut sebelum membuka meja lain.';
    }

    private function tolakMejaLain(Request $request, MejaMakan $meja): RedirectResponse|JsonResponse
    {
        $pesan = $this->pesanMejaLain($meja);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $pesan,
                'errors' => ['kode_unik' => [$pesan]],
            ], 403);
        }

        return back()->withErrors(['kode_unik' => $pesan]);
    }

    private function tandaiSesiTerverifikasi(Request $request, MejaMakan $meja, SesiMeja $sesi): void
    {
        $kunciSesi = 'sesi_meja_' . $meja->id;
        $sesiSebelumnya = $request->session()->get($kunciSesi);


        if ($sesiSebelumnya !== null && (int) $sesiSebelumnya !== (int) $sesi->id) {
            $request->session()->forget('riwayat_meja_' . $meja->id);
        }


        $request->session()->regenerate();
        $request->session()->put($kunciSesi, $sesi->id);
    }

    private function cariMeja(string $meja): MejaMakan
    {
        if (is_numeric($meja)) {
            return MejaMakan::findOrFail($meja);
        }

        return MejaMakan::where('kode_meja', $meja)->firstOrFail();
    }





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



        if ($request->hasValidSignature()) {
            return true;
        }

        $riwayat = $request->session()->get('riwayat_meja_' . $pesanan->meja_id, []);
        if (is_array($riwayat) && in_array($pesanan->kode_pesanan, $riwayat, true)) {
            return true;
        }

        if (AksesPesanan::sesiCookieValid($request, $pesanan)) {
            return true;
        }



        return false;
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
