<?php

namespace App\Http\Middleware;

use App\Events\ApiLogEvent;
use Carbon\Carbon;
use Closure;

class FilterParams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Lấy params đẩy lên
        $request_data = $request->all();
        // Loại bỏ thông tin pass
        if (isset($request_data['password'])) {
            unset($request_data['password']);
        }
        // Lưu log
        event(new ApiLogEvent([
            'request_at' => Carbon::now(),
            'device_id' => null,
            'client_id' => null,
            'client_ip' => $request->getClientIp(),
            'uri' => $request->getUri(),
            'request_data' => json_encode($request_data),
            'response_data' => null,
        ]));

        return $next($request);
    }
}
