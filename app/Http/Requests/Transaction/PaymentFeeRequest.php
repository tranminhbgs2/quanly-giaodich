<?php

namespace App\Http\Requests\Transaction;

use App\Helpers\Constants;
use App\Models\BankAccounts;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentFeeRequest extends FormRequest
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
            'fee_paid' => ['required', 'numeric', 'min:0'],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'fee_paid' => 'Phí thanh toán',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Vui lòng nhập ID giao dịch khách lẻ',
            'id.integer' => 'ID giao dịch khách lẻ phải là số nguyên',
            'id.min' => 'ID giao dịch khách lẻ không hợp lệ',
            'fee_paid.required' => 'Vui lòng nhập phí thanh toán',
            'fee_paid.numeric' => 'Phí thanh toán phải là số',
            'fee_paid.min' => 'Phí thanh toán không hợp lệ',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = Transaction::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status_fee == 3) {
                    $validator->errors()->add('check_exist', 'Giao dịch khách lẻ đã thanh toán phí');
                } else {
                    if ($dep->status == Constants::USER_STATUS_DELETED) {
                        $validator->errors()->add('check_exist', 'Giao dịch khách lẻ đã bị xóa');
                    }
                    if ($dep->status_fee == 3) {
                        $validator->errors()->add('check_exist', 'Giao dịch khách lẻ đã thanh toán hết phí');
                    }
                    if ($dep->method == "DAO_HAN" && ($dep->total_fee - $dep->fee_paid) < $this->request->get('fee_paid') && $dep->status_fee == 2) {
                        $validator->errors()->add('check_exist', 'Phí thanh toán không được lớn hơn phí còn lại');
                    }
                    if ($dep->method != "DAO_HAN") {
                        $dep_bank = BankAccounts::where('type', Constants::ACCOUNT_TYPE_STAFF)->where('staff_id', auth()->user()->id)->first();
                        if ($dep_bank) {
                            if ($dep_bank->balance < $dep->price_transfer) {
                                $validator->errors()->add('check_exist', 'Số dư không đủ');
                            }
                        } else {
                            $validator->errors()->add('check_exist', 'Nhân viên chưa thêm tài khoản ngân hàng');
                        }
                    }
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy giao dịch khách lẻ');
            }


            // Check tài khoản nhận phí
            $bank_account = BankAccounts::Where('type', 'FEE')->withTrashed()->first();
            if (!$bank_account) {
                $validator->errors()->add('check_exist', 'Không tìm thấy tài khoản nhận phí');
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
