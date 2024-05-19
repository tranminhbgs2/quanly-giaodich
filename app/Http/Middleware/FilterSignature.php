<?php

namespace App\Http\Middleware;

use App\Helpers\Constants;
use App\Helpers\NewRSA;
use Closure;

class FilterSignature
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
        $signature = $request->header('Signature', null);

        /*if ($signature) {
            // Tạo RAS
            $rsa = new NewRSA();
            $rsa->setPrivateKey(Constants::API_PRIVATE_KEY_FILE);
            $decrypt = $rsa->decrypt($signature);
            if ($decrypt) {
                $input['otp'] = $decrypt;
            } else {
                return response()->json([
                    'code' => 422,
                    'error' => 'Chữ ký không hợp lệ',
                    'data' => null
                ]);
            }
        } else {
            return response()->json([
                'code' => 422,
                'error' => 'Không tìm thấy chữ ký xác thực',
                'data' => null
            ]);
         }*/

        return $next($request);
    }
}
