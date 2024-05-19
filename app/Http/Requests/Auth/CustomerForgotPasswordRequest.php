<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerForgotPasswordRequest extends FormRequest
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
            'otp' => [
                'required'
            ],
            'receiver_name' => [
                'required'
            ],
            'password' => [
                'required',
                'confirmed'
            ],
            'password_confirmation' => [
                'required'
            ],
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
            'otp.required' => 'Truyền thiếu tham số otp',
            'receiver_name.required' => 'Truyền thiếu tham số receiver_name',

            'password.required' => 'Truyền thiếu tham số password',
            'password.confirmed' => 'Xác nhận mật khẩu không đúng',

            'password_confirmation.required' => 'Truyền thiếu tham số password_confirmation',

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
            // Check trạng thái của tài khoản
            $customer = Customer::where('email', $this->request->get('email'))->first();
            if ($customer) {
                switch ($customer->status) {
                    case 0: $validator->errors()->add('check_exist', 'Tài khoản chưa kích hoạt. Bạn vui lòng, liên hệ DCVInvest'); break;
                    case 9: $validator->errors()->add('check_exist', 'Tài khoản đang tạm khóa. Bạn vui lòng, liên hệ DCVInvest'); break;
                    case 10: $validator->errors()->add('check_exist', 'Tài khoản đã bị xóa. Bạn vui lòng, liên hệ DCVInvest'); break;
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
