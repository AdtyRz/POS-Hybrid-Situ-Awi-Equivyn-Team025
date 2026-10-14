<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesananBaru implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $pesanan) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('kds.dapur'),
            new Channel('kds.bar'),
            new Channel('kasir'),
            new Channel('pelayan'),
            new Channel('admin'),
            new Channel('pesanan.' . ($this->pesanan['kode_pesanan'] ?? '')),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pesanan.baru';
    }

    public function broadcastWith(): array
    {
        return ['pesanan' => $this->pesanan];
    }
}
