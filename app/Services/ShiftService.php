<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\RiwayatShift;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShiftService
{
    public const ROLES = [
        'pelayan' => 'Pelayan',
        'koki' => 'Dapur / Koki',
        'barista' => 'Bar / Barista',
        'kasir' => 'Kasir',
    ];

    public const DEFAULT_SHIFTS = [
        ['nama' => 'Shift Pagi', 'mulai' => '08:00', 'selesai' => '12:00', 'aktif' => true],
        ['nama' => 'Shift Siang', 'mulai' => '12:00', 'selesai' => '18:00', 'aktif' => true],
        ['nama' => 'Shift Malam', 'mulai' => '18:00', 'selesai' => '23:59', 'aktif' => true],
    ];

    public static function daftarShift(): array
    {
        $daftar = [];

        foreach (self::DEFAULT_SHIFTS as $index => $default) {
            $nomor = $index + 1;
            $nama = trim((string) Pengaturan::nilai("shift_{$nomor}_nama", $default['nama']));
            $mulai = substr((string) Pengaturan::nilai("shift_{$nomor}_mulai", $default['mulai']), 0, 5);
            $selesai = substr((string) Pengaturan::nilai("shift_{$nomor}_selesai", $default['selesai']), 0, 5);

            $daftar[] = [
                'nomor' => $nomor,
                'nama' => $nama !== '' ? $nama : $default['nama'],
                'mulai' => $mulai,
                'selesai' => $selesai,
                'aktif' => filter_var(
                    Pengaturan::nilai("shift_{$nomor}_aktif", $default['aktif'] ? '1' : '0'),
                    FILTER_VALIDATE_BOOLEAN
                ),
            ];
        }

        return $daftar;
    }

    public static function pembatasanAktif(): bool
    {
        return filter_var(
            Pengaturan::nilai('shift_restriction_enabled', '0'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function shiftAktif(?Carbon $waktu = null): ?array
    {
        $waktu ??= now('Asia/Jakarta');
        $jam = $waktu->format('H:i');

        foreach (self::daftarShift() as $shift) {
            if (! $shift['aktif']) {
                continue;
            }

            $mulai = $shift['mulai'];
            $selesai = $shift['selesai'];

            $cocok = $mulai < $selesai
                ? ($jam >= $mulai && $jam < $selesai)
                : ($jam >= $mulai || $jam < $selesai);

            if ($cocok) {
                return $shift;
            }
        }

        return null;
    }

    public static function ringkasan(?Carbon $waktu = null): array
    {
        $waktu ??= now('Asia/Jakarta');
        $shift = self::shiftAktif($waktu);

        if (! $shift) {
            return [
                'nama' => 'Luar Shift',
                'mulai' => '00:00',
                'selesai' => '00:00',
                'label' => 'Luar Shift',
                'sisa_menit' => 0,
                'sisa_label' => 'Tidak ada shift aktif',
            ];
        }

        $selesai = $waktu->copy()->setTimeFromTimeString($shift['selesai']);

        if ($selesai <= $waktu) {
            $selesai->addDay();
        }

        $sisaMenit = max(0, (int) $waktu->diffInMinutes($selesai, false));
        $jam = intdiv($sisaMenit, 60);
        $menit = $sisaMenit % 60;

        return [
            'nama' => $shift['nama'],
            'mulai' => $shift['mulai'],
            'selesai' => $shift['selesai'],
            'label' => $shift['nama'].' ('.$shift['mulai'].' - '.$shift['selesai'].' WIB)',
            'sisa_menit' => $sisaMenit,
            'sisa_label' => $jam > 0 ? $jam.' jam '.$menit.' menit' : $menit.' menit',
        ];
    }

    public static function bolehAktif(?Authenticatable $user, ?Carbon $waktu = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'admin' || ! array_key_exists($user->role, self::ROLES)) {
            return true;
        }

        if (! self::pembatasanAktif()) {
            return true;
        }

        return self::shiftAktif($waktu) !== null;
    }

    public static function mulai(Request $request, ?Authenticatable $user = null): void
    {
        $user ??= $request->user();

        if (! $user || ! array_key_exists($user->role, self::ROLES)) {
            return;
        }

        if (! $request->hasSession()) {
            return;
        }

        $aktif = self::shiftAktif();

        $riwayat = RiwayatShift::create([
            'user_id' => $user->id,
            'role' => $user->role,
            'session_id' => $request->session()->getId(),
            'nama_shift' => $aktif['nama'] ?? 'Luar Shift',
            'jadwal_mulai' => $aktif['mulai'] ?? null,
            'jadwal_selesai' => $aktif['selesai'] ?? null,
            'mulai_pada' => now(),
            'terakhir_aktivitas' => now(),
            'status' => 'aktif',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
        ]);

        $request->session()->put('riwayat_shift_id', $riwayat->id);
    }

    public static function selesaikan(Request $request, ?Authenticatable $user = null): void
    {
        $user ??= $request->user();

        if (! $user || ! array_key_exists($user->role, self::ROLES)) {
            return;
        }

        $riwayat = null;

        if ($request->hasSession() && $request->session()->has('riwayat_shift_id')) {
            $riwayat = RiwayatShift::query()
                ->where('id', $request->session()->get('riwayat_shift_id'))
                ->where('user_id', $user->id)
                ->whereNull('selesai_pada')
                ->first();
        }

        if (! $riwayat && $request->hasSession()) {
            $riwayat = RiwayatShift::query()
                ->where('user_id', $user->id)
                ->where('session_id', $request->session()->getId())
                ->whereNull('selesai_pada')
                ->latest('mulai_pada')
                ->first();
        }

        if (! $riwayat) {
            return;
        }

        $selesai = now();
        $durasi = max(0, (int) $riwayat->mulai_pada->diffInSeconds($selesai, false));

        $riwayat->update([
            'selesai_pada' => $selesai,
            'terakhir_aktivitas' => $selesai,
            'durasi_detik' => $durasi,
            'status' => 'selesai',
        ]);

        if ($request->hasSession()) {
            $request->session()->forget('riwayat_shift_id');
        }
    }

    public static function catatAktivitas(Request $request, ?Authenticatable $user = null): void
    {
        $user ??= $request->user();

        if (! $user || ! array_key_exists($user->role, self::ROLES) || ! $request->hasSession()) {
            return;
        }

        $id = $request->session()->get('riwayat_shift_id');

        if (! $id) {
            return;
        }

        $kunci = 'riwayat_shift_aktivitas_'.$id;
        $terakhir = (int) $request->session()->get($kunci, 0);

        if ($terakhir > 0 && now()->diffInSeconds(Carbon::createFromTimestamp($terakhir)) < 60) {
            return;
        }

        $riwayat = RiwayatShift::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('selesai_pada')
            ->first();

        if (! $riwayat) {
            return;
        }

        $riwayat->update(['terakhir_aktivitas' => now()]);
        $request->session()->put($kunci, now()->timestamp);
    }

    public static function tutupKedaluwarsa(): void
    {
        $batas = now()->subMinutes(max(1, (int) config('session.lifetime', 120)));

        RiwayatShift::query()
            ->whereNull('selesai_pada')
            ->where('terakhir_aktivitas', '<', $batas)
            ->get()
            ->each(function (RiwayatShift $riwayat) {
                $selesai = $riwayat->terakhir_aktivitas ?? $riwayat->mulai_pada;
                $durasi = max(0, (int) $riwayat->mulai_pada->diffInSeconds($selesai, false));

                $riwayat->update([
                    'selesai_pada' => $selesai,
                    'durasi_detik' => $durasi,
                    'status' => 'kedaluwarsa',
                ]);
            });
    }

    public static function durasiTeks(?int $detik): string
    {
        $detik = max(0, (int) $detik);
        $jam = intdiv($detik, 3600);
        $menit = intdiv($detik % 3600, 60);

        if ($jam > 0) {
            return $jam.' jam '.$menit.' menit';
        }

        return $menit.' menit';
    }

    public static function ringkasanPerRole(): Collection
    {
        $hariIni = now('Asia/Jakarta');
        $mulaiHari = $hariIni->copy()->startOfDay()->utc();
        $selesaiHari = $hariIni->copy()->endOfDay()->utc();

        return collect(self::ROLES)->mapWithKeys(function ($label, $role) use ($mulaiHari, $selesaiHari) {
            $query = RiwayatShift::query()
                ->where('role', $role)
                ->whereBetween('mulai_pada', [$mulaiHari, $selesaiHari]);

            return [$role => [
                'label' => $label,
                'jumlah' => (clone $query)->count(),
                'aktif' => (clone $query)->whereNull('selesai_pada')->count(),
                'durasi' => (int) (clone $query)->sum('durasi_detik'),
            ]];
        });
    }
}
