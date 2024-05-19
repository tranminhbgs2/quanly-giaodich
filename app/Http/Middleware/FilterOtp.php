<?php

namespace App\Http\Middleware;

use App\Helpers\Constants;
use App\Helpers\NewRSA;
use Closure;

class FilterOtp
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
        // Lấy toàn bộ request
        $input = $request->all();

        // Tạo RAS
        $rsa = new NewRSA();
        $rsa->setPrivateKey(storage_path(Constants::API_PRIVATE_KEY_FILE));

        // Check xem có phải tham số OTP không để còn giải mã
        if (isset($input['otp'])) {
            $decrypt_otp = $rsa->decrypt($input['otp']);
            if ($decrypt_otp) {
                $input['otp'] = $decrypt_otp;
            } else {
                if(strlen($input['otp']) == 4){
                    $input['otp'] = $input['otp'];
                } else{
                    $input['otp'] = 'INVALID_OTP';
                }
            }
        }

        // Thay thế giá trị mới trong request
        $request->replace($input);

        return $next($request);
    }
}
