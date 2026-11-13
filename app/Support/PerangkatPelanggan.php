<?php

namespace App\Support;

use App\Models\SesiMeja;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PerangkatPelanggan
{
    public const KUNCI_SESI = 'perangkat_pelanggan';

    public static function id(Request $request): string
    {
        $id = $request->session()->get(self::KUNCI_SESI);

        if (! is_string($id) || ! preg_match('/^[A-Za-z0-9._-]{8,64}$/', $id)) {
            $id = (string) Str::uuid();
            $request->session()->put(self::KUNCI_SESI, $id);
        }

        return $id;
    }

    public static function label(Request $request): string
    {
        $userAgent = (string) $request->userAgent();

        $platform = 'Perangkat';
        foreach ([
            'Android' => 'Android',
            'iPhone' => 'iPhone',
            'iPad' => 'iPad',
            'Windows' => 'Windows',
            'Macintosh' => 'Mac',
            'Linux' => 'Linux',
        ] as $penanda => $label) {
            if (stripos($userAgent, $penanda) !== false) {
                $platform = $label;
                break;
            }
        }

        $browser = 'Browser';
        foreach ([
            'Edg' => 'Edge',
            'OPR' => 'Opera',
            'Chrome' => 'Chrome',
            'CriOS' => 'Chrome',
            'Firefox' => 'Firefox',
            'FxiOS' => 'Firefox',
            'Safari' => 'Safari',
        ] as $penanda => $label) {
            if (stripos($userAgent, $penanda) !== false) {
                $browser = $label;
                break;
            }
        }

        return mb_substr($browser . ' di ' . $platform, 0, 100);
    }

    public static function cocok(SesiMeja $sesi, string $id): bool
    {
        return filled($sesi->perangkat_id)
            && hash_equals((string) $sesi->perangkat_id, $id);
    }
}
