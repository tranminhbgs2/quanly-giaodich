<?php

namespace App\Http\Requests\Customer;

use App\Helpers\Constants;
use App\Models\Student;
use App\Models\User;
use App\Rules\CurrentDateLimitRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CusUpdateRequest extends FormRequest
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
            'avatar' => ['nullable', 'image'],
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
                'date_format:d/m/Y',
                new CurrentDateLimitRule()
            ],
            'identifier' => ['min:9', 'max:12'],
            'issue_date' => [
                'date_format:d/m/Y',
                new CurrentDateLimitRule()
            ],
            'status' => [
                'required',
                'in:0,1,2,3'
            ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'fullname' => 'Họ và tên',
            'email' => 'Email',
            'avatar' => 'Ảnh đại diện',
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Truyền thiếu tham số fullname',
            'fullname.max' => ':attribute có độ dài tối đa :max ký tự',

            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email có độ dài tối đa :max ký tự',

            'avatar.image' => 'Ảnh đại diện không đúng định dạng (.jpg, .png)',
            'birthday.date_format' => 'Ngày sinh sai định dạng (dd/mm/yyyy)',
            'identifier.unique' => 'Số CMND/CCCD đã được đăng ký',
            'identifier.min' => 'Số CMND/CCCD có độ dài tối thiểu 9 chữ số',
            'identifier.max' => 'Số CMND/CCCD có độ dài tối đa 12 chữ số',
            'issue_date.date_format' => 'Ngày cấp sai định dạng (dd/mm/yyyy)',
            'status.required' => 'Truyền thiếu tham số status',
            'status.in' => 'Trạng thái không hợp lệ (0/1/2/3)',
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
                    $validator->errors()->add('check_exist', 'Thông tin khách hàng đã bị khóa vĩnh viễn, không thể cập nhật');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin khách hàng');
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

            // Check theo identifier
            if ($this->request->get('identifier')) {
                $user = User::where('identifier', $this->request->get('identifier'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('identifier')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Số CMND/CCCD đã được đăng ký');
                }
            }

            // Check ssc_list xem có SSC nào không hợp lệ không
            if ($this->request->get('ssc_list')) {
                $array_ssc_list = explode(',', $this->request->get('ssc_list'));
                if (count($array_ssc_list) > 0) {
                    $count = Student::whereIn('sscid', $array_ssc_list)->count();
                    if (count($array_ssc_list) != $count) {
                        $validator->errors()->add('check_exist', 'Có mã SSC không hợp lệ');
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
