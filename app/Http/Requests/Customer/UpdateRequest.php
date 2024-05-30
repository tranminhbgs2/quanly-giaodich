<?php

namespace App\Http\Requests\Customer;

use App\Helpers\Constants;
use App\Models\User;
use App\Rules\CurrentDateLimitRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
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
            'id' => ['required', 'integer', 'min:1'],
            'fullname' => ['required', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => [
                function ($attribute, $value, $fail) {
                    if (validateMobile($value)) {
                        $user = User::where('phone', formatMobile($value))
                            ->whereNotIn('id', [$this->input('id')])
                            ->withTrashed()
                            ->first();

                        if ($user) {
                            return $fail('Số điện thoại đã được đăng ký');
                        }
                    } else {
                        return $fail('Số điện thoại không đúng định dạng (09x/9x/849x)');
                    }
                },
            ],
            'birthday' => [
                'date_format:d/m/Y'
            ],
            'status' => [
                'in:1,2,3'
            ],
            'action_ids' => 'required|array',
            'action_ids.*' => 'exists:positions,id',
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'fullname' => 'Họ và tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'birthday' => 'Ngày sinh',

        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Mã khách hàng phải là số nguyên dương',
            'id.min' => 'Mã khách hàng phải là số nguyên dương, nhỏ nhất là 1',

            'fullname.required' => 'Truyền thiếu tham số fullname',
            'fullname.max' => 'Họ và tên không được vượt quá 50 ký tự',

            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 100 ký tự',

            'phone.required' => 'Truyền thiếu tham số phone',

            'birthday.date_format' => 'Ngày sinh không đúng định dạng d/m/Y',

            'status.required' => 'Truyền thiếu tham số status',
            'status.in' => 'Trạng thái không hợp lệ',
            'action_ids.required' => 'Truyền thiếu tham số action_ids',
            'action_ids.array' => 'Tham số action_ids phải là mảng',
            'action_ids.*.exists' => 'Một hoặc nhiều action_id không tồn tại',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $user = User::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($user) {
                if ($user->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Thông tin nhân viên đã bị khóa vĩnh viễn, không thể cập nhật');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin nhân viên');
            }

            // Check theo email
            if ($this->request->get('email')) {
                $user = User::where('email', $this->request->get('email'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('email')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Email đã được đăng ký');
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
