namespace App\Events;

use App\Models\TiketAntrian;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TiketDipanggil implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $tiket;

    public function __construct(TiketAntrian $tiket)
    {
        // Load relasi yang dibutuhkan oleh frontend
        $this->tiket = $tiket->load(['pelanggan', 'layanan', 'cs']);
    }

    public function broadcastOn()
    {
        return new Channel('antrean-channel');
    }

    public function broadcastAs()
    {
        return 'tiket.dipanggil';
    }
}