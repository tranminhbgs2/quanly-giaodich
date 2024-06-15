<?php

namespace App\Http\Requests\CashFlow;

use App\Helpers\Constants;
use App\Models\BankAccounts;
use App\Models\CashFlow;
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
            'type' => ['required'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'type' => 'Tên đại lý',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Truyền thiếu tham số type',
            'status.integer' => 'Tham số status phải là số nguyên',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = CashFlow::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Dòng tiền đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy dòng tiền');
            }

            $dep = BankAccounts::where('id', $this->request->get('acc_bank_id'))->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Tài khoản ngân hàng đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy tài khoản ngân hàng');
            }
            // if (! validateMobile($this->request->get('phone'))) {
            //     $validator->errors()->add('check_exist', 'Số điện thoại không đúng định dạng (09x/9x/849x)');
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
