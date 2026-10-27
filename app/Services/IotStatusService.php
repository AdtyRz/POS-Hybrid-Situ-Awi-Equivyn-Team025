<?php

namespace App\Services;

use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;

class IotStatusService
{
    /**
     * Status semua meja untuk LCD/ESP32. Endpoint pemanggilnya wajib memakai
     * token IoT sehingga kode sesi tidak pernah terbuka untuk publik.
     */
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
            'konfigurasi' => [
                'buzzer_duration' => max(1, (int) Pengaturan::nilai('buzzer_duration', 3)),
                'lcd_message' => (string) Pengaturan::nilai('lcd_message', 'Memanggil Pelayan...'),
                'polling_detik' => 30,
            ],
            'data' => $data,
        ];
    }

    /**
     * Status satu meja berdasarkan id_device atau kode_meja.
     */
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

        return [
            'success' => true,
            'message' => 'Berhasil mengambil sesi meja',
            'data' => [
                'id' => $meja->id,
                'kode_meja' => $meja->kode_meja,
                'area' => $meja->area,
                'status_meja' => $meja->status_meja,
                'kode_sesi' => $sesi->kode_unik,
                'total_bayar' => (float) $pesanan->sum('total_bayar'),
                'jumlah_pesanan' => $pesanan->count(),
                'pesanan' => $pesanan->map(fn ($p) => [
                    'kode_pesanan' => $p->kode_pesanan,
                    'status_pesanan' => $p->status_pesanan,
                    'status_pembayaran' => $p->status_pembayaran,
                    'total_bayar' => (float) $p->total_bayar,
                ])->values(),
            ],
        ];
    }
}
