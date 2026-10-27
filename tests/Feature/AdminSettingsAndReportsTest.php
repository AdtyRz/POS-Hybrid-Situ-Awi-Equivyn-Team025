<?php

namespace Tests\Feature;

use App\Models\MejaMakan;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminSettingsAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private function meja(string $kode = 'LB-88'): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => $kode,
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-' . strtolower($kode),
            'status_meja' => 'kosong',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_sesi_otomatis_menutup_sesi_lama_tanpa_pesanan_aktif(): void
    {
        Pengaturan::simpanBanyak([
            'session_auto_close' => true,
            'session_expiry_minutes' => 30,
        ]);

        $meja = $this->meja();
        $lama = SesiMeja::create([
            'meja_id' => $meja->id,
            'kode_unik' => '1234',
            'status' => 'aktif',
            'dibuka_pada' => now()->subHours(2),
        ]);

        $baru = SesiMeja::aktifUntuk($meja);

        $this->assertSame('selesai', $lama->fresh()->status);
        $this->assertSame('aktif', $baru->status);
        $this->assertNotSame($lama->id, $baru->id);
        $this->assertNotSame($lama->kode_unik, $baru->kode_unik);
    }

    public function test_sesi_dengan_pesanan_aktif_tidak_ditutup_otomatis(): void
    {
        Pengaturan::simpanBanyak([
            'session_auto_close' => true,
            'session_expiry_minutes' => 30,
        ]);

        $meja = $this->meja();
        $sesi = SesiMeja::create([
            'meja_id' => $meja->id,
            'kode_unik' => '4321',
            'status' => 'aktif',
            'dibuka_pada' => now()->subHours(2),
        ]);

        Pesanan::create([
            'kode_pesanan' => 'AWI-AUTO-CLOSE',
            'meja_id' => $meja->id,
            'total_bayar' => 25000,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
        ]);

        $this->assertSame($sesi->id, SesiMeja::aktifUntuk($meja)->id);
        $this->assertSame('aktif', $sesi->fresh()->status);
    }

    public function test_admin_dapat_mengunggah_logo_struk(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.pengaturan-sistem.logo'), [
            'logo' => UploadedFile::fake()->image('logo-resto.png', 320, 180),
        ]);

        $response->assertRedirect(route('admin.pengaturan-sistem'));
        $response->assertSessionHas('sukses');

        $path = Pengaturan::nilai('logo_path');

        $this->assertNotEmpty($path);
        $this->assertStringStartsWith('asset/resto/', $path);
        $this->assertFileExists(public_path($path));

        File::delete(public_path($path));
    }

    public function test_cetak_rekap_shift_menampilkan_transaksi_shift_aktif(): void
    {
        $this->travelTo(Carbon::parse('2026-10-27 10:00:00', 'Asia/Jakarta'));

        $meja = $this->meja('LB-89');

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'AWI-SHIFT-01',
            'meja_id' => $meja->id,
            'total_bayar' => 25000,
            'status_pesanan' => Pesanan::STATUS_SIAP,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'metode_pembayaran' => 'tunai',
        ]);

        // useCurrent() pada SQLite memakai jam nyata, bukan jam test.
        $pesanan->update(['waktu_pesan' => now()]);

        $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan.cetak-shift'))
            ->assertOk()
            ->assertSee('Rekap Shift')
            ->assertSee($pesanan->kode_pesanan);
    }

    public function test_halaman_pengaturan_sistem_dapat_dirender(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.pengaturan-sistem'))
            ->assertOk()
            ->assertSee('Pengelolaan Sesi Tamu')
            ->assertSee('Menit tanpa transaksi');
    }

    public function test_kasir_menampilkan_meja_terisi_walau_pesanan_dari_hari_sebelumnya(): void
    {
        $meja = $this->meja('LA-77');

        $pesanan = Pesanan::create([
            'kode_pesanan' => 'AWI-KASIR-LINTAS-HARI',
            'meja_id' => $meja->id,
            'total_bayar' => 18000,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
            'waktu_pesan' => now()->subDay(),
        ]);

        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee($pesanan->kode_pesanan)
            ->assertSee('Terisi')
            ->assertSee('Kosong');
    }
}
