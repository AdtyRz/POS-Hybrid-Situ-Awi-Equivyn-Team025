<?php

namespace App\Services;

use App\Models\MejaMakan;
use App\Models\Pesanan;
use Illuminate\Support\Str;

class SessionService
{
    public function ambilAtauBuatSession(MejaMakan $meja): string
    {
        if ($meja->punyaSessionAktif() && $meja->session_code) {
            return $meja->session_code;
        }

        $kode = $this->buatKodeSession($meja);

        $meja->mulaiSession($kode);

        return $kode;
    }

    public function buatKodeSession(MejaMakan $meja): string
    {
        do {
            $kode = strtoupper(Str::random(6));
        } while (MejaMakan::where('session_code', $kode)->exists());

        return $kode;
    }

    public function tutupSessionMeja(MejaMakan $meja, ?int $userId = null): void
    {
        if ($meja->session_code) {
            Pesanan::where('session_code', $meja->session_code)
                ->whereNull('session_code');
        }

        $meja->tutupSession($userId);
    }

    public function akhiriSession(string $sessionCode, ?int $userId = null): void
    {
        $meja = MejaMakan::where('session_code', $sessionCode)->first();

        if ($meja) {
            $meja->tutupSession($userId);
        }
    }
}
