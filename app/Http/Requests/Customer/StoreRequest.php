<?php

namespace App\Http\Requests\Customer;

use App\Helpers\Constants;
use App\Models\Student;
use App\Models\User;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest
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
        $rule = [
            'fullname' => ['required'],
            'phone' => ['required'],
            'username' => [
                'required',
                new UsernameRule()
            ],
            'password' => [
                'required',
            ],
            'birthday' => [
                'date_format:d/m/Y'
            ],
            'action_ids' => 'required|array',
            'action_ids.*' => 'exists:positions,id',
        ];

        // Nếu nhập email thì check
        if ($this->request->get('email')) {
            $rules['email'] = ['email'];
        }

        // Nếu nhập email thì check
        if ($this->request->get('avatar')) {
            $rules['avatar'] = ['image'];
        }

        return $rule;
    }

    public function attributes()
    {
        return [
            'username' => 'Tên tài khoản',
            'password' => 'Mật khẩu',
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Truyền thiếu tham số fullname',
            'phone.required' => 'Truyền thiếu tham số phone',
            'email.email' => 'Email không đúng định dạng',

            'username.required' => 'Truyền thiếu tham số username',

            'password.required' => 'Truyền thiếu tham số password',
            'action_ids.required' => 'Truyền thiếu tham số action_ids',
            'action_ids.array' => 'Tham số action_ids phải là mảng',
            'birthday.date_format' => 'Ngày sinh không đúng định dạng d/m/Y',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check username
            $user = User::where('username', $this->request->get('username'))->withTrashed()->first();

            if ($user) {
                $validator->errors()->add('check_exist', 'Tên tài khoản đã được đăng ký. Bạn vui lòng, chọn tên khác');
            }

            // Check theo phone
            $user = User::where('phone', formatMobile($this->request->get('phone')))->whereNotNull('phone')->withTrashed()->first();
            if ($user) {
                $validator->errors()->add('check_exist', 'Số điện thoại đã được đăng ký. Bạn vui lòng, chọn số điện thoại khác');
            } else {
                if (!validateMobile($this->request->get('phone'))) {
                    $validator->errors()->add('check_exist', 'Số điện thoại không đúng định dạng (09x/9x/849x)');
                }
            }

            // Check theo email
            if ($this->request->get('email')) {
                $user = User::where('email', $this->request->get('email'))->whereNotNull('email')->withTrashed()->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Email đã được đăng ký. Bạn vui lòng, chọn email khác');
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
