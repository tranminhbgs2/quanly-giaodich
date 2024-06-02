<?php

namespace App\Http\Requests\Transfer;

use App\Helpers\Constants;
use App\Models\BankAccounts;
use App\Models\Transfer;
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
            'acc_bank_from_id' => ['required'],
            'acc_bank_to_id' => ['required'],
            'type_to' => ['required', 'in:STAFF,AGENCY'],
            'price' => ['required', 'numeric', 'min:0'],
            'time_payment' => ['required', 'date_format:Y/m/d H:i:s'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'id' => 'Mã ID',
            'acc_bank_from_id' => 'Tài khoản nguồn',
            'acc_bank_to_id' => 'Tài khoản đích',
            'type_to' => 'Loại tài khoản đích',
            'price' => 'Số tiền',
            'time_payment' => 'Thời gian chuyển tiền',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Mã giao dịch phải là số nguyên dương',
            'id.min' => 'Mã giao dịch phải là số nguyên dương, nhỏ nhất là 1',

            'acc_bank_from_id.required' => 'Truyền thiếu tham số acc_bank_from_id',
            'acc_bank_to_id.required' => 'Truyền thiếu tham số acc_bank_to_id',
            'type_to.required' => 'Truyền thiếu tham số type_to',
            'type_to.in' => 'Truyền tham số type_to không hợp lệ',
            'price.required' => 'Truyền thiếu tham số price',
            'price.numeric' => 'Số tiền phải là số',
            'price.min' => 'Số tiền phải lớn hơn hoặc bằng 0',
            'time_payment.required' => 'Truyền thiếu tham số time_payment',
            'time_payment.date_format' => 'Thời gian chuyển tiền không đúng định dạng Y/m/d H:i:s',
            'status.integer' => 'Trạng thái phải là số nguyên',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $bank_from = BankAccounts::where('id', $this->request->get('acc_bank_from_id'))->first();
            if (!$bank_from) {
                $validator->errors()->add('acc_bank_from_id', 'Tài khoản chuyển không tồn tại');
            }
            $bank_to = BankAccounts::where('id', $this->request->get('acc_bank_to_id'))->first();
            if (!$bank_to) {
                $validator->errors()->add('acc_bank_to_id', 'Tài khoản nhận không tồn tại');
            }
            // Check username
            $dep = Transfer::
            where('acc_bank_from_id', $this->request->get('acc_bank_from_id'))
            ->where('acc_bank_to_id', $this->request->get('acc_bank_to_id'))
            ->where('time_payment', $this->request->get('time_payment'))
            ->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Chuyển tiền đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy Chuyển tiền');
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
