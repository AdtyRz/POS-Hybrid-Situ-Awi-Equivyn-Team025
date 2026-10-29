<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use App\Models\RiwayatShift;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staf(string $role = 'kasir'): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_aktif' => true,
            'password' => bcrypt('password'),
        ]);
    }

    public function test_admin_dapat_menyimpan_jadwal_shift_dan_pembatasan(): void
    {
        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.pengaturan-sistem.simpan'), [
                'nama_resto' => 'Saung Situ Awi',
                'qris_expiry_minutes' => 10,
                'session_expiry_minutes' => 120,
                'shift_restriction_enabled' => true,
                'shift_1_nama' => 'Pagi Cepat',
                'shift_1_mulai' => '06:00',
                'shift_1_selesai' => '11:00',
                'shift_1_aktif' => true,
                'shift_2_nama' => 'Siang',
                'shift_2_mulai' => '11:00',
                'shift_2_selesai' => '17:00',
                'shift_2_aktif' => true,
                'shift_3_nama' => 'Malam',
                'shift_3_mulai' => '17:00',
                'shift_3_selesai' => '23:30',
                'shift_3_aktif' => false,
            ]);

        $response->assertRedirect(route('admin.pengaturan-sistem'));
        $response->assertSessionHasNoErrors();

        $this->assertSame('Pagi Cepat', Pengaturan::nilai('shift_1_nama'));
        $this->assertSame('06:00', Pengaturan::nilai('shift_1_mulai'));
        $this->assertSame('23:30', Pengaturan::nilai('shift_3_selesai'));
        $this->assertSame('1', Pengaturan::nilai('shift_restriction_enabled'));
        $this->assertFalse(filter_var(Pengaturan::nilai('shift_3_aktif'), FILTER_VALIDATE_BOOLEAN));
    }

    public function test_login_dan_logout_mencatat_riwayat_shift(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29 09:15:00', 'Asia/Jakarta'));

        $kasir = $this->staf('kasir');

        $this->post(route('login'), [
            'email' => $kasir->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($kasir);

        $riwayat = RiwayatShift::firstOrFail();

        $this->assertSame($kasir->id, $riwayat->user_id);
        $this->assertSame('kasir', $riwayat->role);
        $this->assertSame('Shift Pagi', $riwayat->nama_shift);
        $this->assertSame('08:00', substr((string) $riwayat->jadwal_mulai, 0, 5));
        $this->assertSame('aktif', $riwayat->status);
        $this->assertNull($riwayat->selesai_pada);

        $this->post(route('logout'))->assertRedirect('/');

        $riwayat->refresh();

        $this->assertSame('selesai', $riwayat->status);
        $this->assertNotNull($riwayat->selesai_pada);
        $this->assertNotNull($riwayat->durasi_detik);
        $this->assertGreaterThanOrEqual(0, $riwayat->durasi_detik);
    }

    public function test_pembatasan_shift_menolak_login_staf_di_luar_jadwal(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29 13:00:00', 'Asia/Jakarta'));

        Pengaturan::simpanBanyak([
            'shift_restriction_enabled' => '1',
            'shift_1_aktif' => '1',
            'shift_1_mulai' => '08:00',
            'shift_1_selesai' => '12:00',
            'shift_2_aktif' => '0',
            'shift_3_aktif' => '0',
        ]);

        $pelayan = $this->staf('pelayan');

        $this->post(route('login'), [
            'email' => $pelayan->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('riwayat_shift', 0);
    }

    public function test_admin_tetap_bisa_login_di_luar_jadwal_shift(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29 13:00:00', 'Asia/Jakarta'));

        Pengaturan::simpanBanyak([
            'shift_restriction_enabled' => '1',
            'shift_1_aktif' => '1',
            'shift_1_mulai' => '08:00',
            'shift_1_selesai' => '12:00',
            'shift_2_aktif' => '0',
            'shift_3_aktif' => '0',
        ]);

        $admin = $this->admin();

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseCount('riwayat_shift', 0);
    }

    public function test_sesi_staf_di_luar_shift_dihentikan_middleware(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29 13:00:00', 'Asia/Jakarta'));

        Pengaturan::simpanBanyak([
            'shift_restriction_enabled' => '1',
            'shift_1_aktif' => '1',
            'shift_1_mulai' => '08:00',
            'shift_1_selesai' => '12:00',
            'shift_2_aktif' => '0',
            'shift_3_aktif' => '0',
        ]);

        $koki = $this->staf('koki');

        $this->actingAs($koki)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sesi_yang_kedaluwarsa_ditutup_otomatis(): void
    {
        $kasir = $this->staf('kasir');
        $mulai = now()->subMinutes(180);
        $terakhir = now()->subMinutes(150);

        $riwayat = RiwayatShift::create([
            'user_id' => $kasir->id,
            'role' => 'kasir',
            'session_id' => 'sesi-lama',
            'nama_shift' => 'Shift Pagi',
            'jadwal_mulai' => '08:00',
            'jadwal_selesai' => '12:00',
            'mulai_pada' => $mulai,
            'terakhir_aktivitas' => $terakhir,
            'status' => 'aktif',
        ]);

        if ((int) config('session.lifetime', 120) <= 150) {
            config()->set('session.lifetime', 60);
        }

        ShiftService::tutupKedaluwarsa();

        $riwayat->refresh();

        $this->assertSame('kedaluwarsa', $riwayat->status);
        $this->assertNotNull($riwayat->selesai_pada);
        $this->assertSame((int) $terakhir->diffInSeconds($riwayat->selesai_pada), 0);
        $this->assertSame(1800, $riwayat->durasi_detik);
    }

    public function test_halaman_riwayat_shift_menampilkan_data_staf_dan_filter(): void
    {
        $admin = $this->admin();
        $kasir = $this->staf('kasir');

        RiwayatShift::create([
            'user_id' => $kasir->id,
            'role' => 'kasir',
            'session_id' => 'sesi-kasir',
            'nama_shift' => 'Shift Pagi',
            'jadwal_mulai' => '08:00',
            'jadwal_selesai' => '12:00',
            'mulai_pada' => now()->subHour(),
            'terakhir_aktivitas' => now()->subMinutes(30),
            'selesai_pada' => now()->subMinutes(30),
            'durasi_detik' => 1800,
            'status' => 'selesai',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.riwayat-shift', ['role' => 'kasir']))
            ->assertOk()
            ->assertSee('Riwayat Shift Staf')
            ->assertSee($kasir->nama)
            ->assertSee('Shift Pagi')
            ->assertSee('30 menit');
    }
}
