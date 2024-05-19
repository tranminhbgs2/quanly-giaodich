<?php

namespace App\Http\Middleware;

use App\Helpers\Constants;
use App\Helpers\NewRSA;
use Closure;

class FilterPassword
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

        // Tạo mảng filter các loại mật khẩu
        $filers = [
            'password',
            'password_confirmation',
            'old_password',
            'otp_password'
        ];

        // Tạo RSA
        $rsa = new NewRSA();
        $rsa->setPrivateKey(storage_path(Constants::API_PRIVATE_KEY_FILE));

        // Check xem có phải api login cần giải mã mật khẩu không
        if (is_array($filers) && count($filers) > 0) {
            foreach ($filers as $field) {
                if (isset($input[$field])) {
                    $decrypt = $rsa->decrypt($input[$field]);
                    if ($decrypt) {
                        $input[$field] = $decrypt;
                    } else {
                        return response()->json([
                            'code' => 422,
                            'error' => 'Tham số ' . $field . ' không hợp lệ',
                            'data' => null
                        ]);

                        /*if ($field == 'otp_password'){
                            if (isset($input['type']) && !in_array($input['type'], ['FORGOT_PASSWORD', 'CREATE_OTP_PASSWORD', 'CUSTOMER_REGISTER', 'CUSTOMER_VERIFY'])) {
                                return response()->json([
                                    'code' => 422,
                                    'error' => 'Tham số ' . $field . ' không hợp lệ',
                                    'data' => null
                                ]);
                            }
                        } else {
                            return response()->json([
                                'code' => 422,
                                'error' => 'Tham số ' . $field . ' không hợp lệ',
                                'data' => null
                            ]);
                        }*/
                    }
                }
            }
        }

        // Thay thế giá trị mới trong request
        $request->replace($input);

        return $next($request);
    }
}
