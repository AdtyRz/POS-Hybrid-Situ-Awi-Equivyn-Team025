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
        $kunci = [];

        foreach (self::DEFAULT_SHIFTS as $index => $default) {
            $nomor = $index + 1;

            foreach (['nama', 'mulai', 'selesai', 'aktif'] as $field) {
                $kunci[] = "shift_{$nomor}_{$field}";
            }
        }




        $nilai = Pengaturan::query()
            ->whereIn('kunci', $kunci)
            ->pluck('nilai', 'kunci');

        $daftar = [];

        foreach (self::DEFAULT_SHIFTS as $index => $default) {
            $nomor = $index + 1;
            $nama = trim((string) ($nilai["shift_{$nomor}_nama"] ?? $default['nama']));
            $mulai = substr((string) ($nilai["shift_{$nomor}_mulai"] ?? $default['mulai']), 0, 5);
            $selesai = substr((string) ($nilai["shift_{$nomor}_selesai"] ?? $default['selesai']), 0, 5);
            $aktif = $nilai["shift_{$nomor}_aktif"] ?? ($default['aktif'] ? '1' : '0');

            $daftar[] = [
                'nomor' => $nomor,
                'nama' => $nama !== '' ? $nama : $default['nama'],
                'mulai' => $mulai,
                'selesai' => $selesai,
                'aktif' => filter_var($aktif, FILTER_VALIDATE_BOOLEAN),
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

    public static function toleransiMenit(): int
    {
        return max(0, min(240, (int) Pengaturan::nilai('shift_toleransi_menit', 15)));
    }

    /**
     * Rentang jam operasional yang dipakai seluruh shift aktif.
     */
    public static function jamOperasional(): ?array
    {
        $shiftAktif = array_values(array_filter(
            self::daftarShift(),
            fn (array $shift) => $shift['aktif']
        ));

        if ($shiftAktif === []) {
            return null;
        }

        $mulai = min(array_map(
            fn (array $shift) => self::menitDariJam($shift['mulai']),
            $shiftAktif
        ));
        $selesai = max(array_map(
            fn (array $shift) => self::menitDariJam($shift['selesai']),
            $shiftAktif
        ));

        return [
            'mulai' => self::teksJam($mulai),
            'selesai' => self::teksJam($selesai),
            'lintas_hari' => $selesai <= $mulai,
            'label' => self::teksJam($mulai).' - '.self::teksJam($selesai).' WIB',
            'toleransi_menit' => self::toleransiMenit(),
        ];
    }

    /**
     * Hitung keterlambatan login terhadap jadwal shift.
     *
     * @return array{menit:int,terlambat:bool,dalam_toleransi:bool,label:string,kelas:string}
     */
    public static function keterlambatan(
        ?string $jadwalMulai,
        ?string $jadwalSelesai,
        ?Carbon $mulaiPada,
        ?int $toleransiMenit = null
    ): array {
        $toleransi = max(0, min(240, $toleransiMenit ?? self::toleransiMenit()));

        if (blank($jadwalMulai) || ! $mulaiPada) {
            return [
                'menit' => 0,
                'terlambat' => false,
                'dalam_toleransi' => false,
                'label' => 'Luar Shift',
                'kelas' => 'luar',
            ];
        }

        $zona = 'Asia/Jakarta';
        $login = $mulaiPada->copy()->timezone($zona);
        $jamMulai = self::menitDariJam($jadwalMulai);
        $jamSelesai = $jadwalSelesai ? self::menitDariJam($jadwalSelesai) : null;
        $loginMenit = ((int) $login->format('H')) * 60 + (int) $login->format('i');

        $jadwal = $login->copy()->startOfDay()->addMinutes($jamMulai);

        // Login setelah tengah malam untuk shift yang dimulai sebelum tengah malam.
        if ($jamSelesai !== null && $jamSelesai <= $jamMulai && $loginMenit < $jamSelesai) {
            $jadwal->subDay();
        }

        $selisih = (int) $jadwal->diffInMinutes($login, false);
        $menit = max(0, $selisih);
        $terlambat = $selisih > $toleransi;
        $label = $terlambat ? 'Terlambat '.self::durasiTeks($menit * 60) : 'Tepat waktu';

        return [
            'menit' => $menit,
            'terlambat' => $terlambat,
            'dalam_toleransi' => ! $terlambat && $menit > 0,
            'label' => $label,
            'kelas' => $terlambat ? 'terlambat' : 'tepat',
        ];
    }

    public static function shiftAktif(?Carbon $waktu = null): ?array
    {
        $waktu = ($waktu ?? now('Asia/Jakarta'))->copy()->timezone('Asia/Jakarta');
        $toleransi = self::toleransiMenit();
        $hariIni = $waktu->copy()->startOfDay();

        foreach (self::daftarShift() as $shift) {
            if (! $shift['aktif']) {
                continue;
            }

            $mulaiMenit = self::menitDariJam($shift['mulai']);
            $selesaiMenit = self::menitDariJam($shift['selesai']);
            $lintasHari = $selesaiMenit <= $mulaiMenit;

            // Periksa kemunculan shift kemarin, hari ini, dan besok agar shift
            // lintas tengah malam serta toleransi sebelum jam mulai tetap akurat.
            foreach ([-1, 0, 1] as $geserHari) {
                $mulai = $hariIni->copy()->addDays($geserHari)->addMinutes($mulaiMenit);
                $selesai = $hariIni->copy()->addDays($geserHari)->addMinutes($selesaiMenit);

                if ($lintasHari) {
                    $selesai->addDay();
                }

                $mulaiAkses = $mulai->copy()->subMinutes($toleransi);

                if ($waktu->greaterThanOrEqualTo($mulaiAkses) && $waktu->lessThan($selesai)) {
                    return $shift;
                }
            }
        }

        return null;
    }

    private static function menitDariJam(string $jam): int
    {
        [$jamBagian, $menitBagian] = array_pad(explode(':', substr($jam, 0, 5)), 2, '0');

        return max(0, min(1439, ((int) $jamBagian) * 60 + (int) $menitBagian));
    }

    private static function teksJam(int $menit): string
    {
        $menit = (($menit % 1440) + 1440) % 1440;

        return sprintf('%02d:%02d', intdiv($menit, 60), $menit % 60);
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
        $toleransi = self::toleransiMenit();

        $riwayat = RiwayatShift::create([
            'user_id' => $user->id,
            'role' => $user->role,
            'session_id' => $request->session()->getId(),
            'nama_shift' => $aktif['nama'] ?? 'Luar Shift',
            'jadwal_mulai' => $aktif['mulai'] ?? null,
            'jadwal_selesai' => $aktif['selesai'] ?? null,
            'toleransi_menit' => $aktif ? $toleransi : 0,
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

            $baris = (clone $query)->get(['jadwal_mulai', 'jadwal_selesai', 'mulai_pada', 'toleransi_menit']);
            $terlambat = $baris->filter(
                fn (RiwayatShift $riwayat) => self::keterlambatan(
                    $riwayat->jadwal_mulai,
                    $riwayat->jadwal_selesai,
                    $riwayat->mulai_pada,
                    (int) $riwayat->toleransi_menit
                )['terlambat']
            )->count();

            return [$role => [
                'label' => $label,
                'jumlah' => (clone $query)->count(),
                'aktif' => (clone $query)->whereNull('selesai_pada')->count(),
                'terlambat' => $terlambat,
                'durasi' => (int) (clone $query)->sum('durasi_detik'),
            ]];
        });
    }
}
