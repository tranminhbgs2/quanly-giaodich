<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerVerifyPasswordRequest extends FormRequest
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
            'password' => [
                'required'
            ],
            'account_type' => [
                'required',
                'in:' . Constants::ACCOUNT_TYPE
            ],
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ],
        ];
    }

    public function attributes()
    {
        return [];
    }

    public function messages()
    {
        return [
            'password.required' => 'Truyền thiếu tham số password',

            'account_type.required' => 'Truyền thiếu tham số account_type',
            'account_type.in' => 'Account_type là một trong các giá trị ' . Constants::ACCOUNT_TYPE,

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM,
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check sự tồn tại
            $customer = Customer::where('uid', Auth::user()->admin_id)->first();
            if ($customer) {
                if (! Hash::check($this->request->get('password'), Auth::user()->password)) {
                    $validator->errors()->add('check_invalid', 'Mật khẩu không chính xác');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin Khách hàng');
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
