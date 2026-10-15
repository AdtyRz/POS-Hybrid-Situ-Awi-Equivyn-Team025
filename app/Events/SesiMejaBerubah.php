<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SesiMejaBerubah implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $kodeMeja  Kode meja (mis. LB-01) sebagai nama channel.
     * @param  int     $mejaId    ID meja.
     * @param  string  $status    'dibuka' saat sesi baru, 'selesai' saat ditutup.
     */
    public function __construct(
        public string $kodeMeja,
        public int $mejaId,
        public string $status = 'selesai',
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('meja.' . $this->kodeMeja),
            new Channel('kasir'),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sesi.berubah';
    }

    public function broadcastWith(): array
    {
        return [
            'meja' => $this->kodeMeja,
            'meja_id' => $this->mejaId,
            'status' => $this->status,
        ];
    }
}
