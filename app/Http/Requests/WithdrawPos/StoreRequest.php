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
            'hkd_id' => ['required', 'integer', 'min:1'],
            'account_bank_id' => ['required', 'integer', 'min:1'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            'price_withdraw' => ['required', 'numeric'],

        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'hkd_id' => 'Hộ kinh doanh',
            'account_bank_id' => 'Tài khoản ngân hàng hưởng thụ',
            'time_payment' => 'Thời gian rút tiền',
            'price_withdraw' => 'Số tiền rút',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'integer' => ':attribute phải là số nguyên',
            'numeric' => ':attribute phải là số',
            'date_format' => ':attribute không đúng định dạng Y/m/d H:i:s',
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
