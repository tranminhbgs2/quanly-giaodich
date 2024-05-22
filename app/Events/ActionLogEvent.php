<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActionLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $actor_id;
    public $username;
    public $action;
    public $data_new;
    public $data_old;
    public $description;
    public $model;
    public $table;
    public $record_id;
    public $ip_address;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->actor_id = isset($data['actor_id']) ? $data['actor_id'] : null;
        $this->username = isset($data['username']) ? $data['username'] : null;
        $this->action = isset($data['action']) ? $data['action'] : null;
        $this->data_new = isset($data['data_new']) ? $data['data_new'] : null;
        $this->data_old = isset($data['data_old']) ? $data['data_old'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->model = isset($data['model']) ? $data['model'] : null;
        $this->table = isset($data['table']) ? $data['table'] : null;
        $this->record_id = isset($data['record_id']) ? $data['record_id'] : null;
        $this->ip_address = isset($data['ip_address']) ? $data['ip_address'] : null;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('action-log-event');
    }
}
