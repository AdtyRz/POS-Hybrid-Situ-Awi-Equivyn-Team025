<?php

namespace Tests\Feature;

use App\Models\MejaMakan;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\RiwayatShift;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\IotStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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
        $this->assertStringStartsWith('logo/', $path);
        Storage::disk()->assertExists($path);
        $this->assertSame(Storage::disk()->url($path), Pengaturan::logoUrl());

        Storage::disk()->delete($path);
    }

    public function test_logo_lama_di_public_dan_logo_baru_di_disk_default_memakai_url_yang_benar(): void
    {
        Pengaturan::simpanBanyak(['logo_path' => 'asset/resto/logo-lama.png']);
        $this->assertSame(asset('asset/resto/logo-lama.png'), Pengaturan::logoUrl());

        Pengaturan::simpanBanyak(['logo_path' => 'logo/logo-baru.png']);
        $this->assertSame(Storage::disk()->url('logo/logo-baru.png'), Pengaturan::logoUrl());

        Pengaturan::simpanBanyak(['logo_path' => '']);
        $this->assertSame(asset('asset/Logo.png'), Pengaturan::logoUrl());
    }

    public function test_unggah_logo_baru_menghapus_logo_lama_di_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('logo/logo-lama.png', 'logo lama');
        Pengaturan::simpanBanyak(['logo_path' => 'logo/logo-lama.png']);

        $this->actingAs($this->admin())->post(route('admin.pengaturan-sistem.logo'), [
            'logo' => UploadedFile::fake()->image('logo-resto.png', 120, 80),
        ])->assertRedirect(route('admin.pengaturan-sistem'));

        Storage::disk('public')->assertMissing('logo/logo-lama.png');
        Storage::disk('public')->assertExists(Pengaturan::nilai('logo_path'));
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
            ->assertSee('Menit tanpa transaksi')
            ->assertSee('5. Shift &amp; Jam Operasional', false)
            ->assertSee('Jadwal Shift Operasional')
            ->assertSee('Batasi Akses Staf ke Jam Shift');
    }

    public function test_admin_dapat_mengubah_batas_waktu_qris(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.pengaturan-sistem.simpan'), [
            'nama_resto' => 'Saung Situ Awi',
            'qris_expiry_minutes' => 17,
            'session_expiry_minutes' => 120,
        ]);

        $response->assertRedirect(route('admin.pengaturan-sistem'));
        $response->assertSessionHas('sukses');
        $this->assertSame('17', Pengaturan::nilai('qris_expiry_minutes'));
    }

    public function test_admin_dapat_menyimpan_panjang_kode_sesi_enam_digit(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.pengaturan-sistem.simpan'), [
            'nama_resto' => 'Saung Situ Awi',
            'qris_expiry_minutes' => 5,
            'session_expiry_minutes' => 120,
            'lcd_message' => Pengaturan::DEFAULTS['lcd_message'],
            'encryption_level' => '6 Digit',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.pengaturan-sistem'));
        $this->assertSame('6 Digit', Pengaturan::nilai('encryption_level'));
        $this->assertLessThanOrEqual(16, mb_strlen((string) Pengaturan::nilai('lcd_message')));
    }

    public function test_master_meja_menampilkan_kode_sesuai_pengaturan_enam_digit(): void
    {
        Pengaturan::simpanBanyak(['encryption_level' => '6 Digit']);

        $meja = $this->meja('LB-99');
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->assertSame(6, strlen($sesi->kode_unik));

        $this->actingAs($this->admin())
            ->get(route('admin.master-meja'))
            ->assertOk()
            ->assertSee('Kode 6 Digit')
            ->assertSee('Kode Masuk (6 Digit):')
            ->assertSee($sesi->kode_unik);
    }

    public function test_default_pesan_lcd_tidak_melebihi_batas_lcd(): void
    {
        $this->assertLessThanOrEqual(16, mb_strlen(Pengaturan::DEFAULTS['lcd_message']));
    }

    public function test_konfigurasi_lcd_dipotong_maksimal_16_karakter(): void
    {
        $pesan = 'Pesan LCD yang sangat panjang';
        Pengaturan::simpanBanyak(['lcd_message' => $pesan]);

        $konfigurasi = app(IotStatusService::class)->statusSemuaMeja()['konfigurasi'];

        $this->assertSame(mb_substr($pesan, 0, 16), $konfigurasi['lcd_message']);
    }

    public function test_admin_dapat_menghapus_akun_staf(): void
    {
        $admin = $this->admin();
        $staf = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($admin)->delete(route('admin.akun.hapus', $staf->id));

        $response->assertRedirect();
        $response->assertSessionHas('sukses');
        $this->assertDatabaseMissing('users', ['id' => $staf->id]);
    }

    public function test_admin_tidak_dapat_menghapus_akun_sendiri(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->from(route('admin.manajemen-akun'))
            ->delete(route('admin.akun.hapus', $admin->id));

        $response->assertRedirect(route('admin.manajemen-akun'));
        $response->assertSessionHasErrors('akun');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_laporan_penjualan_tidak_menampilkan_filter_kluster_saung(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan'))
            ->assertOk()
            ->assertDontSee('KLUSTER SAUNG / MEJA')
            ->assertSee('Filter & Pratinjau Laporan')
            ->assertSee('Unduh PDF');
    }

    public function test_laporan_shift_kerja_menampilkan_tabel_shift_bukan_tabel_transaksi(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $kasir = User::factory()->create(['role' => 'kasir', 'nama' => 'Kasir Shift Uji']);

        RiwayatShift::create([
            'user_id' => $kasir->id,
            'role' => 'kasir',
            'session_id' => 'sesi-laporan-shift',
            'nama_shift' => 'Shift Pagi Laporan',
            'jadwal_mulai' => '08:00',
            'jadwal_selesai' => '12:00',
            'mulai_pada' => now()->subHours(2),
            'terakhir_aktivitas' => now()->subHour(),
            'selesai_pada' => now()->subHour(),
            'durasi_detik' => 3600,
            'status' => 'selesai',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan', ['jenis' => 'shift']))
            ->assertOk()
            ->assertSee('Pratinjau Laporan Shift Kerja')
            ->assertSee('Kasir Shift Uji')
            ->assertSee('Shift Pagi Laporan')
            ->assertSee('TOTAL DURASI KERJA')
            ->assertDontSee('Pratinjau Rekap Transaksi')
            ->assertDontSee('METODE BAYAR');
    }

    public function test_laporan_penjualan_masih_mendukung_jenis_harian_lama(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan', ['jenis' => 'harian']))
            ->assertOk()
            ->assertSee('Pratinjau Rekap Transaksi')
            ->assertSee('Laporan Penjualan &amp; Rekonsiliasi Midtrans vs Tunai', false);
    }

    public function test_state_laporan_penjualan_mengembalikan_transaksi_terfilter(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $meja = $this->meja('LB-91');

        Pesanan::create([
            'kode_pesanan' => 'AWI-STATE-01',
            'meja_id' => $meja->id,
            'total_bayar' => 45000,
            'status_pesanan' => Pesanan::STATUS_SIAP,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'metode_pembayaran' => 'qris',
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.laporan-penjualan.state'))
            ->assertOk()
            ->assertJsonPath('jenis', 'rekonsiliasi')
            ->assertJsonPath('transactions.0.id', '#AWI-STATE-01')
            ->assertJsonPath('transactions.0.method', 'QRIS Midtrans')
            ->assertJsonPath('pagination.total', 1);

        $this->assertSame(45000.0, (float) $response->json('rekap_halaman.total'));
    }

    public function test_state_laporan_shift_mengembalikan_ringkasan_sesi(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $kasir = User::factory()->create(['role' => 'kasir']);

        RiwayatShift::create([
            'user_id' => $kasir->id,
            'role' => 'kasir',
            'nama_shift' => 'Shift Siang',
            'mulai_pada' => now()->subHour(),
            'terakhir_aktivitas' => now(),
            'status' => 'aktif',
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.laporan-penjualan.state', ['jenis' => 'shift']))
            ->assertOk()
            ->assertJsonPath('jenis', 'shift')
            ->assertJsonPath('ringkasan.total', 1)
            ->assertJsonPath('ringkasan.staf', 1)
            ->assertJsonPath('ringkasan.aktif', 1);
    }

    public function test_cetak_laporan_shift_kerja_memakai_view_khusus_shift(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $kasir = User::factory()->create(['role' => 'kasir', 'nama' => 'Kasir Cetak Shift']);

        RiwayatShift::create([
            'user_id' => $kasir->id,
            'role' => 'kasir',
            'nama_shift' => 'Shift Malam Cetak',
            'jadwal_mulai' => '17:00',
            'jadwal_selesai' => '23:00',
            'mulai_pada' => now()->subHour(),
            'terakhir_aktivitas' => now(),
            'selesai_pada' => now(),
            'durasi_detik' => 1800,
            'status' => 'selesai',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan.cetak', ['jenis' => 'shift']))
            ->assertOk()
            ->assertSee('Laporan Shift Kerja')
            ->assertSee('Rincian Sesi Staf')
            ->assertSee('Kasir Cetak Shift')
            ->assertSee('Shift Malam Cetak')
            ->assertDontSee('Rincian Transaksi Shift');
    }

    public function test_ekspor_laporan_shift_kerja_csv_dan_xls(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $kasir = User::factory()->create(['role' => 'kasir', 'nama' => 'Kasir Ekspor Shift']);

        RiwayatShift::create([
            'user_id' => $kasir->id,
            'role' => 'kasir',
            'nama_shift' => 'Shift Ekspor',
            'mulai_pada' => now()->subHour(),
            'terakhir_aktivitas' => now(),
            'selesai_pada' => now(),
            'durasi_detik' => 1800,
            'status' => 'selesai',
        ]);

        $csv = $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan.ekspor', ['format' => 'csv', 'jenis' => 'shift']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Laporan Shift Kerja', $csv);
        $this->assertStringContainsString('Kasir Ekspor Shift', $csv);
        $this->assertStringContainsString('Shift Ekspor', $csv);
        $this->assertStringNotContainsString('Kode Pesanan', $csv);

        $xls = $this->actingAs($this->admin())
            ->get(route('admin.laporan-penjualan.ekspor', ['format' => 'xls', 'jenis' => 'shift']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('LAPORAN SHIFT KERJA', $xls);
        $this->assertStringContainsString('Kasir Ekspor Shift', $xls);
        $this->assertStringContainsString('Shift Ekspor', $xls);
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
