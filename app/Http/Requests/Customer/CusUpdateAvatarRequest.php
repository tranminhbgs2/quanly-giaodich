<?php

namespace App\Http\Requests\Customer;

use App\Helpers\Constants;
use App\Models\User;
use App\Rules\CurrentDateLimitRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class CusUpdateAvatarRequest extends FormRequest
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
        $rules = [
            'avatar' => ['required', 'image'],
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ]
        ];

        // Nếu nhập ngày cấp thì sẽ kiểm tra định dạng
        if ($this->request->get('issue_date')) {
            $rules['issue_date'] = [
                'date_format:d/m/Y',
                new CurrentDateLimitRule()
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'avatar' => 'Ảnh đại diện',
        ];
    }

    public function messages()
    {
        return [
            'avatar.required' => 'Truyền thiếu tham số avatar',
            'avatar.image' => 'Ảnh đại diện không đúng định dạng (.jpg, .png)',

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM,
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //
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
