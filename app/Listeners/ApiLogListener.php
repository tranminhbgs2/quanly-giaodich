<?php

namespace App\Listeners;

use App\Events\ApiLogEvent;
use App\Events\SessionLogEvent;
use App\Helpers\Constants;
use App\Models\LogApi;
use App\Models\LogAuth;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ApiLogListener
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
     * @param ApiLogEvent $event
     */
    public function handle(ApiLogEvent $event)
    {
        if (config('customize.logs.api', false)) {
            LogApi::create([
                'request_at' => isset($event->request_at) ? $event->request_at : null,
                'device_id' => isset($event->device_id) ? $event->device_id : null,
                'client_id' => isset($event->client_id) ? $event->client_id : null,
                'client_ip' => isset($event->client_ip) ? $event->client_ip : null,
                'uri' => isset($event->uri) ? $event->uri : null,
                'request_data' => isset($event->request_data) ? $event->request_data : null,
                'response_data' => isset($event->response_data) ? $event->response_data : null,
            ]);
        }
    }
}
