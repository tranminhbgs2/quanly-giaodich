<?php

namespace App\Http\Requests\User;

use App\Helpers\Constants;
use App\Models\Department;
use App\Models\Position;
use App\Models\Student;
use App\Models\User;
use App\Rules\CurrentDateLimitRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
            'fullname' => ['required'],
            'phone' => [
                'required',
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
            'action_ids' => 'required|array',
            'action_ids.*' => 'exists:positions,id', // Kiểm tra từng phần tử trong mảng
            'birthday' => [
                'date_format:d/m/Y',
                new CurrentDateLimitRule()
            ],
            'status' => [
                'required',
                'in:1,2,3'
            ],
        ];

        // Nếu nhập email thì check
        if ($this->request->get('email')) {
            $rules['email'] = ['email'];
        }

        // Nếu chọn avatar thì check
        if ($this->request->get('avatar')) {
            $rules['avatar'] = ['image'];
        }

        return $rule;
    }

    public function attributes()
    {
        return [
            //
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Mã nhân viên phải là số nguyên dương',
            'id.min' => 'Mã nhân viên phải là số nguyên dương, nhỏ nhất là 1',

            'fullname.required' => 'Truyền thiếu tham số fullname',
            'phone.required' => 'Truyền thiếu tham số phone',
            'email.email' => 'Email không đúng định dạng',
            'avatar.image' => 'Ảnh đại diện không đúng định dạng (.jpg, .png)',
            'birthday.date_format' => 'Ngày sinh sai định dạng (dd/mm/yyyy)',

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

            // Check mã phòng ban
            $depart = Department::find($this->request->get('department_id'));
            if (! $depart) {
                $validator->errors()->add('check_exist', 'Mã phòng/ban không tồn tại');
            }

            // Check mã chức danh có thuộc phòng ban hay không
            $pos = Position::where('id', $this->request->get('position_id'))
                ->where('department_id', $this->request->get('department_id'))
                ->first();
            if (! $pos) {
                $validator->errors()->add('check_exist', 'Mã chức danh không tồn tại hoặc không thuộc phòng/ban trên');
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
