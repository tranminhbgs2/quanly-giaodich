<?php

namespace App\Http\Requests\Device;

use App\Helpers\Constants;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDeviceTokenRequest extends FormRequest
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
            'device_token' => [
                'required',
                'min:100'
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
            'device_token.required' => 'Truyền thiếu tham số device_token',
            'device_token.min' => 'Tham số device_token tối thiểu :min ký tự',
            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM
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
