<?php

namespace App\Http\Requests\Category;

use App\Helpers\Constants;
use App\Models\Categories;
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
            'name' => ['required'],
            'fee' => ['required', 'numeric', 'min:0', 'max:99'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên danh mục',
            'code' => 'Mã danh mục',
            'fee' => 'Phí',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Truyền thiếu tham số name',
            'code.required' => 'Truyền thiếu tham số code',
            'fee.required' => 'Truyền thiếu tham số fee',
            'fee.numeric' => 'Tham số fee phải là số',
            'fee.min' => "Tham số fee tối thiểu phải là :min",
            'status.integer' => 'Tham số status phải là số nguyên',
            'status.in' => 'Tham số status không hợp lệ',
            'fee.max' => "Tham số fee tối đa phải là :max",
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = Categories::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Danh mục đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy danh mục');
            }

            // Check theo email
            if ($this->request->get('name')) {
                $user = Categories::where('name', $this->request->get('name'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('name')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Tên danh mục đã được đăng ký');
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
