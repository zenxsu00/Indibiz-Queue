<?php

namespace App\Events;

use App\Models\TiketAntrian;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TiketDipanggil implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tiketId;
    public string $status;

    public function __construct(TiketAntrian $tiket)
    {
        $this->tiketId = $tiket->id;
        $this->status  = $tiket->status;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('antrean-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'tiket.updated';
    }
}