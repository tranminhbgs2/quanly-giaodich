<?php

namespace App\Http\Requests\WithdrawPos;

use App\Helpers\Constants;
use App\Models\WithdrawPos;
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
            'hkd_id' => ['required', 'integer', 'min:1'],
            'account_bank_id' => ['required', 'integer', 'min:1'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            'price_withdraw' => ['required', 'numeric'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'id' => 'ID',
            'hkd_id' => 'Hộ kinh doanh',
            'account_bank_id' => 'Tài khoản ngân hàng hưởng thụ',
            'time_payment' => 'Thời gian rút tiền',
            'price_withdraw' => 'Số tiền rút',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'integer' => ':attribute phải là số nguyên',
            'numeric' => ':attribute phải là số',
            'date_format' => ':attribute không đúng định dạng Y/m/d H:i:s',
            'in' => ':attribute không hợp lệ',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = WithdrawPos::where('id', $this->request->get('id'))->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Rút tiền đã bị xóa');
                }
                if ($dep->hkd_id != $this->request->get('hkd_id')) {
                    $validator->errors()->add('check_exist', 'Không được thay đổi hộ kinh doanh');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy GD rút tiền');
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
