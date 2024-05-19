<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            // Check phương thức nhận OTP
            $otp_by = $this->request->get('otp_by');
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
                    // Check trạng thái của tài khoản
                    $customer = Customer::where('email', $this->request->get('otp_by'))->first();
                    if ($customer) {
                        switch ($customer->status) {
                            case 0: $validator->errors()->add('check_exist', 'Tài khoản chưa kích hoạt. Bạn vui lòng, liên hệ DCVInvest'); break;
                            case 9: $validator->errors()->add('check_exist', 'Tài khoản đang tạm khóa. Bạn vui lòng, liên hệ DCVInvest'); break;
                            case 10: $validator->errors()->add('check_exist', 'Tài khoản đã bị xóa. Bạn vui lòng, liên hệ DCVInvest'); break;
                        }
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
