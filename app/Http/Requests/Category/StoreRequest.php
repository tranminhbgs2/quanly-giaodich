<?php

namespace App\Http\Requests\Category;

use App\Helpers\Constants;
use App\Models\Categories;
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
            'name' => ['required'],
            'fee' => ['required', 'numeric', 'min:0', 'max:99'],

        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên danh mục',
            'code' => 'Mã danh mục',
            'fee' => 'Phí',

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
            'fee.max' => "Tham số fee tối đa phải là :max",

        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check username
            $dep = Categories::where('name', $this->request->get('name'))->withTrashed()->first();

            if ($dep) {
                $validator->errors()->add('check_exist', 'Tên danh mục đã tồn tại');
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
