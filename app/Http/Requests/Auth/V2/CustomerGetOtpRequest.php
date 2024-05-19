<?php

namespace App\Http\Requests\Auth\V2;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;

class CustomerGetOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'otp_by' => [
                'required'
            ],
            'type' => 'required|in:'.Constants::OPT_TYPE,
            'otp_password' => [],
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ]
        ];
    }

    public function attributes()
    {
        return [];
    }

    public function messages()
    {
        return [
            'otp_by.required' => 'Truyền thiếu tham số otp_by',

            'type.required' => 'Truyền thiếu tham số type',
            'type.in' => 'Tham số type là một trong các giá trị: ' . Constants::OPT_TYPE,

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $customer = null;

            // Check phương thức nhận OTP
            $otp_by = $this->request->get('otp_by');
            $type = $this->request->get('type');
            if (preg_match('/^[0-9]{10,11}$/', $otp_by) || !strpos($otp_by, '@')) {
                if (! validateMobile($otp_by)) {
                    $validator->errors()->add('check_exist', 'Số điện thoại không hợp lệ. Bạn vui lòng, kiểm tra lại');
                } else {
                    $validator->errors()->add('check_exist', 'Hệ thống hiện tại chưa hỗ trợ xác thực OTP qua tin nhắn SMS');
                }
            } else {
                if (! filter_var($otp_by, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('check_exist', 'Email không hợp lệ. Bạn vui lòng, kiểm tra lại');
                } else {
                    if (! in_array($type, ['CUSTOMER_REGISTER', 'CUSTOMER_VERIFY'])) {
                        // Check trạng thái của tài khoản
                        $customer = Customer::where('email', $this->request->get('otp_by'))->first();
                        if ($customer) {
                            switch ($customer->status) {
                                case 0: $validator->errors()->add('check_exist', 'Tài khoản chưa kích hoạt. Bạn vui lòng, liên hệ DCVInvest'); break;
                                case 9: $validator->errors()->add('check_exist', 'Tài khoản đang tạm khóa. Bạn vui lòng, liên hệ DCVInvest'); break;
                                case 10: $validator->errors()->add('check_exist', 'Tài khoản đã bị xóa. Bạn vui lòng, liên hệ DCVInvest'); break;
                            }
                        } else {
                            $validator->errors()->add('check_valid', 'Không tìm thấy thông tin Khách hàng');
                        }
                    }
                }
            }

            // Check loại OTP để xác định xem có check mk để lấy OTP hay không
            if (! in_array($type, ['FORGOT_PASSWORD', 'CREATE_OTP_PASSWORD', 'CUSTOMER_REGISTER', 'CUSTOMER_VERIFY'])) {
                if (! $this->request->has('otp_password')) {
                    $validator->errors()->add('check_valid', 'Truyền thiếu mật khẩu lấy mã OTP (otp_password)');
                } else {
                    $otp_password = $this->request->get('otp_password');
                    if ($customer && $customer->otp_password && $otp_password) {
                        if (! Hash::check($otp_password, $customer->otp_password)) {
                            $validator->errors()->add('check_valid', 'Mật khẩu lấy mã OTP (otp_password) không đúng');
                        }
                    } else {
                        $validator->errors()->add('check_valid', 'Không tìm thấy thông tin Khách hàng hoặc Bạn chưa thiết lập mật khẩu lấy mã OTP');
                    }
                }
            }
        });
    }

    /**
     * @param Validator $validator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'code' => 422,
                'error' => $validator->errors()->first(),
                'data' => null
            ])
        );
    }
}
