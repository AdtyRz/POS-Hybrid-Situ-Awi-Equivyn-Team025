<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirStrukTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        return User::create([
            'nama' => 'Kasir Uji',
            'email' => 'kasir-struk-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode = 'LB-88'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-'.strtolower($kode).'-abcdefghij',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(int $harga = 10000): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Struk '.uniqid(),
            'slug' => 'nasi-struk-'.uniqid(),
            'harga' => $harga,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);
    }

    private function pesanan(MejaMakan $meja, Menu $menu, int $jumlah = 1): Pesanan
    {
        return app(PesananService::class)->buat(
            $meja,
            [$menu->id => $jumlah],
            [],
            ['asal' => 'kasir', 'metode' => 'tunai'],
        );
    }

    private function lunasi(Pesanan $pesanan): void
    {
        $pesanan->update([
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
        ]);
    }

    public function test_struk_memuat_semua_pesanan_lunas_agar_bisa_dicetak_ulang(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja();
        $menu = $this->menu(12500);

        $sudahDicetak = $this->pesanan($meja, $menu, 1);
        $this->lunasi($sudahDicetak);
        $sudahDicetak->update(['printed_at' => now()->subMinute()]);

        $belumDicetak = $this->pesanan($meja, $menu, 2);
        $this->lunasi($belumDicetak);

        $belumBayar = $this->pesanan($meja, $menu, 3);

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));

        $response->assertOk();

        $saung = collect($response->viewData('saungsJson'))
            ->firstWhere('id', $meja->kode_meja);

        $this->assertTrue($saung['hasReceiptOrders']);
        $this->assertEqualsCanonicalizing(
            [$sudahDicetak->kode_pesanan, $belumDicetak->kode_pesanan],
            $saung['receiptOrderIds']->all(),
        );
        $this->assertCount(2, $saung['receiptItems']);
        $this->assertSame(37500.0, $saung['receiptTotal']);
        $this->assertNotContains($belumBayar->kode_pesanan, $saung['receiptOrderIds']->all());

        $statusCetak = collect($saung['receiptOrders'])
            ->mapWithKeys(fn ($order) => [$order['kode_pesanan'] => $order['printed']]);

        $this->assertTrue($statusCetak[$sudahDicetak->kode_pesanan]);
        $this->assertFalse($statusCetak[$belumDicetak->kode_pesanan]);
    }

    public function test_struk_hanya_memuat_pesanan_lunas_pada_sesi_terakhir(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja('LB-91');
        $menu = $this->menu(10000);

        $sesiLama = $this->pesanan($meja, $menu);
        $this->lunasi($sesiLama);
        $sesiLama->update(['waktu_pesan' => now()->subHour()]);

        SesiMeja::create([
            'meja_id' => $meja->id,
            'kode_unik' => '4321',
            'status' => 'aktif',
            'dibuka_pada' => now()->subMinutes(30),
        ]);

        $baruSatu = $this->pesanan($meja, $menu);
        $this->lunasi($baruSatu);
        $baruSatu->update(['waktu_pesan' => now()->subMinutes(10)]);

        $baruDua = $this->pesanan($meja, $menu, 2);
        $this->lunasi($baruDua);
        $baruDua->update(['waktu_pesan' => now()->subMinutes(5)]);

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));
        $saung = collect($response->viewData('saungsJson'))
            ->firstWhere('id', $meja->kode_meja);

        $this->assertEqualsCanonicalizing(
            [$baruSatu->kode_pesanan, $baruDua->kode_pesanan],
            $saung['receiptOrderIds']->all(),
        );
        $this->assertNotContains($sesiLama->kode_pesanan, $saung['receiptOrderIds']->all());
        $this->assertSame(30000.0, $saung['receiptTotal']);
    }

    public function test_menandai_struk_hanya_mengubah_pesanan_lunas_dan_idempoten(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja('LB-89');
        $menu = $this->menu();

        $lunas = $this->pesanan($meja, $menu);
        $this->lunasi($lunas);

        $belumBayar = $this->pesanan($meja, $menu);

        $payload = [
            'meja_id' => $meja->id,
            'kode_pesanan' => [$lunas->kode_pesanan, $belumBayar->kode_pesanan],
        ];

        $this->actingAs($kasir)
            ->postJson(route('kasir.struk.tandai'), $payload)
            ->assertOk()
            ->assertJson(['success' => true, 'jumlah' => 1]);

        $this->assertNotNull($lunas->fresh()->printed_at);
        $this->assertNull($belumBayar->fresh()->printed_at);

        $this->actingAs($kasir)
            ->postJson(route('kasir.struk.tandai'), $payload)
            ->assertOk()
            ->assertJson(['success' => true, 'jumlah' => 0]);
    }

    public function test_semua_cluster_saung_memakai_kartu_dan_struk_gabungan_yang_sama(): void
    {
        $kasir = $this->kasir();
        $meja = $this->meja('LB-90');
        $menuSatu = $this->menu(10000);
        $menuDua = $this->menu(15000);

        $pesananSatu = $this->pesanan($meja, $menuSatu, 2);
        $this->lunasi($pesananSatu);

        $pesananDua = $this->pesanan($meja, $menuDua, 1);
        $this->lunasi($pesananDua);

        $response = $this->actingAs($kasir)->get(route('kasir.dashboard'));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertSame(3, substr_count($html, 'data-saung-card'));
        $this->assertStringContainsString('Cluster 1: Lesehan Bawah', $html);
        $this->assertStringContainsString('Cluster 2: Lesehan Atas', $html);
        $this->assertStringContainsString('Cluster 3: Kursi Atas', $html);
        $this->assertSame(1, substr_count($html, "const bagian = dokumen.createElement('section');"));
        $this->assertStringContainsString('Semua pesanan yang dicentang digabung menjadi SATU struk.', $html);

        $saung = collect($response->viewData('saungsJson'))
            ->firstWhere('id', $meja->kode_meja);

        $this->assertTrue($saung['hasReceiptOrders']);
        $this->assertCount(2, $saung['receiptOrderIds']);
        $this->assertCount(2, $saung['receiptItems']);
        $this->assertSame(35000.0, $saung['receiptTotal']);

        $this->actingAs($kasir)
            ->postJson(route('kasir.struk.tandai'), [
                'meja_id' => $meja->id,
                'kode_pesanan' => $saung['receiptOrderIds']->all(),
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'jumlah' => 2]);

        $this->assertNotNull($pesananSatu->fresh()->printed_at);
        $this->assertNotNull($pesananDua->fresh()->printed_at);

        // Setelah dicetak, pesanan lunas harus tetap tampil agar bisa dicetak ulang.
        $responseUlang = $this->actingAs($kasir)->get(route('kasir.dashboard'));
        $saungUlang = collect($responseUlang->viewData('saungsJson'))
            ->firstWhere('id', $meja->kode_meja);

        $this->assertTrue($saungUlang['hasReceiptOrders']);
        $this->assertEqualsCanonicalizing(
            [$pesananSatu->kode_pesanan, $pesananDua->kode_pesanan],
            $saungUlang['receiptOrderIds']->all(),
        );
        $this->assertSame(35000.0, $saungUlang['receiptTotal']);
    }
}
