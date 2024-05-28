<?php

namespace App\Http\Requests\User;

use App\Helpers\Constants;
use App\Models\Position;
use App\Models\User;
use App\Rules\CurrentDateLimitRule;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserStoreRequest extends FormRequest
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
            'birthday' => [
                'date_format:d/m/Y',
                new CurrentDateLimitRule()
            ],
            'action_ids' => 'required|array',
            'action_ids.*' => 'exists:positions,id',
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
            'status' => [
                'required',
                'in:1,2,3'
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

            'status.required' => 'Truyền thiếu tham số status',
            'status.in' => 'Trạng thái không hợp lệ (1/2/3)',
            'action_ids.required' => 'Truyền thiếu tham số action_ids',
            'action_ids.array' => 'Danh sách hành động không đúng định dạng',
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

            // Check mã chức danh có thuộc phòng ban hay không
            $pos = Position::where('id', $this->request->get('position_id'))->first();
            if (! $pos) {
                $validator->errors()->add('check_exist', 'Mã chức danh không tồn tại');
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
