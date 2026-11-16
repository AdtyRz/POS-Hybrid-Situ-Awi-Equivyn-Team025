<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransferPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function kasir(): User
    {
        return User::create([
            'nama' => 'Kasir Transfer',
            'email' => 'kasir-transfer-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'kasir',
            'is_aktif' => true,
        ]);
    }

    private function meja(string $kode = 'TR-01'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-'.Str::lower($kode).'-abcdefghij',
            'status_meja' => 'kosong',
        ]);
    }

    private function menu(): Menu
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Makanan '.uniqid(),
            'target_kds' => 'dapur',
        ]);

        return Menu::create([
            'kategori_id' => $kategori->id,
            'nama_menu' => 'Nasi Transfer '.uniqid(),
            'slug' => 'nasi-transfer-'.uniqid(),
            'harga' => 25000,
            'stok_menu' => 20,
            'is_aktif' => true,
        ]);
    }

    private function pesananTransfer(MejaMakan $meja, Menu $menu): Pesanan
    {
        return app(PesananService::class)->buat(
            $meja,
            [$menu->id => 1],
            [],
            [
                'asal' => 'emenu',
                'metode' => 'transfer',
                'nama_pelanggan' => 'Tamu Transfer',
            ],
        );
    }

    public function test_status_pesanan_transfer_menampilkan_rekening_tujuan(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();
        $pesanan = $this->pesananTransfer($meja, $menu);

        $this->withSession([
            'riwayat_meja_'.$meja->id => [$pesanan->kode_pesanan],
        ])->get(route('emenu.status', $pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Pembayaran Transfer')
            ->assertSee('BCA')
            ->assertSee('1234567890')
            ->assertSee('Saung Situ Awi');
    }

    public function test_kasir_dapat_mengonfirmasi_pembayaran_transfer(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();
        $pesanan = $this->pesananTransfer($meja, $menu);
        $kasir = $this->kasir();

        $this->actingAs($kasir)
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'transfer',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertRedirect(route('kasir.dashboard', ['meja' => $meja->kode_meja]))
            ->assertSessionHas('sukses');

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_SUDAH_BAYAR, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status_pesanan);
        $this->assertSame('transfer', $pesanan->metode_pembayaran);
        $this->assertSame(19, (int) $menu->fresh()->stok_menu);

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'transfer',
            'status_pembayaran' => 'lunas',
            'jumlah_bayar' => 25000,
        ]);
    }

    public function test_pesanan_transfer_tidak_bisa_dikonfirmasi_sebagai_tunai(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();
        $pesanan = $this->pesananTransfer($meja, $menu);

        $this->actingAs($this->kasir())
            ->post(route('kasir.pembayaran.store'), [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => (float) $pesanan->total_bayar,
            ])
            ->assertSessionHasErrors('metode_pembayaran');

        $pesanan->refresh();

        $this->assertSame(Pesanan::PAYMENT_PENDING_PEMBAYARAN, $pesanan->status_pembayaran);
        $this->assertSame(Pesanan::STATUS_MENUNGGU_PEMBAYARAN, $pesanan->status_pesanan);
        $this->assertSame(20, (int) $menu->fresh()->stok_menu);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
    }

    public function test_dashboard_kasir_menandai_pesanan_transfer_menunggu_konfirmasi(): void
    {
        $meja = $this->meja();
        $menu = $this->menu();
        $this->pesananTransfer($meja, $menu);

        $this->actingAs($this->kasir())
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('waiting_transfer')
            ->assertSee('MENUNGGU KONFIRMASI TRANSFER');
    }
}
