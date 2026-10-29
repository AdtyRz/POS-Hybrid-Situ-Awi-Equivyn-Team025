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

    /**
     * Tandai sesi meja sebagai sudah terverifikasi (lolos gate kode unik)
     * agar request berikutnya boleh mengakses halaman e-menu.
     */
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

    public function test_halaman_bayar_tunai_memakai_kode_pesanan_yang_sama_dengan_kasir(): void
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
            ->assertSee('Kode Bayar Anda')
            ->assertSee($pesanan->kode_pesanan)
            ->assertDontSee('PAY-');
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
}
