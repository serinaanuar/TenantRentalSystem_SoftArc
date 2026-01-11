<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param string $status
     * @param array $data
     */
    public function __construct(string $status, array $data)
    {
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Private channel for the user who created the request
            new PrivateChannel('maintenance.user.' . $this->data['user_id']),
            
            // Private channel for maintenance requests by property
            new PrivateChannel('maintenance.property.' . $this->data['property_id']),
            
            // General maintenance channel for real-time updates
            new Channel('maintenance.updates'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'maintenance.status.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'status' => $this->status,
            'request_id' => $this->data['request_id'],
            'user_id' => $this->data['user_id'],
            'property_id' => $this->data['property_id'],
            'title' => $this->data['title'],
            'description' => $this->data['description'],
            'priority' => $this->data['priority'] ?? 'MEDIUM',
            'updated_at' => $this->data['updated_at'],
        ];
    }
}
