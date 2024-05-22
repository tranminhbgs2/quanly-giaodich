<?php

namespace App\Listeners;

use App\Events\ActionLogEvent;
use App\Models\LogAction;
use App\Models\LogAuth;
use Carbon\Carbon;

class ActionLogListener
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
     * @param ActionLogEvent $event
     */
    public function handle(ActionLogEvent $event)
    {
        LogAction::create([
            'actor_id' => isset($event->actor_id) ? $event->actor_id : null,
            'username' => isset($event->username) ? $event->username : null,
            'action' => isset($event->action) ? $event->action : null,
            'data_new' => isset($event->data_new) ? $event->data_new : null,
            'data_old' => isset($event->data_old) ? $event->data_old : null,
            'description' => isset($event->description) ? $event->description : null,
            'model' => isset($event->model) ? $event->model : null,
            'table' => isset($event->table) ? $event->table : null,
            'record_id' => isset($event->record_id) ? $event->record_id : null,
            'ip_address' => isset($event->ip_address) ? $event->ip_address : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
