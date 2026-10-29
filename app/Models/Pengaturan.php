<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $guarded = ['id'];

    public const DEFAULTS = [
        'nama_resto' => 'Saung Situ Awi',
        'alamat_resto' => 'Jl. Raya Situ Awi Km. 4, Ciwidey, Kabupaten Bandung',
        'telepon_resto' => '0812-3456-7890',
        'email_resto' => 'halo@situawi.id',
        'struk_footer' => 'Hatur Nuhun Parantos Sumping',
        'qris_expiry_minutes' => 5,
        'buzzer_duration' => 3,
        'anti_spam_cooldown' => 5,
        'lcd_message' => 'Panggil Pelayan',
        'encryption_level' => '4 Digit',
        'session_auto_close' => '0',
        'session_expiry_minutes' => 120,
        'printer_ip' => '',
        'printer_port' => 9100,
        'paper_size' => '80mm',
        'auto_cutter' => '1',
        'print_logo' => '1',
        'logo_path' => '',
        'print_qr' => '1',
        'receipt_header' => 'Saung Situ Awi - Ciwidey',
        'service_charge_enabled' => '0',
        'service_charge_rate' => 5,
        'rounding_rule' => 'none',
        'backup_frequency' => 'harian',
        'backup_retention' => 7,
        'backup_cloud' => '0',
        'midtrans_auto_cancel' => '0',
    ];

    public static function nilai(string $kunci, mixed $default = null): mixed
    {
        $nilai = static::query()->where('kunci', $kunci)->value('nilai');

        if ($nilai !== null) {
            return $nilai;
        }

        return $default ?? self::DEFAULTS[$kunci] ?? null;
    }

    public static function simpanBanyak(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data as $kunci => $nilai) {
                static::query()->updateOrCreate(
                    ['kunci' => $kunci],
                    ['nilai' => is_null($nilai) ? null : (string) $nilai]
                );
            }
        });
    }

    public static function logoUrl(): string
    {
        $path = trim((string) static::nilai('logo_path', ''));

        return $path !== '' ? asset($path) : asset('asset/Logo.png');
    }
}
