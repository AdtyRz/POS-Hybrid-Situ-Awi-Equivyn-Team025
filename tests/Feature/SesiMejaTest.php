<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SesiMejaTest extends TestCase
{
    use RefreshDatabase;

    private function siapkanMeja(): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => 'LB-77',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-77-abcdefghijklmnop',
            'status_meja' => 'kosong',
        ]);
    }

    public function test_halaman_meja_diarahkan_ke_kunci_tanpa_kode(): void
    {
        $meja = $this->siapkanMeja();

        $this->get(route('emenu.meja', $meja->id))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->get(route('emenu.pelacakan', $meja->id))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja));

        $this->get(route('emenu.kunci', $meja->kode_meja))
            ->assertOk()
            ->assertSee('Akses E-Menu');
    }

    public function test_beranda_memakai_form_kode_kunci_login(): void
    {
        $this->get(route('emenu.index'))
            ->assertOk()
            ->assertSee('kode kunci login')
            ->assertSee('action="' . route('emenu.masuk') . '"', false)
            ->assertSee('name="kode_unik"', false)
            ->assertDontSee('atau ketik kode meja');
    }

    public function test_kode_kunci_beranda_langsung_membuka_meja(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->post(route('emenu.masuk'), ['kode_unik' => $sesi->kode_unik])
            ->assertRedirect(route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $meja->kode_meja))
            ->assertOk()
            ->assertSee($meja->kode_meja);
    }

    public function test_kode_kunci_beranda_salah_ditolak(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $panjang = strlen($sesi->kode_unik);
        $salah = str_pad(
            (string) (((int) $sesi->kode_unik + 1) % (10 ** $panjang)),
            $panjang,
            '0',
            STR_PAD_LEFT
        );

        $this->post(route('emenu.masuk'), ['kode_unik' => $salah])
            ->assertRedirect(route('emenu.index'))
            ->assertSessionHasErrors('kode_unik')
            ->assertSessionMissing('sesi_meja_' . $meja->id);
    }

    public function test_kode_kunci_dari_sesi_lama_ditolak(): void
    {
        $meja = $this->siapkanMeja();
        $lama = SesiMeja::aktifUntuk($meja);
        SesiMeja::putuskanSesi($meja);

        $this->post(route('emenu.masuk'), ['kode_unik' => $lama->kode_unik])
            ->assertRedirect(route('emenu.index'))
            ->assertSessionHasErrors('kode_unik')
            ->assertSessionMissing('sesi_meja_' . $meja->id);
    }

    public function test_kode_unik_salah_ditolak(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $salah = str_pad((string) (((int) $sesi->kode_unik + 1) % 10000), 4, '0', STR_PAD_LEFT);

        $this->from(route('emenu.kunci', $meja->kode_meja))
            ->post(route('emenu.buka', $meja->id), ['kode_unik' => $salah])
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja))
            ->assertSessionHasErrors('kode_unik');
    }

    public function test_kode_unik_salah_ditolak_untuk_permintaan_json(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $salah = str_pad((string) (((int) $sesi->kode_unik + 1) % 10000), 4, '0', STR_PAD_LEFT);

        $this->postJson(route('emenu.buka', $meja->id), ['kode_unik' => $salah])
            ->assertStatus(422)
            ->assertJsonPath('errors.kode_unik.0', 'Kode tidak sesuai dengan yang tampil di layar meja.');
    }

    public function test_kode_unik_benar_membuka_emenu(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->post(route('emenu.buka', $meja->id), ['kode_unik' => $sesi->kode_unik])
            ->assertRedirect(route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $meja->id))
            ->assertOk();
    }

    public function test_kode_unik_benar_mengembalikan_redirect_untuk_permintaan_json(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->postJson(route('emenu.buka', $meja->id), ['kode_unik' => $sesi->kode_unik])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect', route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);
    }

    public function test_kode_enam_digit_ditampilkan_dan_dapat_dibuka(): void
    {
        Pengaturan::create(['kunci' => 'encryption_level', 'nilai' => '6 Digit']);

        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->assertSame(6, strlen($sesi->kode_unik));

        $this->get(route('emenu.kunci', $meja->kode_meja))
            ->assertOk()
            ->assertSee('kunciApp(6,', false);

        $this->postJson(route('emenu.buka', $meja->id), ['kode_unik' => $sesi->kode_unik])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->post(route('emenu.masuk'), ['kode_unik' => $sesi->kode_unik])
            ->assertRedirect(route('emenu.meja', $meja->kode_meja));
    }

    public function test_putuskan_sesi_ditolak_bila_masih_ada_pesanan_belum_dibayar(): void
    {
        $meja = $this->siapkanMeja();

        $kategori = Kategori::create(['nama_kategori' => 'Makanan', 'target_kds' => 'dapur']);
        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Uji',
            'slug' => 'nasi-uji',
            'harga' => 10000,
            'stok_menu' => 10,
        ]);

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-SESI-01',
            'meja_id' => $meja->id,
            'total_bayar' => 11000,
            'metode_pembayaran' => 'tunai',
            'status_pesanan' => Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            'status_pembayaran' => Pesanan::PAYMENT_PENDING_PEMBAYARAN,
        ]);
        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 10000,
            'subtotal' => 10000,
        ]);

        $meja->update(['status_meja' => 'terisi']);

        $sesi = SesiMeja::aktifUntuk($meja);

        $this->expectException(\RuntimeException::class);

        try {
            SesiMeja::putuskanSesi($meja);
        } finally {
            $this->assertSame('aktif', $sesi->fresh()->status);
            $this->assertSame('menunggu_pembayaran', $pesanan->fresh()->status_pesanan);
            $this->assertSame(10, $menu->fresh()->stok_menu);
            $this->assertSame('terisi', $meja->fresh()->status_meja);
        }
    }

    public function test_putuskan_sesi_menutup_pesanan_lunas_tanpa_memotong_stok_lagi(): void
    {
        $meja = $this->siapkanMeja();

        $kategori = Kategori::create(['nama_kategori' => 'Makanan', 'target_kds' => 'dapur']);
        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Lunas',
            'slug' => 'nasi-lunas',
            'harga' => 10000,
            'stok_menu' => 10,
        ]);

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-SESI-02',
            'meja_id' => $meja->id,
            'total_bayar' => 10000,
            'metode_pembayaran' => 'tunai',
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
        ]);
        $pesanan->detailPesanan()->create([
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 10000,
            'subtotal' => 10000,
        ]);

        $meja->update(['status_meja' => 'terisi']);
        $lama = SesiMeja::aktifUntuk($meja);

        $baru = SesiMeja::putuskanSesi($meja);

        $this->assertSame('selesai', $lama->fresh()->status);
        $this->assertSame('aktif', $baru->status);
        $this->assertNotSame($lama->kode_unik, $baru->kode_unik);
        $this->assertSame('selesai', $pesanan->fresh()->status_pesanan);
        $this->assertSame(10, $menu->fresh()->stok_menu);
        $this->assertSame('kosong', $meja->fresh()->status_meja);
        $this->assertSame('aktif', SesiMeja::aktifUntuk($meja)->status);
    }
}
