<?php

namespace App\Listeners;

use App\Events\SessionLogEvent;
use App\Helpers\Constants;
use App\Models\LogAuth;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SessionLogListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param SessionLogEvent $event
     */
    public function handle(SessionLogEvent $event)
    {
        LogAuth::create([
            'account_type' => isset($event->account_type) ? $event->account_type : null,
            'session_id' => isset($event->session_id) ? $event->session_id : null,
            'user_id' => isset($event->user_id) ? $event->user_id : null,
            'action_type' => isset($event->action_type) ? $event->action_type : null,
            'logged_in_at' => Carbon::now(),
            'account_input' => isset($event->account_input) ? $event->account_input : null,
            'logged_out_at' => null,
            'user_agent' => null,
            'duration' => 0,
            'ip_address' => isset($event->ip_address) ? $event->ip_address : null,
            'error_code' => isset($event->error_code) ? $event->error_code : null,
            'result' => isset($event->result) ? $event->result : null
        ]);
    }
}
