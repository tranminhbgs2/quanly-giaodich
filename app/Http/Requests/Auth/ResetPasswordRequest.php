<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
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
                'required',
                'email'
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
            'email.required' => 'Truyền thiếu tham số email',
            'email.email' => 'Email không hợp lệ'];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check xem nhập email hay sdt
            $receiver_by = $this->request->get('email');

            // Nếu nhập toàn số

                if (! filter_var($receiver_by, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('check_invalid', 'Email không hợp lệ. Bạn vui lòng, kiểm tra lại');
                }

            // Chech sự tồn tài khoản
            $customer = User::where([
                'email' => $receiver_by
            ])->withTrashed()->first();

            if ($customer) {
                switch ($customer->status) {
                    case Constants::USER_STATUS_NEW: $message = 'Tài khoản chưa được kích hoạt'; break;
                    case Constants::USER_STATUS_LOCKED: $message = 'Tài khoản đang tạm khóa'; break;
                    case Constants::USER_STATUS_DELETED: $message = 'Tài khoản đã bị khóa vĩnh viễn'; break;
                }
                if (isset($message)) {
                    $validator->errors()->add('check_invalid', $message);
                }
            } else {
                $validator->errors()->add('check_invalid', 'Email chưa được đăng ký. Bạn vui lòng, kiểm tra lại');
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
