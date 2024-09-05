<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Clip;

class ClipCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Public attributes are accessible in its event message 
    public $clip;

    /**
     * Create a new event instance.
    */
    public function __construct( Clip $clip ) // Model objects are serialized thanks to SerializesModels
    {
        $this->clip = $clip;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('clips'),
        ];
    }

    /**
     * This will be the event's signature sent out to channel
     */
    public function broadcastAs()
    {
        return 'clip-created';
    }
}
