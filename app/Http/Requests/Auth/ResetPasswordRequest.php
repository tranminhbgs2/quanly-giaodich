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
            'account_type' => [
                'required',
                'in:' . Constants::ACCOUNT_TYPE
            ],
            'receiver_by' => [
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
            'account_type.required' => 'Truyền thiếu tham số account_type',
            'account_type.in' => 'Account_type là một trong các giá trị ' . Constants::ACCOUNT_TYPE,

            'receiver_by.required' => 'Truyền thiếu tham số receiver_by',

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
            // Check xem nhập email hay sdt
            $receiver_by = $this->request->get('receiver_by');
            $account_type = $this->request->get('account_type');

            // Nếu nhập toàn số
            if (preg_match('/^[0-9]{1,}$/', $receiver_by)) {
                // Tạm thời chỉ reset mật khẩu qua email
                $validator->errors()->add('check_invalid', 'Hiện tại, hệ thống chỉ thiết lập lại mật khẩu qua Email. Bạn vui lòng, nhập email đã đăng ký');

                if (strlen($receiver_by) > 11) {
                    $validator->errors()->add('check_invalid', 'Số điện thoại không hợp lệ.');
                } else {
                    if (! validateMobile($receiver_by)) {
                        $validator->errors()->add('check_invalid', 'Số điện thoại không hợp lệ. Bạn vui lòng, kiểm tra lại');
                    }
                }
            } else {
                if (! filter_var($receiver_by, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('check_invalid', 'Email không hợp lệ. Bạn vui lòng, kiểm tra lại');
                }
            }

            // Chech sự tồn tài khoản
            $customer = User::where([
                'account_type' => $account_type,
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
                'message' => $validator->errors()->first(),
                'data' => null
            ])
        );
    }
}
