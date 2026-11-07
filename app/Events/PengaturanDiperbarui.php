<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
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
            new PrivateChannel('admin'),
            new PrivateChannel('kasir'),
            new PrivateChannel('pelayan'),
            new PrivateChannel('menu'),
            new PrivateChannel('kds.dapur'),
            new PrivateChannel('kds.bar'),
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
