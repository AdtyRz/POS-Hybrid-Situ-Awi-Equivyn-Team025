<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;
use App\Models\User;
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

    private function siapkanMejaLain(): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => 'LB-78',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-78-abcdefghijklmnop',
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

    public function test_pelanggan_terikat_satu_meja_ditolak_membuka_meja_lain_dari_beranda(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $lain = $this->siapkanMejaLain();
        $sesiLain = SesiMeja::aktifUntuk($lain);

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->post(route('emenu.masuk'), ['kode_unik' => $sesiLain->kode_unik])
            ->assertRedirect(route('emenu.index'))
            ->assertSessionHasErrors('kode_unik')
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id)
            ->assertSessionMissing('sesi_meja_' . $lain->id);
    }

    public function test_pelanggan_terikat_satu_meja_ditolak_membuka_meja_lain(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $lain = $this->siapkanMejaLain();
        $sesiLain = SesiMeja::aktifUntuk($lain);
        $pesan = 'Anda masih terhubung ke LB-77. Keluar dari sesi meja tersebut sebelum membuka meja lain.';

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->postJson(route('emenu.buka', $lain->id), ['kode_unik' => $sesiLain->kode_unik])
            ->assertStatus(403)
            ->assertJsonPath('message', $pesan)
            ->assertJsonPath('errors.kode_unik.0', $pesan)
            ->assertSessionMissing('sesi_meja_' . $lain->id);

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->from(route('emenu.kunci', $lain->kode_meja))
            ->post(route('emenu.buka', $lain->id), ['kode_unik' => $sesiLain->kode_unik])
            ->assertRedirect(route('emenu.kunci', $lain->kode_meja))
            ->assertSessionHasErrors('kode_unik')
            ->assertSessionMissing('sesi_meja_' . $lain->id);
    }

    public function test_pelanggan_terikat_satu_meja_ditolak_melihat_meja_lain(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $lain = $this->siapkanMejaLain();

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $lain->kode_meja))
            ->assertRedirect(route('emenu.index'))
            ->assertSessionHasErrors('kode_unik');

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.kunci', $lain->kode_meja))
            ->assertRedirect(route('emenu.index'))
            ->assertSessionHasErrors('kode_unik');
    }

    public function test_meja_yang_sama_tetap_bisa_dibuka_ulang_dan_memakai_form_keluar(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->postJson(route('emenu.buka', $meja->id), ['kode_unik' => $sesi->kode_unik])
            ->assertOk()
            ->assertJsonPath('redirect', route('emenu.meja', $meja->kode_meja));

        $this->withSession(['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $meja->kode_meja))
            ->assertOk()
            ->assertSee('id="formKeluar"', false)
            ->assertSee('action="' . route('emenu.keluar') . '"', false);
    }

    public function test_panah_kembali_mengeluarkan_sesi_pelanggan_dan_kembali_ke_beranda(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $lain = $this->siapkanMejaLain();
        $sesiLain = SesiMeja::aktifUntuk($lain);

        $this->withSession([
            'sesi_meja_' . $meja->id => $sesi->id,
            'riwayat_meja_' . $meja->id => ['SW-LAMA-1'],
            'sesi_meja_' . $lain->id => $sesiLain->id,
        ])
            ->post(route('emenu.keluar'))
            ->assertRedirect(route('emenu.index'))
            ->assertSessionMissing('sesi_meja_' . $meja->id)
            ->assertSessionMissing('riwayat_meja_' . $meja->id)
            ->assertSessionMissing('sesi_meja_' . $lain->id);

        // Setelah keluar, pelanggan bebas membuka meja lain.
        $this->post(route('emenu.masuk'), ['kode_unik' => $sesiLain->kode_unik])
            ->assertRedirect(route('emenu.meja', $lain->kode_meja))
            ->assertSessionHas('sesi_meja_' . $lain->id, $sesiLain->id);
    }

    public function test_keluar_pelanggan_tidak_mengeluarkan_akun_staf(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        $this->actingAs($kasir)
            ->post(route('emenu.keluar'))
            ->assertRedirect(route('emenu.index'));

        $this->assertAuthenticatedAs($kasir);
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

    public function test_sesi_aktif_menyesuaikan_panjang_kode_setelah_pengaturan_diubah(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->assertSame(4, strlen($sesi->kode_unik));

        Pengaturan::simpanBanyak(['encryption_level' => '6 Digit']);

        $sesiAktif = SesiMeja::aktifUntuk($meja);

        $this->assertSame($sesi->id, $sesiAktif->id);
        $this->assertSame(6, strlen($sesiAktif->kode_unik));
        $this->assertNotSame($sesi->kode_unik, $sesiAktif->kode_unik);

        $this->get(route('emenu.kunci', $meja->kode_meja))
            ->assertOk()
            ->assertSee('kunciApp(6,', false);
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

    public function test_sesi_lintas_hari_tanpa_pesanan_aktif_ditutup_dan_tidak_menghitung_meja_terisi(): void
    {
        $mejaAktif = $this->siapkanMeja();
        $mejaBasi = MejaMakan::create([
            'kode_meja' => 'LB-78',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-78-abcdefghijklmnop',
            'status_meja' => 'kosong',
        ]);
        $mejaPesanan = MejaMakan::create([
            'kode_meja' => 'LB-79',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-79-abcdefghijklmnop',
            'status_meja' => 'kosong',
        ]);

        $aktif = SesiMeja::aktifUntuk($mejaAktif);

        $basi = SesiMeja::create([
            'meja_id' => $mejaBasi->id,
            'kode_unik' => '1111',
            'status' => 'aktif',
            'dibuka_pada' => now()->subDay(),
        ]);

        $denganPesanan = SesiMeja::create([
            'meja_id' => $mejaPesanan->id,
            'kode_unik' => '2222',
            'status' => 'aktif',
            'dibuka_pada' => now()->subDays(2),
        ]);

        $kategori = Kategori::create(['nama_kategori' => 'Makanan', 'target_kds' => 'dapur']);
        $menu = Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Aktif',
            'slug' => 'nasi-aktif',
            'harga' => 10000,
            'stok_menu' => 10,
        ]);

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-SESI-LINTAS',
            'meja_id' => $mejaPesanan->id,
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

        $this->assertSame(1, SesiMeja::tutupSesiLintasHari());
        $this->assertSame('selesai', $basi->fresh()->status);
        $this->assertSame('aktif', $denganPesanan->fresh()->status);

        $this->assertSame(2, SesiMeja::where('status', 'aktif')->count());
        $this->assertSame($aktif->id, SesiMeja::where('meja_id', $mejaAktif->id)->where('status', 'aktif')->value('id'));
    }

    public function test_aktif_untuk_mengganti_sesi_lintas_hari_yang_tidak_punya_pesanan_aktif(): void
    {
        $meja = $this->siapkanMeja();

        $lama = SesiMeja::create([
            'meja_id' => $meja->id,
            'kode_unik' => '3333',
            'status' => 'aktif',
            'dibuka_pada' => now()->subDays(2),
        ]);

        $baru = SesiMeja::aktifUntuk($meja);

        $this->assertSame('selesai', $lama->fresh()->status);
        $this->assertSame('aktif', $baru->status);
        $this->assertNotSame($lama->id, $baru->id);
    }
}
