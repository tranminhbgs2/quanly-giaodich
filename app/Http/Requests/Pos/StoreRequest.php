<?php

namespace App\Http\Requests\Pos;

use App\Models\Pos;
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
            'bank_code' => ['required'],
            'method' => ['required'],
            'name' => ['required'],
            'fee' => ['required', 'numeric', 'min:0'],
            'code' => ['required'],
            'hkd_id' => ['numeric', 'min:0'],
            'fee_cashback' => ['numeric', 'min:0'],
            'total_fee' => ['numeric', 'min:0'],
            'price_pos' => ['numeric', 'min:0'],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'bank_code' => 'Mã ngân hàng',
            'method' => 'Phương thức',
            'name' => 'Tên máy Pos',
            'fee' => 'Phí',
            'code' => 'Mã máy Pos',
            'hkd_id' => 'Hkd Id',
            'fee_cashback' => 'Phí cashback',
            'total_fee' => 'Tổng phí',
            'price_pos' => 'Tiền tồn Pos',

        ];
    }

    public function messages()
    {
        return [
            'bank_code.required' => 'Mã ngân hàng không được để trống',
            'method.required' => 'Phương thức không được để trống',
            'name.required' => 'Tên máy Pos không được để trống',
            'fee.required' => 'Phí không được để trống',
            'fee.numeric' => 'Phí phải là số',
            'fee.min' => 'Phí phải lớn hơn hoặc bằng 0',
            'code.required' => 'Mã máy Pos không được để trống',
            'hkd_id.numeric' => 'Hkd Id phải là số',
            'hkd_id.min' => 'Hkd Id phải lớn hơn hoặc bằng 0',
            'fee_cashback.numeric' => 'Phí cashback phải là số',
            'fee_cashback.min' => 'Phí cashback phải lớn hơn hoặc bằng 0',
            'total_fee.numeric' => 'Tổng phí phải là số',
            'total_fee.min' => 'Tổng phí phải lớn hơn hoặc bằng 0',
            'price_pos.numeric' => 'Tiền tồn Pos phải là số',
            'price_pos.min' => 'Tiền tồn Pos phải lớn hơn hoặc bằng 0',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check username
            $dep = Pos::where('name', $this->request->get('name'))->withTrashed()->first();

            if ($dep) {
                $validator->errors()->add('check_exist', 'Tên máy POS đã tồn tại');
            }

            $dep_code = Pos::where('code', $this->request->get('code'))->withTrashed()->first();
            if ($dep_code) {
                $validator->errors()->add('check_exist', 'Mã máy POS đã tồn tại');
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
