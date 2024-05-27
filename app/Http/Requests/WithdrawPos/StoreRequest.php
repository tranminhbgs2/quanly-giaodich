<?php

namespace App\Http\Requests\WithdrawPos;

use App\Helpers\Constants;
use App\Models\WithdrawPos;
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
            'pos_id' => ['required', 'integer', 'min:1'],
            'account_bank_id' => ['required', 'integer', 'min:1'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            'price_withdraw' => ['required', 'numeric', 'min:0'],

        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'pos_id' => 'Máy Pos',
            'account_bank_id' => 'Tài khoản ngân hàng hưởng thụ',
            'time_payment' => 'Thời gian rút tiền',
            'price_withdraw' => 'Số tiền rút',
        ];
    }

    public function messages()
    {
        return [
            'pos_id.required' => 'Truyền thiếu tham số máy pos',
            'pos_id.integer' => 'Mã máy pos phải là số nguyên dương',
            'pos_id.min' => 'Mã máy pos phải là số nguyên dương, nhỏ nhất là 1',

            'account_bank_id.required' => 'Truyền thiếu tham số tài khoản ngân hàng hưởng thụ',
            'account_bank_id.integer' => 'ID tài khoản ngân hàng hưởng thụ phải là số nguyên dương',
            'account_bank_id.min' => 'ID tài khoản ngân hàng hưởng thụ phải là số nguyên dương, nhỏ nhất là 1',

            'time_payment.date_format' => 'Thời gian rút tiền không đúng định dạng Y-m-d H:i:s',

            'price_withdraw.required' => 'Truyền thiếu tham số số tiền rút',
            'price_withdraw.numeric' => 'Số tiền rút phải là số',
            'price_withdraw.min' => 'Số tiền rút phải là số dương',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check username
            // $dep = WithdrawPos::where('name', $this->request->get('name'))->withTrashed()->first();

            // if ($dep) {
            //     $validator->errors()->add('check_exist', 'Tên danh mục đã tồn tại');
            // }

            // $dep_code = Categories::where('code', $this->request->get('code'))->withTrashed()->first();
            // if ($dep_code) {
            //     $validator->errors()->add('check_exist', 'Mã danh mục đã tồn tại');
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
