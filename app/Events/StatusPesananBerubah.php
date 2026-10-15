<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusPesananBerubah implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $pesanan) {}

    public function broadcastOn(): array
    {
        $kode = $this->pesanan['kode_pesanan'] ?? '';

        return [
            new Channel('pesanan.' . $kode),
            new Channel('kds.dapur'),
            new Channel('kds.bar'),
            new Channel('kasir'),
            new Channel('pelayan'),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'status.berubah';
    }

    public function broadcastWith(): array
    {
        return ['pesanan' => $this->pesanan];
    }
}
