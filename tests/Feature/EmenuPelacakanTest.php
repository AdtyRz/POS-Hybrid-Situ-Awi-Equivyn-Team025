<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Services\MidtransService;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmenuPelacakanTest extends TestCase
{
    use RefreshDatabase;





    private function sesiTerverifikasi(MejaMakan $meja): array
    {
        $sesi = SesiMeja::aktifUntuk($meja);

        return ['sesi_meja_' . $meja->id => $sesi->id];
    }

    private function siapkanMejaDanMenu(): array
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan Utama',
            'target_kds' => 'dapur',
        ]);

        $meja = MejaMakan::create([
            'kode_meja' => 'LB-99',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-99-abcdefghijklmnop',
            'status_meja' => 'kosong',
        ]);

        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Liwet Uji',
            'slug' => 'nasi-liwet-uji',
            'harga' => 25000,
            'stok_menu' => 10,
        ]);

        return [$meja, $menu];
    }

    public function test_menu_pelanggan_memuat_state_live_dan_marker_sesi_berakhir(): void
    {
        [$meja] = $this->siapkanMejaDanMenu();

        $this->withSession($this->sesiTerverifikasi($meja))
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('id="pelanggan-menu-state"', false)
            ->assertSee('data-live="pelanggan-menu-state"', false)
            ->assertSee('data-live="pelanggan-menu-header-actions"', false)
            ->assertSee("realtime:sesi.berubah", false);

        $this->get(route('emenu.kunci', $meja->kode_meja))
            ->assertOk()
            ->assertSee('data-sesi-berakhir="1"', false);
    }

    public function test_menu_pelanggan_tidak_memakai_foto_dari_url_luar(): void
    {
        [$meja] = $this->siapkanMejaDanMenu();

        $this->withSession($this->sesiTerverifikasi($meja))
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertDontSee('images.unsplash.com', false)
            ->assertDontSee('images.pexels.com', false);
    }

    public function test_checkout_menyimpan_riwayat_tanpa_memotong_stok(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $response = $this->withSession($this->sesiTerverifikasi($meja))
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 2],
                'catatan' => [$menu->id => 'pedas'],
                'metode' => 'tunai',
                'nama_pelanggan' => 'Budi Santoso',
            ]);

        $response->assertSessionHas('riwayat_meja_' . $meja->id);

        $pesanan = Pesanan::first();
        $this->assertNotNull($pesanan);
        $this->assertSame('Budi Santoso', $pesanan->nama_pelanggan);
        $this->assertSame('Budi Santoso', SesiMeja::aktifUntuk($meja)->nama_pelanggan);
        $this->assertSame('terisi', $meja->fresh()->status_meja);
        $this->assertSame(10, $menu->fresh()->stok_menu);

        $this->withSession($this->sesiTerverifikasi($meja))
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Nama tersimpan untuk sesi meja ini');
    }

    public function test_checkout_pertama_wajib_mengisi_nama_pelanggan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $this->withSession($this->sesiTerverifikasi($meja))
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 1],
                'metode' => 'tunai',
            ])
            ->assertSessionHasErrors('nama_pelanggan');

        $this->assertSame(0, Pesanan::count());
        $this->assertNull(SesiMeja::aktifUntuk($meja)->nama_pelanggan);
    }

    public function test_nama_pelanggan_dipakai_ulang_untuk_pesanan_berikutnya(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 1],
                'metode' => 'tunai',
                'nama_pelanggan' => 'Budi Santoso',
            ])
            ->assertRedirect();

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 1],
                'metode' => 'tunai',
                'nama_pelanggan' => 'Nama Lain',
            ])
            ->assertRedirect();

        $this->assertSame(
            ['Budi Santoso', 'Budi Santoso'],
            Pesanan::orderBy('id')->pluck('nama_pelanggan')->all()
        );
        $this->assertSame('Budi Santoso', SesiMeja::aktifUntuk($meja)->nama_pelanggan);
    }

    public function test_nama_pelanggan_hilang_setelah_sesi_baru_dan_wajib_diisi_lagi(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesiLama = SesiMeja::aktifUntuk($meja);

        $this->withSession(['sesi_meja_' . $meja->id => $sesiLama->id])
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 1],
                'metode' => 'tunai',
                'nama_pelanggan' => 'Budi Santoso',
            ])
            ->assertRedirect();

        $pesanan = Pesanan::first();
        $pesanan->update([
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
        ]);

        $sesiBaru = SesiMeja::putuskanSesi($meja);

        $this->assertNull($sesiBaru->nama_pelanggan);
        $this->assertSame('Budi Santoso', $pesanan->fresh()->nama_pelanggan);

        $this->withSession(['sesi_meja_' . $meja->id => $sesiBaru->id])
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 1],
                'metode' => 'tunai',
            ])
            ->assertSessionHasErrors('nama_pelanggan');
    }

    public function test_tombol_pelacakan_muncul_saat_ada_riwayat(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $this->withSession($this->sesiTerverifikasi($meja))
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertDontSee('Lihat Status Pesanan');

        $this->withSession(array_merge($this->sesiTerverifikasi($meja), [
            'riwayat_meja_' . $meja->id => ['SW-UJI-01'],
        ]))
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Lihat Status Pesanan');
    }

    public function test_tombol_status_tetap_muncul_saat_ada_pesanan_meski_riwayat_browser_hilang(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );



        $this->withSession($sesi)
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Lihat Status Pesanan');
    }

    public function test_pelacakan_menampilkan_pesanan_sesi_aktif_tanpa_riwayat_browser(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $this->withSession($sesi)
            ->get(route('emenu.pelacakan', $meja->id))
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_hero_menampilkan_nama_pelanggan_setelah_diisi(): void
    {
        [$meja] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $this->withSession($sesi)
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Tamu '.$meja->kode_meja);

        SesiMeja::aktifUntuk($meja)->update(['nama_pelanggan' => 'Budi Santoso']);

        $this->withSession($sesi)
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_halaman_pelacakan_menampilkan_riwayat_pesanan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();

        $sesi = SesiMeja::aktifUntuk($meja);

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-UJI-01',
            'meja_id' => $meja->id,
            'total_bayar' => 55000,
            'metode_pembayaran' => 'tunai',
        ]);
        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);

        $this->withSession([
            'sesi_meja_' . $meja->id => $sesi->id,
            'riwayat_meja_' . $meja->id => ['SW-UJI-01'],
        ])
            ->get(route('emenu.pelacakan', $meja->id))
            ->assertOk()
            ->assertSee('SW-UJI-01')
            ->assertSee('2 item');
    }

    public function test_halaman_pelacakan_kosong_tanpa_sesi(): void
    {
        [$meja] = $this->siapkanMejaDanMenu();

        $this->withSession($this->sesiTerverifikasi($meja))
            ->get(route('emenu.pelacakan', $meja->id))
            ->assertOk()
            ->assertSee('Belum ada pesanan pada sesi ini');
    }

    public function test_payload_emenu_memuat_stok_dan_deskripsi_lengkap(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $deskripsi = str_repeat('Deskripsi menu panjang untuk pelanggan. ', 12);

        $menu->update([
            'stok_menu' => 1,
            'deskripsi' => $deskripsi,
        ]);

        $response = $this->withSession($this->sesiTerverifikasi($meja))
            ->get(route('emenu.meja', $meja->id))
            ->assertOk()
            ->assertSee('Jumlah porsi')
            ->assertSee('Stok tersedia');

        $menuJson = collect($response->viewData('menuJson'));
        $payload = $menuJson->firstWhere('id', $menu->id);

        $this->assertNotNull($payload);
        $this->assertSame(1, $payload['stock']);
        $this->assertSame($deskripsi, $payload['desc']);
        $this->assertFalse($payload['isSoldOut']);
    }

    public function test_checkout_emenu_menolak_jumlah_melebihi_stok_tersedia(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $menu->update(['stok_menu' => 1]);

        $this->withSession($this->sesiTerverifikasi($meja))
            ->post(route('emenu.checkout', $meja->id), [
                'jumlah' => [$menu->id => 2],
                'metode' => 'tunai',
                'nama_pelanggan' => 'Budi Santoso',
            ])
            ->assertSessionHasErrors('jumlah');

        $this->assertSame(0, Pesanan::count());
        $this->assertSame(1, $menu->fresh()->stok_menu);
    }

    public function test_halaman_bayar_tunai_menampilkan_kode_meja_dan_nama_pemesan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $this->assertSame($pesanan->kode_pesanan, $pesanan->kode_pembayaran);

        $this->withSession($sesi)
            ->get(route('emenu.bayar-tunai', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Kode Meja Anda')
            ->assertSee($meja->kode_meja)
            ->assertSee('Budi Santoso')
            ->assertDontSee('Kode Bayar Anda')
            ->assertDontSee('PAY-');
    }

    public function test_halaman_bayar_tunai_langsung_mengarah_ke_status_setelah_lunas(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
        ]);

        $response = $this->withSession($sesi)
            ->get(route('emenu.bayar-tunai', $pesanan->kode_pesanan));

        $response->assertRedirect();

        $tujuan = (string) $response->headers->get('Location');

        $this->assertStringContainsString('signature=', $tujuan);

        $this->get($tujuan)
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_endpoint_status_tunai_memberi_sinyal_lunas_dan_url_status(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $response = $this->withSession($sesi)
            ->getJson(route('emenu.bayar-tunai.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertJsonPath('lunas', true)
            ->assertJsonPath('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR);

        $redirect = (string) $response->json('redirect');

        $this->assertStringContainsString('signature=', $redirect);

        $this->get($redirect)
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_halaman_bayar_tunai_lunas_tetap_mengarah_ke_status_tanpa_cookie_sesi(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = SesiMeja::aktifUntuk($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
        ]);



        $this->flushSession();

        $response = $this->get(route('emenu.bayar-tunai', $pesanan->kode_pesanan));

        $response->assertRedirect();

        $tujuan = (string) $response->headers->get('Location');

        $this->assertStringContainsString('signature=', $tujuan);

        $this->get($tujuan)
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_endpoint_status_tunai_tetap_menyatakan_lunas_tanpa_cookie_sesi(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        SesiMeja::aktifUntuk($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $this->flushSession();

        $response = $this->getJson(route('emenu.bayar-tunai.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertJsonPath('lunas', true)
            ->assertJsonPath('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR);

        $redirect = (string) $response->json('redirect');

        $this->assertStringContainsString('signature=', $redirect);

        $this->get($redirect)
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_status_pesanan_menolak_kode_saung_di_query_dan_header(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = SesiMeja::aktifUntuk($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $this->flushSession();

        $this->get(route('emenu.status', [
            'kode_pesanan' => $pesanan->kode_pesanan,
            'kode_saung' => $sesi->kode_unik,
        ]))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->withHeader('X-Kode-Saung', $sesi->kode_unik)
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));
    }

    public function test_status_pesanan_qris_lunas_bisa_dibuka_dengan_url_bertanda_tangan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'qris', 'nama_pelanggan' => 'Budi Santoso']
        );

        $status = (object) [
            'order_id' => $pesanan->kode_pesanan,
            'gross_amount' => (string) (float) $pesanan->total_bayar,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-uji-qris',
        ];

        $this->partialMock(MidtransService::class, function ($mock) use ($status) {
            $mock->shouldReceive('getTransactionStatus')
                ->once()
                ->andReturn($status);
        });

        $response = $this->withSession($sesi)
            ->getJson(route('emenu.qris.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertJsonPath('lunas', true);

        $redirect = (string) $response->json('redirect');

        $this->assertStringContainsString('signature=', $redirect);

        $this->flushSession();

        $this->get($redirect)
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_status_pesanan_lunas_tidak_menampilkan_instruksi_pembayaran_tunai(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
        ]);

        $this->withSession($sesi)
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Meja ' . $meja->kode_meja)
            ->assertSee('Budi Santoso')
            ->assertDontSee('Pembayaran Tunai')
            ->assertDontSee('Sebutkan kode meja');
    }

    public function test_status_pesanan_diantar_menampilkan_tahap_sedang_diantar(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => Pesanan::STATUS_DIANTAR,
        ]);

        $response = $this->withSession($sesi)
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Sedang Diantar ke Meja')
            ->assertSeeInOrder(['Sedang Diantar ke Meja', 'Aktif'])
            ->assertViewHas('tahap', 4);

        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '>Aktif</span>'));
        $this->assertSame(0, substr_count($html, '>Tiba</span>'));
    }

    public function test_status_pesanan_selesai_menampilkan_tahap_pesanan_tiba(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'tunai', 'nama_pelanggan' => 'Budi Santoso']
        );

        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'status_pesanan' => Pesanan::STATUS_SELESAI,
        ]);

        $response = $this->withSession($sesi)
            ->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Pesanan Tiba di ' . $meja->kode_meja)
            ->assertViewHas('tahap', 5);

        $html = $response->getContent();

        $this->assertSame(0, substr_count($html, '>Aktif</span>'));
        $this->assertSame(1, substr_count($html, '>Tiba</span>'));
    }

    public function test_halaman_verifikasi_qris_menampilkan_kode_meja_dan_expiry_dari_pengaturan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);
        Pengaturan::simpanBanyak(['qris_expiry_minutes' => 17]);

        $this->partialMock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createQrisCharge')->once()->andReturn(null);
        });

        $pesanan = app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            ['asal' => 'emenu', 'metode' => 'qris', 'nama_pelanggan' => 'Budi Santoso']
        );

        $this->withSession($sesi)
            ->get(route('emenu.verifikasi', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Kode Meja:')
            ->assertSee($meja->kode_meja)
            ->assertDontSee('Kode Pesanan:')
            ->assertSee('const qrTtl = 1020;', false);
    }

    public function test_checkout_dengan_kunci_idempotensi_sama_tidak_membuat_pesanan_ganda(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $muatan = [
            'jumlah' => [$menu->id => 2],
            'catatan' => [$menu->id => 'pedas'],
            'metode' => 'tunai',
            'nama_pelanggan' => 'Budi Santoso',
            'idempotency_key' => 'checkout-uji-ganda-001',
        ];

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), $muatan)
            ->assertRedirect(route('emenu.bayar-tunai', Pesanan::firstOrFail()->kode_pesanan));

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), $muatan)
            ->assertRedirect(route('emenu.bayar-tunai', Pesanan::firstOrFail()->kode_pesanan));

        $this->assertSame(1, Pesanan::count());
        $this->assertSame(1, Pesanan::firstOrFail()->detailPesanan()->count());
        $this->assertSame(10, $menu->fresh()->stok_menu);
        $this->assertCount(1, session('riwayat_meja_' . $meja->id, []));
    }

    public function test_checkout_qris_dengan_kunci_idempotensi_sama_hanya_membuat_satu_tagihan(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $muatan = [
            'jumlah' => [$menu->id => 1],
            'metode' => 'qris',
            'nama_pelanggan' => 'Budi Santoso',
            'idempotency_key' => 'checkout-qris-uji-ganda-001',
        ];

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), $muatan)
            ->assertRedirect(route('emenu.verifikasi', Pesanan::firstOrFail()->kode_pesanan));

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), $muatan)
            ->assertRedirect(route('emenu.verifikasi', Pesanan::firstOrFail()->kode_pesanan));

        $pesanan = Pesanan::sole();

        $this->assertSame('qris', $pesanan->metode_pembayaran);
        $this->assertSame(1, $pesanan->detailPesanan()->count());
        $this->assertCount(1, session('riwayat_meja_' . $meja->id, []));
    }

    public function test_checkout_dengan_kunci_idempotensi_berbeda_membuat_pesanan_baru(): void
    {
        [$meja, $menu] = $this->siapkanMejaDanMenu();
        $sesi = $this->sesiTerverifikasi($meja);

        $muatan = [
            'jumlah' => [$menu->id => 1],
            'metode' => 'tunai',
            'nama_pelanggan' => 'Budi Santoso',
        ];

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), $muatan + ['idempotency_key' => 'checkout-uji-a'])
            ->assertRedirect();

        $this->withSession($sesi)
            ->post(route('emenu.checkout', $meja->id), $muatan + ['idempotency_key' => 'checkout-uji-b'])
            ->assertRedirect();

        $this->assertSame(2, Pesanan::count());
    }
}
