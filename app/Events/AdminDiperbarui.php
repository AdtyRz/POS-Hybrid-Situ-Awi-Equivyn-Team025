<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Perubahan data yang hanya tampil di konsol admin (meja, area, akun, dan
 * sebagainya). Halaman admin mendengarkan event ini untuk menyegarkan
 * region [data-live] tanpa reload penuh.
 */
class AdminDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $jenis = 'umum') {}

    public function broadcastOn(): array
    {
        return [new Channel('admin')];
    }

    public function broadcastAs(): string
    {
        return 'admin.diperbarui';
    }

    public function broadcastWith(): array
    {
        return ['jenis' => $this->jenis];
    }
}
