<?php

namespace App\Http\Requests\Auth\V2;

use App\Helpers\Constants;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerLoginRequest extends FormRequest
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
            'email' => [
                'required'
            ],
            'password' => [
                'required'
            ],
            'account_type' => [
                'required',
                'in:' . Constants::ACCOUNT_TYPE_CUSTOMER
            ],
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ],
            'device_token' => []
        ];
    }

    public function attributes()
    {
        return [];
    }

    public function messages()
    {
        return [
            'email.required' => 'Truyền thiếu tham số email',
            'password.required' => 'Truyền thiếu tham số password',

            'account_type.required' => 'Truyền thiếu tham số account_type',
            'account_type.in' => 'Account_type là một trong các giá trị ' . Constants::ACCOUNT_TYPE_CUSTOMER,

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM,

            'device_token.min' => 'Tham số device_token tối thiểu :min ký tự'
        ];
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
