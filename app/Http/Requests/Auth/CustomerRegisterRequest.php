<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerRegisterRequest extends FormRequest
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
            ],
            /*'phone' => [
                'required'
            ],*/
            'password' => [
                'required',
                'confirmed'
            ],
            'password_confirmation' => [
                'required'
            ],
            'sponsor_id' => [],
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
            'email.required' => 'Truyền thiếu tham số email',
            'email.email' => 'Email không đúng định dạng',

            'phone.required' => 'Truyền thiếu tham số phone',

            'password.required' => 'Truyền thiếu tham số password',
            'password.confirmed' => 'Xác nhận mật khẩu không đúng',

            'password_confirmation.required' => 'Truyền thiếu tham số password_confirmation',

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM,

            'sponsor_id.required' => 'Truyền thiếu tham số sponsor_id'
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check sự tồn tại của người giới thiệu, đại lý
            if ($this->request->has('sponsor_id')) {
                if ($this->request->get('sponsor_id')) {
                    $customer = Customer::where('user_id', $this->request->get('sponsor_id'))->first();
                    if (!$customer) {
                        $validator->errors()->add('check_exist', 'Không tìm thấy thông tin người giới thiệu');
                    }
                } else {
                    $validator->errors()->add('check_exist', 'Truyền thiếu tham số sponsor_id');
                }
            }
            // Check sự tồn tại
            $customer = Customer::where('email', $this->request->get('email'))
                ->orWhere('username', $this->request->get('email'))
                ->first();
            if ($customer) {
                $validator->errors()->add('check_exist', 'Email đã được đăng ký. Bạn vui lòng, chọn email khác');
            }
            //
            /*if ($this->request->has('phone')) {
                $check = validateMobile($this->request->get('phone'));
                if (!$check) {
                    $validator->errors()->add('validate', 'Số điện thoại không đúng định dạng (0912x, 912x, 84912x)');
                }
            }*/
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
