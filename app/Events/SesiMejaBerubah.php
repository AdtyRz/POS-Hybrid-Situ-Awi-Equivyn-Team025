<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SesiMejaBerubah implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;






    public function __construct(
        public string $kodeMeja,
        public int $mejaId,
        public string $status = 'selesai',
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('meja.' . $this->kodeMeja),
            new PrivateChannel('pelayan'),
            new PrivateChannel('kasir'),
            new PrivateChannel('admin'),
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
