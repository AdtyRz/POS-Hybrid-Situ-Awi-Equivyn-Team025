<?php

namespace App\Services;

use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;

class IotStatusService
{




    public function statusSemuaMeja(): array
    {
        $meja = MejaMakan::select('id', 'kode_meja', 'area', 'kapasitas', 'status_meja')
            ->orderBy('kode_meja')
            ->get();

        $kodeSesi = SesiMeja::where('status', 'aktif')
            ->orderBy('id')
            ->get()
            ->keyBy('meja_id');

        $data = $meja->map(function ($m) use ($kodeSesi) {
            return [
                'id' => $m->id,
                'kode_meja' => $m->kode_meja,
                'area' => $m->area,
                'kapasitas' => $m->kapasitas,
                'status_meja' => $m->status_meja,
                'kode_sesi' => optional($kodeSesi->get($m->id))->kode_unik,
            ];
        });

        return [
            'success' => true,
            'message' => 'Berhasil mengambil status meja',
            'konfigurasi' => $this->konfigurasi(),
            'data' => $data,
        ];
    }




    public function detailMeja(string $ident): ?array
    {
        $meja = MejaMakan::where('id_device', $ident)
            ->orWhere('kode_meja', $ident)
            ->first();

        if (! $meja) {
            return null;
        }

        $sesi = SesiMeja::aktifUntuk($meja);

        $pesanan = Pesanan::query()
            ->where('meja_id', $meja->id)
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->where('waktu_pesan', '>=', $sesi->dibuka_pada)
            ->orderByDesc('waktu_pesan')
            ->get();





        $pesananAktif = $pesanan
            ->reject(fn (Pesanan $p) => $p->status_pesanan === Pesanan::STATUS_SELESAI)
            ->sortBy(fn (Pesanan $p) => match ($p->status_pesanan) {
                Pesanan::STATUS_DIANTAR => 0,
                Pesanan::STATUS_SIAP => 1,
                Pesanan::STATUS_DIPROSES => 2,
                default => 3,
            })
            ->values();

        $pesananSelesai = $pesanan
            ->filter(fn (Pesanan $p) => $p->status_pesanan === Pesanan::STATUS_SELESAI)
            ->values();

        $belumBayar = $pesanan->filter(
            fn (Pesanan $p) => $p->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR
        );





        $fase = 'standby';
        $metodePembayaran = null;

        if ($pesanan->isNotEmpty()) {
            if ($belumBayar->isEmpty()) {
                $fase = 'lunas';
            } else {
                $fase = 'pembayaran';
                $metode = $belumBayar
                    ->pluck('metode_pembayaran')
                    ->filter()
                    ->unique()
                    ->values();

                if ($metode->count() === 1) {
                    $metodePembayaran = $metode->first();
                } elseif ($metode->count() > 1) {
                    $metodePembayaran = 'campuran';
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Berhasil mengambil sesi meja',
            'konfigurasi' => $this->konfigurasi(),
            'data' => [
                'id' => $meja->id,
                'kode_meja' => $meja->kode_meja,
                'area' => $meja->area,
                'status_meja' => $pesanan->isNotEmpty() ? 'terisi' : $meja->status_meja,
                'kode_sesi' => $sesi->kode_unik,
                'fase' => $fase,
                'metode_pembayaran' => $metodePembayaran,
                'ada_pesanan' => $pesanan->isNotEmpty(),
                'semua_lunas' => $pesanan->isNotEmpty() && $belumBayar->isEmpty(),
                'total_bayar' => (float) $pesanan->sum('total_bayar'),
                'jumlah_pesanan' => $pesananAktif->count(),
                'jumlah_selesai' => $pesananSelesai->count(),
                'pesanan' => $pesananAktif->map(fn ($p) => [
                    'kode_pesanan' => $p->kode_pesanan,
                    'status_pesanan' => $p->status_pesanan,
                    'status_pembayaran' => $p->status_pembayaran,
                    'metode_pembayaran' => $p->metode_pembayaran,
                    'total_bayar' => (float) $p->total_bayar,
                ])->values(),
            ],
        ];
    }





    private function konfigurasi(): array
    {
        return [
            'buzzer_duration' => max(1, (int) Pengaturan::nilai('buzzer_duration', 3)),
            'lcd_message' => mb_substr((string) Pengaturan::nilai('lcd_message', 'Panggil Pelayan'), 0, 16),
            'polling_detik' => 30,
        ];
    }
}
