<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request_at;
    public $device_id;
    public $client_id;
    public $client_ip;
    public $uri;
    public $request_data;
    public $response_data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->request_at = isset($data['request_at']) ? $data['request_at'] : null;
        $this->device_id = isset($data['device_id']) ? $data['device_id'] : null;
        $this->client_id = isset($data['client_id']) ? $data['client_id'] : null;
        $this->client_ip = isset($data['client_ip']) ? $data['client_ip'] : null;
        $this->uri = isset($data['uri']) ? $data['uri'] : null;
        $this->request_data = isset($data['request_data']) ? $data['request_data'] : null;
        $this->response_data = isset($data['response_data']) ? $data['response_data'] : null;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('api-log-event');
    }
}
