<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
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
            new PrivateChannel('pelayan'),
            new PrivateChannel('kasir'),
            new PrivateChannel('admin'),
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
