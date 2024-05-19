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

class CusStoreRequest extends FormRequest
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
            //'email' => ['email'],
            //'avatar' => ['image'],
            'username' => [
                'required',
                new UsernameRule()
            ],
            'password' => [
                'required',
                new PasswordRule(),
                'confirmed'
            ],
            'password_confirmation' => ['required'],
            'ssc_list' => [],
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ]
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
            'password_confirmation' => 'Xác nhận mật khẩu',
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Truyền thiếu tham số fullname',
            'phone.required' => 'Truyền thiếu tham số phone',
            'email.email' => 'Email không đúng định dạng',
            'avatar.image' => 'Ảnh đại diện không đúng định dạng (.jpg, .png)',

            'username.required' => 'Truyền thiếu tham số username',

            'password.required' => 'Truyền thiếu tham số password',
            'password.confirmed' => 'Xác nhận mật khẩu không đúng',

            'password_confirmation.required' => 'Truyền thiếu tham số password_confirmation',

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
                if (! validateMobile($this->request->get('phone'))) {
                    $validator->errors()->add('check_exist', 'Số điện thoại không đúng định dạng (09x/9x/849x)');
                }
            }

            // Check theo email
            $user = User::where('email', $this->request->get('email'))->whereNotNull('email')->withTrashed()->first();
            if ($user) {
                $validator->errors()->add('check_exist', 'Email đã được đăng ký. Bạn vui lòng, chọn email khác');
            }

            // Check ssc_list xem có SSC nào không hợp lệ không
            if ($this->request->get('ssc_list')) {
                $array_ssc_list = explode(',', $this->request->get('ssc_list'));
                if (count($array_ssc_list) > 0) {
                    $count = Student::whereIn('sscid', $array_ssc_list)->count();
                    if (count($array_ssc_list) != $count) {
                        $validator->errors()->add('check_exist', 'Có mã SSC không hợp lệ. Bạn vui lòng, kiểm tra lại');
                    }
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
