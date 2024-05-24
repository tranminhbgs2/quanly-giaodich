<?php

namespace App\Http\Requests\Customer;

use App\Helpers\Constants;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
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
            'old_password' => [
                'required',
            ],
            'password' => [
                'required',
                'confirmed'
            ],
            'password_confirmation' => [
                'required'
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
            'old_password.required' => 'Truyền thiếu tham số old_password',

            'password.required' => 'Truyền thiếu tham số password',
            'password.max' => 'Mật khẩu dài tối đa :max ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không đúng',
            'password_confirmation.required' => 'Truyền thiếu tham số password_confirmation',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check sự tồn tại
            $user = User::where('id', Auth::id())->withTrashed()->first();
            $old_password = $this->request->get('old_password');
            if ($user && $user->password && $old_password) {
                if (! Hash::check($old_password, $user->password)) {
                    $validator->errors()->add('check_valid', 'Mật khẩu cũ không đúng');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin');
            }

            // Check mk cũ phải khác mk mới
            $password = $this->request->get('password');
            if ($old_password == $password) {
                $validator->errors()->add('check_valid', 'Mật khẩu mới phải khác mật khẩu cũ');
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
