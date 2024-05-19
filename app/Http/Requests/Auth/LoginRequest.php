<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'username' => [
                'required'
            ],
            'password' => [
                'required'
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
            'username.required' => 'Truyền thiếu tham số username',
            'password.required' => 'Truyền thiếu tham số password',
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
                'codes' => 422,
                'error' => $validator->errors()->first(),
                'data' => null
            ])
        );
    }
}
