<?php

namespace App\Events;

use App\Models\TiketAntrian;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TiketDipanggil implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public int $tiketId;
    public string $status;

    public function __construct(TiketAntrian $tiket)
    {
        $this->tiketId = $tiket->id;
        $this->status  = $tiket->status;
    }

    public function broadcastOn()
    {
        return new Channel('antrean-channel');
    }

    public function broadcastAs()
    {
        return 'tiket.updated';
    }
}