<?php

namespace App\Http\Requests\CashFlow;

use App\Models\CashFlow;
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
            'type' => ['required'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            // 'surrogate' => ['required'],
            // 'phone' => ['numeric', 'digits:10'],

        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'type' => 'Loại',
            'time_payment' => 'Thời gian thanh toán',

        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Truyền thiếu tham số name',
            'time_payment.date_format' => 'Định dạng ngày tháng không hợp lệ',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

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