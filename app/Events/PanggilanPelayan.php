<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanggilanPelayan implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $panggilan) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('pelayan'),
            new Channel('kasir'),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'panggilan.pelayan';
    }

    public function broadcastWith(): array
    {
        return ['panggilan' => $this->panggilan];
    }
}
