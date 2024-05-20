<?php

namespace App\Http\Requests\HoKinhDoanh;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ListingRequest extends FormRequest
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
        return [
            'keyword' => [],
            'status' => 'integer|min:0',
            'page_index' => 'integer|min:1|required_with:page_size',
            'page_size' => 'integer|min:1|required_with:page_index'
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'page_index.integer' => 'Tham số page_index phải là số nguyên',
            'page_index.min' => "Tham số page_index tối thiểu phải là :min",
            'page_index.required_with' => 'Truyền thiếu tham số page_index',

            'page_size.integer' => 'Tham số page_size phải là số nguyên',
            'page_size.min' => "Tham số page_size tối thiểu phải là :min",
            'page_size.required_with' => 'Truyền thiếu tham số page_size',
            'status.integer' => 'Tham số status phải là số nguyên',
            'status.min' => "Tham số status tối thiểu phải là :min",
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check key keyword
            // if (!$this->request->has('keyword')) {
            //     $validator->errors()->add('check_exist', 'Truyền thiếu tham số keyword');
            // }
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
