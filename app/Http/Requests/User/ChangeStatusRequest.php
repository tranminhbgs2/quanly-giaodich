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

class ChangeStatusRequest extends FormRequest
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
            //
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Mã nhân viên phải là số nguyên dương',
            'id.min' => 'Mã nhân viên phải là số nguyên dương, nhỏ nhất là 1',

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
                    $validator->errors()->add('check_exist', 'Thông tin nhân viên đã bị xóa vĩnh viễn, không thể cập nhật');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin nhân viên');
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
