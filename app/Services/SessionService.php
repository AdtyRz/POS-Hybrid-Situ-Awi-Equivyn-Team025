<?php

namespace App\Services;

use App\Models\MejaMakan;
use Illuminate\Support\Str;

class SessionService
{
    public function mulai(MejaMakan $meja, ?int $endedBy = null): MejaMakan
    {
        if ($meja->status_meja !== 'kosong') {
            throw new \RuntimeException('Meja tidak kosong, tidak bisa memulai session.');
        }

        $sessionCode = $this->generateSessionCode();

        $meja->update([
            'session_code' => $sessionCode,
            'session_started_at' => now(),
            'session_ended_at' => null,
            'session_ended_by' => null,
            'status_meja' => 'terisi',
        ]);

        return $meja->fresh();
    }

    public function ambilAktif(int $mejaId): ?MejaMakan
    {
        return MejaMakan::where('id', $mejaId)
            ->whereNotNull('session_code')
            ->whereNull('session_ended_at')
            ->first();
    }

    public function akhiri(MejaMakan $meja, int $endedBy): MejaMakan
    {
        $meja->update([
            'session_ended_at' => now(),
            'session_ended_by' => $endedBy,
            'status_meja' => 'kosong',
        ]);

        return $meja->fresh();
    }

    private function generateSessionCode(): string
    {
        return 'SES-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }
}
