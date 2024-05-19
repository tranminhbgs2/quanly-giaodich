<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account_type;
    public $session_id;
    public $user_id;
    public $action_type;
    public $account_input;
    public $ip_address;
    public $error_code;
    public $result;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->account_type = isset($data['account_type']) ? $data['account_type'] : null;
        $this->session_id = isset($data['session_id']) ? $data['session_id'] : null;
        $this->user_id = isset($data['user_id']) ? $data['user_id'] : null;
        $this->action_type = isset($data['action_type']) ? $data['action_type'] : null;
        $this->account_input = isset($data['account_input']) ? $data['account_input'] : null;
        $this->ip_address = isset($data['ip_address']) ? $data['ip_address'] : null;
        $this->error_code = isset($data['error_code']) ? $data['error_code'] : null;
        $this->result = isset($data['result']) ? $data['result'] : null;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('session-log-event');
    }
}
