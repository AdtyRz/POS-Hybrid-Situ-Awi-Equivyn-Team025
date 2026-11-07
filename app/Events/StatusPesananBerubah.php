<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
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
        $kodeMeja = $this->pesanan['meja'] ?? '';

        $channels = [
            new PrivateChannel('pesanan.' . $kode),
            new PrivateChannel('kds.dapur'),
            new PrivateChannel('kds.bar'),
            new PrivateChannel('kasir'),
            new PrivateChannel('pelayan'),
            new PrivateChannel('admin'),
        ];

        if ($kodeMeja !== '') {
            $channels[] = new PrivateChannel('meja.' . $kodeMeja);
        }

        return $channels;
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
