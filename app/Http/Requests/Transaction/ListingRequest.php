<?php

namespace App\Http\Requests\Transaction;

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
            'pos_id' => 'integer|min:0',
            'category_id' => 'integer|min:0',
            'lo_number' => 'integer|min:0',
            'account_type' => 'string|in:STAFF,SYSTEM',
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
            'pos_id.integer' => 'Tham số pos_id phải là số nguyên',
            'pos_id.min' => "Tham số pos_id tối thiểu phải là :min",
            'category_id.integer' => 'Tham số category_id phải là số nguyên',
            'category_id.min' => "Tham số category_id tối thiểu phải là :min",
            'lo_number.integer' => 'Tham số lo_number phải là số nguyên',
            'lo_number.min' => "Tham số lo_number tối thiểu phải là :min",
            'account_type.string' => 'Tham số account_type phải là chuỗi',
            'account_type.in' => 'Tham số account_type không hợp lệ'
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //check date_from date_format
            if ($this->request->has('date_from')&& !empty($this->request->get('date_from')) && !strtotime($this->request->get('date_from'))) {
                $validator->errors()->add('check_date', 'Tham số date_from không đúng định dạng Y-m-d');
            }

            if ($this->request->has('date_to') && !empty($this->request->get('date_to')) && !strtotime($this->request->get('date_to'))) {
                $validator->errors()->add('check_date', 'Tham số date_to không đúng định dạng Y-m-d');
            }
            //check date_from and date_to
            if ($this->request->has('date_from') && $this->request->has('date_to')) {
                $dateFrom = strtotime($this->request->get('date_from'));
                $dateTo = strtotime($this->request->get('date_to'));
                if ($dateFrom > $dateTo) {
                    $validator->errors()->add('check_date', 'Tham số date_from phải nhỏ hơn hoặc bằng date_to');
                }
            }
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
