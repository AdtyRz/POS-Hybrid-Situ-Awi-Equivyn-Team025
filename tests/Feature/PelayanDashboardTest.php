<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PelayanDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function pelayan(string $area = 'Lesehan Bawah'): User
    {
        return User::factory()->create([
            'role' => 'pelayan',
            'area' => $area,
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode, string $area): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => $area,
            'kapasitas' => 4,
            'qr_token' => 'QR-'.$kode.'-'.Str::random(12),
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(string $nama, string $target = 'dapur', int $stok = 20, bool $aktif = true): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori '.$target.' '.Str::random(6),
            'target_kds' => $target,
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => $nama,
            'slug' => Str::slug($nama).'-'.Str::lower(Str::random(5)),
            'harga' => 25000,
            'stok_menu' => $stok,
            'is_aktif' => $aktif,
        ]);
    }

    private function pesanan(MejaMakan $meja, Menu $menu, string $status = Pesanan::STATUS_MENUNGGU, ?string $nama = null): Pesanan
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => 'SW-PLY-'.Str::upper(Str::random(8)),
            'meja_id' => $meja->id,
            'nama_pelanggan' => $nama,
            'total_bayar' => 25000,
            'status_pesanan' => $status,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
            'waktu_pesan' => now()->subMinutes(3),
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'subtotal' => 25000,
            'status_item' => $status === Pesanan::STATUS_SIAP ? 'siap' : 'menunggu',
            'catatan' => 'Tanpa sambal',
        ]);

        return $pesanan->fresh();
    }

    private function panggilan(MejaMakan $meja): LogPanggilPelayan
    {
        return LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
            'waktu_panggil' => now()->subMinutes(2),
        ]);
    }

    public function test_semua_halaman_pelayan_memakai_data_database_dan_area_tugas(): void
    {
        $this->withoutVite();

        $pelayan = $this->pelayan();
        $mejaSendiri = $this->meja('LB-01', 'Lesehan Bawah');
        $mejaLain = $this->meja('LA-01', 'Lesehan Atas');
        $menuDapur = $this->menu('Nasi Liwet', 'dapur', 12, true);
        $menuBar = $this->menu('Es Teh', 'bar', 8, true);
        $menuNonaktif = $this->menu('Menu Nonaktif', 'dapur', 10, false);
        $menuHabis = $this->menu('Menu Habis', 'dapur', 0, true);
        $menuKasir = $this->menu('Menu Kasir', 'kasir', 9, true);

        $this->pesanan($mejaSendiri, $menuDapur, Pesanan::STATUS_SIAP, 'Ibu Rina');
        $this->pesanan($mejaLain, $menuDapur, Pesanan::STATUS_SIAP, 'Tamu Lain');
        $this->panggilan($mejaSendiri);
        $this->panggilan($mejaLain);

        SesiMeja::aktifUntuk($mejaSendiri);

        $this->actingAs($pelayan)
            ->get(route('pelayan.notifikasi-panggilan'))
            ->assertOk()
            ->assertSee('LB-01')
            ->assertSee('Ibu Rina')
            ->assertSee('Nasi Liwet')
            ->assertDontSee('LA-01')
            ->assertDontSee('Tamu Lain');

        $this->actingAs($pelayan)
            ->get(route('pelayan.tab-antar'))
            ->assertOk()
            ->assertSee('LB-01')
            ->assertSee('Nasi Liwet')
            ->assertDontSee('LA-01')
            ->assertDontSee("@click='openDenah({", false);

        $this->actingAs($pelayan)
            ->get(route('pelayan.tab-saung'))
            ->assertOk()
            ->assertSee('LB-01')
            ->assertSee('Nasi Liwet')
            ->assertSee('Es Teh')
            ->assertDontSee('LA-01')
            ->assertDontSee('Menu Nonaktif')
            ->assertDontSee('Menu Habis')
            ->assertDontSee('Menu Kasir')
            ->assertDontSee('@click="openDetail({', false)
            ->assertDontSee("@click='openQr({", false);

        $this->actingAs($pelayan)
            ->get(route('pelayan.tab-shift'))
            ->assertOk()
            ->assertSee('Lesehan Bawah')
            ->assertSee('Kesiapan Area')
            ->assertSee('Nasi Liwet')
            ->assertDontSee('Menu Kasir')
            ->assertDontSee('LA-01');
    }

    public function test_pelayan_hanya_bisa_mengubah_panggilan_dan_pesanan_di_area_tugas(): void
    {
        $pelayan = $this->pelayan();
        $mejaSendiri = $this->meja('LB-02', 'Lesehan Bawah');
        $mejaLain = $this->meja('LA-02', 'Lesehan Atas');
        $menu = $this->menu('Penyetan', 'dapur');
        $panggilanSendiri = $this->panggilan($mejaSendiri);
        $panggilanLain = $this->panggilan($mejaLain);
        $pesananSendiri = $this->pesanan($mejaSendiri, $menu, Pesanan::STATUS_SIAP);
        $pesananLain = $this->pesanan($mejaLain, $menu, Pesanan::STATUS_SIAP);

        $this->actingAs($pelayan)
            ->patchJson(route('pelayan.panggilan.ubah', $panggilanSendiri->id), [
                'status_panggilan' => 'selesai',
            ])
            ->assertOk()
            ->assertJsonPath('panggilan.status_panggilan', 'selesai');

        $this->assertSame('selesai', $panggilanSendiri->fresh()->status_panggilan);

        $this->actingAs($pelayan)
            ->patchJson(route('pelayan.panggilan.ubah', $panggilanLain->id), [
                'status_panggilan' => 'selesai',
            ])
            ->assertForbidden();

        $this->assertSame('menunggu', $panggilanLain->fresh()->status_panggilan);

        $this->actingAs($pelayan)
            ->patchJson(route('pesanan.status', $pesananSendiri->kode_pesanan), [
                'status_pesanan' => 'diantar',
            ])
            ->assertOk();

        $this->assertSame(Pesanan::STATUS_DIANTAR, $pesananSendiri->fresh()->status_pesanan);

        $this->actingAs($pelayan)
            ->patchJson(route('pesanan.status', $pesananLain->kode_pesanan), [
                'status_pesanan' => 'diantar',
            ])
            ->assertForbidden();

        $this->assertSame(Pesanan::STATUS_SIAP, $pesananLain->fresh()->status_pesanan);
    }

    public function test_pelayan_bisa_membuat_pesanan_tambahan_dan_membuka_sesi_dari_database(): void
    {
        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-03', 'Lesehan Bawah');
        $mejaLain = $this->meja('LA-03', 'Lesehan Atas');
        $menuDapur = $this->menu('Gurame Bakar', 'dapur', 10);
        $menuBar = $this->menu('Jeruk Hangat', 'bar', 6);
        $sesi = SesiMeja::aktifUntuk($meja);
        $sesi->update(['nama_pelanggan' => 'Bapak Andi']);

        $response = $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'jumlah' => [
                    $menuDapur->id => 2,
                    $menuBar->id => 1,
                ],
                'catatan' => 'Satu tanpa nasi',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'ok');

        $kodePesanan = $response->json('pesanan.kode_pesanan');
        $pesanan = Pesanan::where('kode_pesanan', $kodePesanan)->firstOrFail();

        $this->assertSame($meja->id, $pesanan->meja_id);
        $this->assertSame('Bapak Andi', $pesanan->nama_pelanggan);
        $this->assertSame(Pesanan::STATUS_MENUNGGU, $pesanan->status_pesanan);
        $this->assertSame(3, $pesanan->detailPesanan()->sum('jumlah'));
        $this->assertSame(2, $pesanan->detailPesanan()->where('menu_id', $menuDapur->id)->value('jumlah'));
        $this->assertSame(1, $pesanan->detailPesanan()->where('menu_id', $menuBar->id)->value('jumlah'));

        $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $mejaLain->id,
                'jumlah' => [$menuDapur->id => 1],
            ])
            ->assertForbidden();

        $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'jumlah' => [$menuDapur->id => 99],
            ])
            ->assertStatus(422);

        $bukaMeja = $this->actingAs($pelayan)
            ->postJson(route('pelayan.meja.buka', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('meja', $meja->kode_meja)
            ->assertJsonPath('area', $meja->area)
            ->assertJsonStructure(['kode_unik', 'qr_url']);

        $kodeSesi = (string) $bukaMeja->json('kode_unik');

        $this->assertContains(strlen($kodeSesi), [4, 6]);
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $kodeSesi);

        $this->actingAs($pelayan)
            ->postJson(route('pelayan.meja.buka', $mejaLain->kode_meja))
            ->assertForbidden();
    }
}
