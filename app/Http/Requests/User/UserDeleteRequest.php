<?php

namespace App\Http\Requests\User;

use App\Helpers\Constants;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserDeleteRequest extends FormRequest
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
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
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
            // Check sự tồn tại
            if ($this->request->get('id') > 0) {
                $user = User::where('id', $this->request->get('id'))->withTrashed()->first();
                if ($user && $user->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Thông tin nhân viên đang bị khóa vĩnh viễn');
                } else {
                    $validator->errors()->add('check_exist', 'Không tìm thấy thông tin nhân viên 2');
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
