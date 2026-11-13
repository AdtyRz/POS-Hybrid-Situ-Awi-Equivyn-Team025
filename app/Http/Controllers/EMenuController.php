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
use App\Support\PerangkatPelanggan;
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

        $perangkat = PerangkatPelanggan::id($request);

        if ($this->perangkatLainAktif($sesiAktif, $perangkat)) {
            $this->catatIzinAmbilAlih($request, $sesiAktif);

            return redirect()->route('emenu.index')
                ->with('konflik_perangkat', $this->payloadKonflik($sesiAktif, $sesi->meja));
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

        $perangkat = PerangkatPelanggan::id($request);

        if ($this->perangkatLainAktif($sesi, $perangkat)) {
            $this->catatIzinAmbilAlih($request, $sesi);
            $konflik = $this->payloadKonflik($sesi, $mejaMakan);
            $pesan = 'Sesi meja ini sedang dipakai di perangkat lain.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $pesan,
                    'konflik' => $konflik,
                    'errors' => ['kode_unik' => [$pesan]],
                ], 409);
            }

            return redirect()->route('emenu.kunci', $mejaMakan->kode_meja)
                ->with('konflik_perangkat', $konflik)
                ->withErrors(['kode_unik' => $pesan]);
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
        $perangkat = PerangkatPelanggan::id($request);

        foreach (array_keys($request->session()->all()) as $kunci) {
            $kunci = (string) $kunci;

            if (preg_match('/^sesi_meja_(\d+)$/', $kunci, $cocok)) {
                $meja = MejaMakan::find((int) $cocok[1]);

                if ($meja) {
                    $sesi = SesiMeja::aktifUntuk($meja);

                    if (PerangkatPelanggan::cocok($sesi, $perangkat)) {
                        $this->lepasPerangkat($sesi);
                    }
                }
            }

            if (preg_match('/^(sesi_meja|riwayat_meja|izin_ambil_alih)_\d+$/', $kunci)) {
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
            $sesiAktif = SesiMeja::aktifUntuk($mejaMakan);
            $pesan = $this->perangkatLainAktif($sesiAktif, PerangkatPelanggan::id($request))
                ? 'Sesi meja ini sedang dipakai di perangkat lain.'
                : 'Sesi meja sudah berakhir. Masukkan kembali kode saung untuk membuka pesanan.';

            return $this->kunciRedirect($mejaMakan, $pesan);
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
            'idempotency_key' => ['nullable', 'string', 'max:64'],
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
                [
                    'asal' => 'emenu',
                    'metode' => $data['metode'],
                    'nama_pelanggan' => $namaPelanggan,
                    'idempotency_key' => $data['idempotency_key'] ?? null,
                ],
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['jumlah' => $exception->getMessage()])->withInput();
        }

        $pesananBaru = $pesanan->wasRecentlyCreated;

        if ($pesananBaru) {
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
        }


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

    public function mintaAmbilAlih(Request $request, string $meja): JsonResponse
    {
        $mejaMakan = $this->cariMeja($meja);
        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $perangkat = PerangkatPelanggan::id($request);
        $izin = $request->session()->get('izin_ambil_alih_' . $mejaMakan->id);

        if (! is_array($izin)
            || (int) ($izin['sesi_id'] ?? 0) !== (int) $sesi->id
            || now()->timestamp - (int) ($izin['waktu'] ?? 0) > 600) {
            return response()->json([
                'message' => 'Sesi tidak valid. Masukkan kembali kode meja lalu coba lagi.',
            ], 403);
        }

        if (! $this->perangkatLainAktif($sesi, $perangkat)) {
            $this->klaimPerangkat($request, $sesi);
            $request->session()->put('sesi_meja_' . $mejaMakan->id, $sesi->id);
            $request->session()->forget('izin_ambil_alih_' . $mejaMakan->id);

            return response()->json([
                'status' => 'disetujui',
                'redirect' => route('emenu.meja', $mejaMakan->kode_meja),
            ]);
        }

        if ($sesi->takeover_status === 'menunggu'
            && filled($sesi->takeover_perangkat_id)
            && ! hash_equals((string) $sesi->takeover_perangkat_id, $perangkat)
            && ! $this->takeoverKedaluwarsa($sesi)) {
            return response()->json([
                'message' => 'Permintaan perangkat lain sedang diproses. Coba lagi sebentar.',
            ], 409);
        }

        $sesi->forceFill([
            'takeover_status' => 'menunggu',
            'takeover_perangkat_id' => $perangkat,
            'takeover_perangkat_label' => PerangkatPelanggan::label($request),
            'takeover_diminta_pada' => now(),
        ])->save();

        return response()->json([
            'status' => 'menunggu',
            'label_aktif' => $sesi->perangkat_label ?: 'perangkat lain',
        ]);
    }

    public function statusPerangkat(Request $request, string $meja): JsonResponse
    {
        $mejaMakan = $this->cariMeja($meja);
        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $perangkat = PerangkatPelanggan::id($request);

        if ($this->takeoverKedaluwarsa($sesi)) {
            $sesi->forceFill(['takeover_status' => 'kedaluwarsa'])->save();
        }

        $aktif = PerangkatPelanggan::cocok($sesi, $perangkat);
        $pemohon = filled($sesi->takeover_perangkat_id)
            && hash_equals((string) $sesi->takeover_perangkat_id, $perangkat);

        $redirect = null;

        if ($aktif) {
            $this->sentuhPerangkat($sesi);

            $kunciSesi = 'sesi_meja_' . $mejaMakan->id;

            if ((int) $request->session()->get($kunciSesi) !== (int) $sesi->id) {
                $request->session()->put($kunciSesi, $sesi->id);
                $request->session()->forget('izin_ambil_alih_' . $mejaMakan->id);
                $redirect = route('emenu.meja', $mejaMakan->kode_meja);
            }
        }

        return response()->json([
            'peran' => $aktif ? 'aktif' : ($pemohon ? 'pemohon' : 'lain'),
            'sesi_aktif' => $sesi->status === 'aktif',
            'kode_meja' => $mejaMakan->kode_meja,
            'takeover' => filled($sesi->takeover_status) ? [
                'status' => $sesi->takeover_status,
                'label_aktif' => $sesi->perangkat_label ?: 'perangkat lain',
                'label_pemohon' => $sesi->takeover_perangkat_label ?: 'perangkat lain',
                'diminta_pada' => $sesi->takeover_diminta_pada?->toIso8601String(),
            ] : null,
            'redirect' => $redirect,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function responAmbilAlih(Request $request, string $meja): JsonResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', 'in:terima,tolak'],
        ]);

        $mejaMakan = $this->cariMeja($meja);
        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $perangkat = PerangkatPelanggan::id($request);

        if (! PerangkatPelanggan::cocok($sesi, $perangkat)) {
            return response()->json([
                'message' => 'Hanya perangkat yang sedang aktif yang dapat menjawab.',
            ], 403);
        }

        if ($sesi->takeover_status !== 'menunggu' || $this->takeoverKedaluwarsa($sesi)) {
            if ($sesi->takeover_status === 'menunggu') {
                $sesi->forceFill(['takeover_status' => 'kedaluwarsa'])->save();
            }

            return response()->json([
                'message' => 'Permintaan sudah tidak berlaku.',
            ], 409);
        }

        if ($data['keputusan'] === 'terima') {
            if (blank($sesi->takeover_perangkat_id)) {
                return response()->json([
                    'message' => 'Perangkat peminta tidak ditemukan. Coba lagi.',
                ], 409);
            }

            $sesi->forceFill([
                'perangkat_id' => $sesi->takeover_perangkat_id,
                'perangkat_label' => $sesi->takeover_perangkat_label,
                'perangkat_aktif_pada' => now(),
                'takeover_status' => 'disetujui',
            ])->save();

            $this->lupakanSesiPelanggan($request, $mejaMakan);

            return response()->json([
                'status' => 'disetujui',
                'redirect' => route('emenu.kunci', $mejaMakan->kode_meja),
            ]);
        }

        $sesi->forceFill(['takeover_status' => 'ditolak'])->save();

        return response()->json(['status' => 'ditolak']);
    }

    public function batalAmbilAlih(Request $request, string $meja): JsonResponse
    {
        $mejaMakan = $this->cariMeja($meja);
        $sesi = SesiMeja::aktifUntuk($mejaMakan);
        $perangkat = PerangkatPelanggan::id($request);

        if ($sesi->takeover_status === 'menunggu'
            && filled($sesi->takeover_perangkat_id)
            && hash_equals((string) $sesi->takeover_perangkat_id, $perangkat)) {
            $sesi->forceFill([
                'takeover_status' => null,
                'takeover_perangkat_id' => null,
                'takeover_perangkat_label' => null,
                'takeover_diminta_pada' => null,
            ])->save();
        }

        return response()->json(['status' => 'dibatalkan']);
    }

    private function payloadKonflik(SesiMeja $sesi, MejaMakan $meja): array
    {
        return [
            'meja_id' => $meja->id,
            'kode_meja' => $meja->kode_meja,
            'label' => $sesi->perangkat_label ?: 'perangkat lain',
            'minta_url' => route('emenu.perangkat.minta', $meja->kode_meja),
            'status_url' => route('emenu.perangkat.status', $meja->kode_meja),
            'batal_url' => route('emenu.perangkat.batal', $meja->kode_meja),
        ];
    }

    private function catatIzinAmbilAlih(Request $request, SesiMeja $sesi): void
    {
        $request->session()->put('izin_ambil_alih_' . $sesi->meja_id, [
            'sesi_id' => $sesi->id,
            'waktu' => now()->timestamp,
        ]);
    }

    private function perangkatLainAktif(SesiMeja $sesi, string $perangkat): bool
    {
        if (blank($sesi->perangkat_id)) {
            return false;
        }

        if (hash_equals((string) $sesi->perangkat_id, $perangkat)) {
            return false;
        }

        if ($this->perangkatKedaluwarsa($sesi)) {
            $this->lepasPerangkat($sesi);

            return false;
        }

        return true;
    }

    private function perangkatKedaluwarsa(SesiMeja $sesi): bool
    {
        return $sesi->perangkat_aktif_pada === null
            || $sesi->perangkat_aktif_pada->lt(now()->subMinutes(5));
    }

    private function takeoverKedaluwarsa(SesiMeja $sesi): bool
    {
        return $sesi->takeover_status === 'menunggu'
            && $sesi->takeover_diminta_pada !== null
            && $sesi->takeover_diminta_pada->lt(now()->subSeconds(60));
    }

    private function klaimPerangkat(Request $request, SesiMeja $sesi): void
    {
        $sesi->forceFill([
            'perangkat_id' => PerangkatPelanggan::id($request),
            'perangkat_label' => PerangkatPelanggan::label($request),
            'perangkat_aktif_pada' => now(),
            'takeover_status' => null,
            'takeover_perangkat_id' => null,
            'takeover_perangkat_label' => null,
            'takeover_diminta_pada' => null,
        ])->save();
    }

    private function sentuhPerangkat(SesiMeja $sesi): void
    {
        if ($sesi->perangkat_aktif_pada === null
            || $sesi->perangkat_aktif_pada->lt(now()->subSeconds(30))) {
            $sesi->forceFill(['perangkat_aktif_pada' => now()])->save();
        }
    }

    private function lepasPerangkat(SesiMeja $sesi): void
    {
        $sesi->forceFill([
            'perangkat_id' => null,
            'perangkat_label' => null,
            'perangkat_aktif_pada' => null,
            'takeover_status' => null,
            'takeover_perangkat_id' => null,
            'takeover_perangkat_label' => null,
            'takeover_diminta_pada' => null,
        ])->save();
    }

    private function lupakanSesiPelanggan(Request $request, MejaMakan $meja): void
    {
        $request->session()->forget([
            'sesi_meja_' . $meja->id,
            'riwayat_meja_' . $meja->id,
            'izin_ambil_alih_' . $meja->id,
        ]);
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
        $request->session()->forget('izin_ambil_alih_' . $meja->id);

        $this->klaimPerangkat($request, $sesi);
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

        if ((int) $sesiId !== $aktif->id) {
            return false;
        }

        $perangkat = PerangkatPelanggan::id(request());

        if (blank($aktif->perangkat_id)) {
            $this->klaimPerangkat(request(), $aktif);

            return true;
        }

        if (PerangkatPelanggan::cocok($aktif, $perangkat)) {
            $this->sentuhPerangkat($aktif);

            return true;
        }

        return false;
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
