<?php

namespace App\Http\Controllers\Api;

use App\Events\SessionLogEvent;
use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AppRegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\LogAuth;
use App\Repositories\Customer\CustomerRepo;
use App\Repositories\OtpRepo;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $otpRepository;
    protected $customer_repo;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(OtpRepo $otpRepository, CustomerRepo $customerRepo)
    {
        $this->otpRepository = $otpRepository;
        $this->customer_repo = $customerRepo;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        // $credentials = $request->only('account_type', 'username', 'password');
        $credentials = $request->only('username', 'password');
        //
        // $session_id = strtoupper(uniqid(request('account_type').'-'));
        $session_id = strtoupper(uniqid('system-'));

        try {
            // Attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                // Lưu log qua event
                event(new SessionLogEvent([
                    // 'account_type' => request('account_type'),
                    'session_id' => $session_id,
                    'user_id' => null,
                    'action_type' => Constants::LOG_TYPE_LOGIN,
                    'account_input' => request('username'),
                    'ip_address' => $request->getClientIp(),
                    'error_code' => 401,
                    'result' => Constants::LOGIN_STATUS_FAILED,
                ]));
                return response()->json([
                    'code' => 401,
                    'error' => 'Đăng nhập không thành công',
                    'data' => null
                ]);
            }
        } catch (JWTException $e) {
            // Lưu log qua event
            event(new SessionLogEvent([
                // 'account_type' => request('account_type'),
                'session_id' => $session_id,
                'user_id' => null,
                'action_type' => Constants::LOG_TYPE_LOGIN,
                'account_input' => request('username'),
                'ip_address' => $request->getClientIp(),
                'error_code' => 500,
                'result' => Constants::LOGIN_STATUS_FAILED,
            ]));

            // Something went wrong whilst attempting to encode the token
            return response()->json([
                'code' => 500,
                'error' => 'Đã có lỗi xảy ra, Bạn vui lòng thử lại sau',
                'data' => null
            ]);
        }

        $user = Auth::user();
        if ($user->status == Constants::USER_STATUS_ACTIVE) {
            // Cập nhật log login thành công trong bảng users
            $user->last_login = Carbon::now();
            $user->save();

            // Cập nhật device_token nếu có

            $data = [
                'id' => $user->id,
                'account_type' => $user->account_type,
                'username' => $user->username,
                'fullname' => $user->fullname,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'phone' => $user->phone,
                'birthday' => $user->birthday,
                'display_name' => $user->display_name,
                'address' => $user->address,
                'session_id' => $session_id,
            ];

            // Data chung
            $data['token'] = $token;

            // Lưu log qua event
            event(new SessionLogEvent([
                'account_type' => request('account_type'),
                'session_id' => $session_id,
                'user_id' => null,
                'action_type' => Constants::LOG_TYPE_LOGIN,
                'account_input' => request('username'),
                'ip_address' => $request->getClientIp(),
                'error_code' => 200,
                'result' => Constants::LOGIN_STATUS_SUCCESS,
            ]));

            return response()->json([
                'code' => 200,
                'error' => 'Đăng nhập thành công',
                'data' => $data
            ]);

        } else {
            switch ($user->status){
                case Constants::USER_STATUS_NEW:
                    // Tạo mới, chưa kích hoạt
                    $message = 'Tài khoản chưa được kích hoạt, vui lòng liên hệ SSC';
                    break;
                case Constants::USER_STATUS_ACTIVE:
                    // Đã kích hoạt
                    $message = 'Tài khoản đang hoạt động';
                    break;
                case Constants::USER_STATUS_LOCKED:
                    // Tạm khóa có thể do nghỉ dài ngày
                    $message = 'Tài khoản đang tạm khóa, vui lòng liên hệ SSC';
                    break;
                case Constants::USER_STATUS_DELETED:
                    // Đã chuyển trường
                    $message = 'Tài khoản đã bị khóa vĩnh viễn';
                    break;
            }

            // Lưu log qua event
            event(new SessionLogEvent([
                'account_type' => request('account_type'),
                'session_id' => $session_id,
                'user_id' => null,
                'action_type' => Constants::LOG_TYPE_LOGIN,
                'account_input' => request('username'),
                'ip_address' => $request->getClientIp(),
                'error_code' => 400,
                'result' => Constants::LOGIN_STATUS_FAILED,
            ]));

            return response()->json([
                'code' => 400,
                'error' => $message,
                'data' => null
            ]);
        }
    }

    /**
     * API đăng ký tài khoản Khách hàng từ App và CMS
     * URL: {{url}}/api/v1/auth/login
     *
     * @param CusUpdateInfoSscidRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appRegister(AppRegisterRequest $request)
    {
        $params['fullname'] = request('fullname', null);
        $params['phone'] = request('phone', null);
        $params['email'] = request('email', null);
        $params['username'] = request('username', null);
        $params['password'] = request('password', null);
        $params['password_confirmation'] = request('password_confirmation', null);
        $params['platform'] = request('platform', null);

        $resutl = $this->customer_repo->store($params);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Thêm mới khách hàng thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Thêm mới khách hàng không thành công',
            'data' => null
        ]);
    }

    /**
     * API reset lại mật khẩu cho người dùng
     *
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest  $request)
    {
        $params['account_type'] = request('account_type', null);
        $params['receiver_by'] = request('receiver_by', null);

        $result = $this->customer_repo->resetPassword($params);

        if ($result) {
            return response()->json([
                'code' => 200,
                'error' => 'Mật khẩu đã được gửi về email. Bạn vui lòng, kiểm tra email để lấy thông tin',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng, thử lại sau',
            'data' => null
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(LogoutRequest $request)
    {
        // Lấy mã phiên làm việc qua header hoặc truyền tham số lên nếu có
        if ($request->has('session_id')) {
            $session_id = request('session_id', null);
        } else {
            $session_id = $request->header('session_id');
        }

        // Cập nhật thời gian logout nếu có
        if ($session_id) {
            LogAuth::where('session_id', $session_id)->update([
                'logged_out_at' => Carbon::now()
            ]);
        }

        JWTAuth::invalidate();  // add it to the blacklist
        return response()->json([
            'code' => 200,
            'error' => 'Đăng xuất thành công',
            'data' => null
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'code' => 200,
            'error' => 'Đăng nhập thành công.',
            'data' => [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'token' => $token,
            ]
        ]);
    }
}
