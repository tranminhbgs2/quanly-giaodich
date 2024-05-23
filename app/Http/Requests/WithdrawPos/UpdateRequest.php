<?php

namespace App\Http\Requests\WithdrawPos;

use App\Helpers\Constants;
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
            'pos_id' => ['required', 'integer', 'min:1'],
            'account_bank_id' => ['required', 'integer', 'min:1'],
            'time_payment' => ['date_format:Y-m-d H:i:s'],
            'price_withdraw' => ['required', 'numeric', 'min:0'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'id' => 'ID',
            'pos_id' => 'Máy Pos',
            'account_bank_id' => 'Tài khoản ngân hàng hưởng thụ',
            'time_payment' => 'Thời gian rút tiền',
            'price_withdraw' => 'Số tiền rút',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Mã giao dịch phải là số nguyên dương',
            'id.min' => 'Mã giao dịch phải là số nguyên dương, nhỏ nhất là 1',

            'pos_id.required' => 'Truyền thiếu tham số máy pos',
            'pos_id.integer' => 'Mã máy pos phải là số nguyên dương',
            'pos_id.min' => 'Mã máy pos phải là số nguyên dương, nhỏ nhất là 1',

            'account_bank_id.required' => 'Truyền thiếu tham số tài khoản ngân hàng hưởng thụ',
            'account_bank_id.integer' => 'ID tài khoản ngân hàng hưởng thụ phải là số nguyên dương',
            'account_bank_id.min' => 'ID tài khoản ngân hàng hưởng thụ phải là số nguyên dương, nhỏ nhất là 1',

            'time_payment.date_format' => 'Thời gian rút tiền không đúng định dạng Y-m-d H:i:s',

            'price_withdraw.required' => 'Truyền thiếu tham số số tiền rút',
            'price_withdraw.numeric' => 'Số tiền rút phải là số',
            'price_withdraw.min' => 'Số tiền rút phải lớn hơn hoặc bằng 0',

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
            // Check tồn tại
            // $dep = Categories::where('id', $this->request->get('id'))->withTrashed()->first();
            // if ($dep) {
            //     if ($dep->status == Constants::USER_STATUS_DELETED) {
            //         $validator->errors()->add('check_exist', 'Danh mục đã bị xóa');
            //     }
            // } else {
            //     $validator->errors()->add('check_exist', 'Không tìm thấy danh mục');
            // }

            // // Check theo email
            // if ($this->request->get('name')) {
            //     $user = Categories::where('name', $this->request->get('name'))
            //         ->whereNotIn('id', [$this->request->get('id')])
            //         ->whereNotNull('name')
            //         ->withTrashed()
            //         ->first();
            //     if ($user) {
            //         $validator->errors()->add('check_exist', 'Tên danh mục đã được đăng ký');
            //     }
            // }

            // // Check theo identifier
            // if ($this->request->get('code')) {
            //     $user = Categories::where('code', $this->request->get('code'))
            //         ->whereNotIn('id', [$this->request->get('id')])
            //         ->whereNotNull('code')
            //         ->withTrashed()
            //         ->first();
            //     if ($user) {
            //         $validator->errors()->add('check_exist', 'Mã danh mục đã được đăng ký');
            //     }
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
