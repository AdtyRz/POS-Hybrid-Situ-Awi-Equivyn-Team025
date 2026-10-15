<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $jenis = 'menu') {}

    public function broadcastOn(): array
    {
        return [
            new Channel('menu'),
            new Channel('kasir'),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'menu.diperbarui';
    }

    public function broadcastWith(): array
    {
        return ['jenis' => $this->jenis];
    }
}
