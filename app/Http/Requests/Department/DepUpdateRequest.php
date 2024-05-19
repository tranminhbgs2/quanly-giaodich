<?php

namespace App\Http\Requests\Department;

use App\Helpers\Constants;
use App\Models\Department;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DepUpdateRequest extends FormRequest
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
            'name' => ['required'],
            'code' => ['required'],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên nhóm quyền',
            'code' => 'Mã nhóm quyền',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Truyền thiếu tham số name',
            'code.required' => 'Truyền thiếu tham số code',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = Department::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Nhóm quyền đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy nhóm quyền');
            }

            // Check theo email
            if ($this->request->get('name')) {
                $user = Department::where('name', $this->request->get('name'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('name')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Tên nhóm quyền đã được đăng ký');
                }
            }

            // Check theo code
            if ($this->request->get('code')) {
                $user = Department::where('code', $this->request->get('code'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('code')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Mã nhóm quyền đã được đăng ký');
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
