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
use App\Services\IotStatusService;
use App\Services\MidtransService;
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
                'metode' => 'tunai',
                'jumlah' => [
                    $menuDapur->id => 2,
                    $menuBar->id => 1,
                ],
                'catatan' => 'Satu tanpa nasi',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('metode', 'tunai');

        $kodePesanan = $response->json('pesanan.kode_pesanan');
        $pesanan = Pesanan::where('kode_pesanan', $kodePesanan)->firstOrFail();

        $this->assertSame($meja->id, $pesanan->meja_id);
        $this->assertSame('Bapak Andi', $pesanan->nama_pelanggan);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame('tunai', $pesanan->metode_pembayaran);
        $this->assertSame(3, $pesanan->detailPesanan()->sum('jumlah'));
        $this->assertSame(2, $pesanan->detailPesanan()->where('menu_id', $menuDapur->id)->value('jumlah'));
        $this->assertSame(1, $pesanan->detailPesanan()->where('menu_id', $menuBar->id)->value('jumlah'));
        $this->assertSame(8, (int) $menuDapur->fresh()->stok_menu);
        $this->assertSame(5, (int) $menuBar->fresh()->stok_menu);
        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
        ]);

        $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $mejaLain->id,
                'metode' => 'tunai',
                'jumlah' => [$menuDapur->id => 1],
            ])
            ->assertForbidden();

        $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'metode' => 'tunai',
                'jumlah' => [$menuDapur->id => 99],
            ])
            ->assertStatus(422);

        $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'jumlah' => [$menuDapur->id => 1],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('metode');

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

    public function test_pelayan_bisa_mengakhiri_sesi_aktif_dan_menolak_tagihan_belum_lunas(): void
    {
        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-04', 'Lesehan Bawah');
        $mejaBelumLunas = $this->meja('LB-05', 'Lesehan Bawah');
        $mejaLain = $this->meja('LA-04', 'Lesehan Atas');
        $menu = $this->menu('Sate Ayam', 'dapur');

        $sesiLama = SesiMeja::aktifUntuk($meja);
        $pesananLunas = $this->pesanan($meja, $menu, Pesanan::STATUS_DIPROSES);
        $pesananLunas->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $this->actingAs($pelayan)
            ->patchJson(route('pelayan.session.tutup', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meja', $meja->kode_meja);

        $this->assertSame('selesai', $sesiLama->fresh()->status);
        $this->assertSame(Pesanan::STATUS_SELESAI, $pesananLunas->fresh()->status_pesanan);
        $this->assertSame('kosong', $meja->fresh()->status_meja);

        $sesiBaru = SesiMeja::where('meja_id', $meja->id)
            ->where('status', 'aktif')
            ->latest('id')
            ->first();

        $this->assertNotNull($sesiBaru);
        $this->assertNotSame($sesiLama->kode_unik, $sesiBaru->kode_unik);

        SesiMeja::aktifUntuk($mejaBelumLunas);
        $pesananBelumLunas = $this->pesanan($mejaBelumLunas, $menu, Pesanan::STATUS_DIPROSES);

        $this->actingAs($pelayan)
            ->patchJson(route('pelayan.session.tutup', $mejaBelumLunas->kode_meja))
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesananBelumLunas->fresh()->status_pesanan);
        $this->assertSame('aktif', SesiMeja::aktifUntuk($mejaBelumLunas)->status);

        $this->actingAs($pelayan)
            ->patchJson(route('pelayan.session.tutup', $mejaLain->kode_meja))
            ->assertForbidden();
    }

    public function test_sesi_baru_setelah_diakhiri_tidak_lagi_menghitung_meja_terisi(): void
    {
        $this->withoutVite();

        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-06', 'Lesehan Bawah');
        $menu = $this->menu('Ikan Bakar', 'dapur');

        $sesiLama = SesiMeja::aktifUntuk($meja);
        $pesanan = $this->pesanan($meja, $menu, Pesanan::STATUS_DIPROSES);
        $pesanan->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $response = $this->actingAs($pelayan)
            ->patchJson(route('pelayan.session.tutup', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('session_occupied', false);

        $kodeBaru = (string) $response->json('kode_unik');
        $this->assertNotSame($sesiLama->kode_unik, $kodeBaru);
        $this->assertSame($kodeBaru, SesiMeja::aktifUntuk($meja)->kode_unik);

        $halaman = $this->actingAs($pelayan)
            ->get(route('pelayan.tab-saung'))
            ->assertOk()
            ->assertViewHas('terisiCount', 0)
            ->assertViewHas('tersediaCount', 1);

        $table = collect($halaman->viewData('tables'))->firstWhere('id', $meja->id);

        $this->assertNotNull($table);
        $this->assertSame($kodeBaru, $table['session_code']);
        $this->assertFalse($table['session_occupied']);
        $this->assertSame('tersedia', $table['status']);
    }

    public function test_meja_dengan_pesanan_selesai_dalam_sesi_tetap_terisi(): void
    {
        $this->withoutVite();

        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-08', 'Lesehan Bawah');
        $menu = $this->menu('Ayam Bakar', 'dapur');

        $sesi = SesiMeja::aktifUntuk($meja);
        $pesanan = $this->pesanan($meja, $menu, Pesanan::STATUS_SELESAI);
        $pesanan->update([
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'waktu_pesan' => $sesi->dibuka_pada->copy()->addMinute(),
        ]);

        $halaman = $this->actingAs($pelayan)
            ->get(route('pelayan.tab-saung'))
            ->assertOk()
            ->assertViewHas('terisiCount', 1)
            ->assertViewHas('tersediaCount', 0);

        $table = collect($halaman->viewData('tables'))->firstWhere('id', $meja->id);

        $this->assertNotNull($table);
        $this->assertSame('terisi', $table['status']);
        $this->assertSame('Terisi', $table['status_label']);
        $this->assertTrue($table['session_occupied']);
        $this->assertSame(1, $table['order_count']);
        $this->assertSame('Rp 25.000', $table['total_format']);

        $this->actingAs($pelayan)
            ->get(route('pelayan.notifikasi-panggilan'))
            ->assertOk()
            ->assertSee('Terisi');
    }

    public function test_tab_saung_tidak_menghitung_sesi_lintas_hari_sebagai_meja_terisi(): void
    {
        $this->withoutVite();

        $pelayan = $this->pelayan();
        $mejaAktif = $this->meja('LB-01', 'Lesehan Bawah');
        $mejaBasi = $this->meja('LB-02', 'Lesehan Bawah');
        $menu = $this->menu('Nasi Liwet');

        $this->pesanan($mejaAktif, $menu, Pesanan::STATUS_DIPROSES);

        $basi = SesiMeja::create([
            'meja_id' => $mejaBasi->id,
            'kode_unik' => '9191',
            'status' => 'aktif',
            'dibuka_pada' => now()->subDay(),
        ]);

        $response = $this->actingAs($pelayan)
            ->get(route('pelayan.tab-saung'))
            ->assertOk();

        $this->assertSame('selesai', $basi->fresh()->status);
        $response->assertViewHas('terisiCount', 1);
        $response->assertViewHas('tersediaCount', 1);
    }

    public function test_pesanan_qris_pelayan_menunggu_settlement_dan_belum_masuk_kds(): void
    {
        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-07', 'Lesehan Bawah');
        $menu = $this->menu('Kepiting Rebus', 'dapur', 6);

        $this->partialMock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createQrisCharge')->once()->andReturn([
                'order_id' => 'SW-QRIS-TEST',
                'transaction_id' => 'trx-pending',
                'qr_url' => 'https://example.test/qris-pending.png',
                'transaction_status' => 'pending',
            ]);
            $mock->shouldReceive('qrisExpiryMinutes')->andReturn(5);
        });

        $response = $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'metode' => 'qris',
                'jumlah' => [$menu->id => 2],
            ])
            ->assertCreated()
            ->assertJsonPath('metode', 'qris')
            ->assertJsonPath('qris.qr_url', 'https://example.test/qris-pending.png')
            ->assertJsonPath('qris.expires_in', 300);

        $pesanan = Pesanan::where('kode_pesanan', $response->json('pesanan.kode_pesanan'))->firstOrFail();

        $this->assertSame(Pesanan::STATUS_MENUNGGU_VERIFIKASI, $pesanan->status_pesanan);
        $this->assertSame(Pesanan::PAYMENT_PENDING_VERIFIKASI, $pesanan->status_pembayaran);
        $this->assertSame('qris', $pesanan->metode_pembayaran);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
        $this->assertSame(6, (int) $menu->fresh()->stok_menu);


        $this->assertFalse(
            Pesanan::whereKey($pesanan->id)
                ->whereIn('status_pesanan', [Pesanan::STATUS_MENUNGGU, Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP])
                ->exists()
        );
    }

    public function test_qris_pelayan_lunas_setelah_settlement_lalu_masuk_kds(): void
    {
        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-08', 'Lesehan Bawah');
        $menu = $this->menu('Udang Bakar', 'dapur', 4);

        $this->partialMock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createQrisCharge')->once()->andReturn([
                'order_id' => 'SW-QRIS-SETTLE',
                'transaction_id' => 'trx-settle',
                'qr_url' => 'https://example.test/qris-settle.png',
                'transaction_status' => 'pending',
            ]);
            $mock->shouldReceive('qrisExpiryMinutes')->andReturn(5);
            $mock->shouldReceive('getTransactionStatus')->once()->andReturnUsing(fn ($orderId) => (object) [
                'order_id' => $orderId,
                'gross_amount' => '75000.00',
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'transaction_id' => 'trx-settle',
            ]);
        });

        $response = $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'metode' => 'qris',
                'jumlah' => [$menu->id => 3],
            ])
            ->assertCreated();

        $kode = (string) $response->json('pesanan.kode_pesanan');

        $this->actingAs($pelayan)
            ->getJson(route('pelayan.pesanan.qris-status', $kode))
            ->assertOk()
            ->assertJsonPath('lunas', true)
            ->assertJsonPath('status_pesanan', Pesanan::STATUS_DIPROSES)
            ->assertJsonPath('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR);

        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame(1, (int) $menu->fresh()->stok_menu);
        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'qris',
            'status_pembayaran' => 'lunas',
            'transaction_id' => 'trx-settle',
        ]);

        $this->assertTrue(
            Pesanan::whereKey($pesanan->id)
                ->whereIn('status_pesanan', [Pesanan::STATUS_MENUNGGU, Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP])
                ->exists()
        );
    }

    public function test_pelayan_bisa_membatalkan_pesanan_qris_yang_belum_dibayar(): void
    {
        $pelayan = $this->pelayan();
        $pelayanLain = $this->pelayan('Lesehan Atas');
        $meja = $this->meja('LB-09', 'Lesehan Bawah');
        $menu = $this->menu('Cumi Goreng', 'dapur', 5);

        $this->partialMock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createQrisCharge')->once()->andReturn([
                'order_id' => 'SW-QRIS-CANCEL',
                'transaction_id' => 'trx-cancel',
                'qr_url' => 'https://example.test/qris-cancel.png',
                'transaction_status' => 'pending',
            ]);
            $mock->shouldReceive('qrisExpiryMinutes')->andReturn(5);
            $mock->shouldReceive('getTransactionStatus')->once()->andReturn((object) [
                'transaction_status' => 'pending',
                'fraud_status' => 'accept',
                'transaction_id' => 'trx-cancel',
            ]);
        });

        $response = $this->actingAs($pelayan)
            ->postJson(route('pelayan.pesanan.store'), [
                'meja_id' => $meja->id,
                'metode' => 'qris',
                'jumlah' => [$menu->id => 1],
            ])
            ->assertCreated();

        $kode = (string) $response->json('pesanan.kode_pesanan');

        $this->actingAs($pelayanLain)
            ->deleteJson(route('pelayan.pesanan.batal', $kode))
            ->assertForbidden();

        $this->actingAs($pelayan)
            ->deleteJson(route('pelayan.pesanan.batal', $kode))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('status_pesanan', Pesanan::STATUS_DIBATALKAN);

        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        $this->assertSame(Pesanan::STATUS_DIBATALKAN, $pesanan->status_pesanan);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
        $this->assertSame(5, (int) $menu->fresh()->stok_menu);

        $this->actingAs($pelayan)
            ->getJson(route('pelayan.pesanan.qris-status', $kode))
            ->assertStatus(410)
            ->assertJsonPath('dibatalkan', true);
    }

    public function test_tab_antar_memakai_tombol_status_tunggal_untuk_pesanan(): void
    {
        $this->withoutVite();

        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-11', 'Lesehan Bawah');
        $menu = $this->menu('Nasi Timbel', 'dapur');

        $this->pesanan($meja, $menu, Pesanan::STATUS_SIAP);
        $this->pesanan($meja, $menu, Pesanan::STATUS_DIANTAR);

        $this->actingAs($pelayan)
            ->get(route('pelayan.tab-antar'))
            ->assertOk()
            ->assertSee('Konfirmasi Kirim')
            ->assertSee('Pesanan Sudah Dikirim')
            ->assertDontSee('Selesai Diantar');
    }

    public function test_pelayan_menyelesaikan_pengantaran_dan_pesanan_tetap_di_sesi(): void
    {
        $this->withoutVite();

        $pelayan = $this->pelayan();
        $meja = $this->meja('LB-10', 'Lesehan Bawah');
        $menu = $this->menu('Sate Ayam', 'dapur');

        $sesi = SesiMeja::aktifUntuk($meja);
        $sesi->update(['dibuka_pada' => now()->subMinutes(5)]);

        $pesanan = $this->pesanan($meja, $menu, Pesanan::STATUS_DIANTAR);
        $pesanan->update(['status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR]);

        $this->actingAs($pelayan)
            ->get(route('pelayan.tab-antar'))
            ->assertOk()
            ->assertViewHas('deliveringCount', 1);

        $this->actingAs($pelayan)
            ->patchJson(route('pesanan.status', $pesanan->kode_pesanan), [
                'status_pesanan' => Pesanan::STATUS_SELESAI,
            ])
            ->assertOk()
            ->assertJsonPath('pesanan.status_pesanan', Pesanan::STATUS_SELESAI);

        $this->assertSame(Pesanan::STATUS_SELESAI, $pesanan->fresh()->status_pesanan);

        $this->assertSame('terisi', $meja->fresh()->status_meja);

        $this->actingAs($pelayan)
            ->get(route('pelayan.tab-antar'))
            ->assertOk()
            ->assertViewHas('deliveringCount', 0);

        $lcd = app(IotStatusService::class)->detailMeja($meja->kode_meja);

        $this->assertSame('lunas', $lcd['data']['fase']);
        $this->assertSame(0, $lcd['data']['jumlah_pesanan']);
        $this->assertSame(1, $lcd['data']['jumlah_selesai']);
        $this->assertTrue($lcd['data']['ada_pesanan']);
        $this->assertCount(0, $lcd['data']['pesanan']);
        $this->assertSame(25000.0, (float) $lcd['data']['total_bayar']);
    }
}
