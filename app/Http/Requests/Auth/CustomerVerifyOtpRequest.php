<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerVerifyOtpRequest extends FormRequest
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
            'type' => 'required|in:'.Constants::OPT_TYPE,
            'otp' => [
                'required'
            ],
            'receiver_name' => [
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
            'type.required' => 'Truyền thiếu tham số type',
            'type.in' => 'Tham số type là một trong các giá trị: ' . Constants::OPT_TYPE,

            'otp.required' => 'Truyền thiếu tham số otp',
            'receiver_name.required' => 'Truyền thiếu tham số receiver_name',

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
            $otp = $this->request->get('otp');

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
