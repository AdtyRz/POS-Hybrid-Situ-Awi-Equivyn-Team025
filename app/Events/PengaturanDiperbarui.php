<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PengaturanDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $kunci = []) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin'),
            new Channel('kasir'),
            new Channel('pelayan'),
            new Channel('menu'),
            new Channel('kds.dapur'),
            new Channel('kds.bar'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pengaturan.diperbarui';
    }

    public function broadcastWith(): array
    {
        return ['kunci' => $this->kunci];
    }
}
