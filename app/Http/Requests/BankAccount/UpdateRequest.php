<?php

namespace App\Http\Requests\BankAccount;

use App\Helpers\Constants;
use App\Models\BankAccounts;
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
            'account_name' => ['required'],
            'account_number' => ['required'],
            'bank_code' => ['required', 'string'],
            'agent_id' => ['integer', 'min:0'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
            'staff_id' => ['integer', 'min:0'],
            'type' => ['required', 'in:STAFF,AGENCY,MASTER,FEE'],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'id' => 'ID',
            'account_name' => 'Tên tài khoản',
            'account_number' => 'Số tài khoản',
            'bank_code' => 'Mã ngân hàng',
            'agent_id' => 'ID đại lý',
            'status' => 'Trạng thái',
            'staff_id' => 'ID nhân viên',
            'type' => 'Loại tài khoản',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Tham số id phải là số nguyên',
            'id.min' => "Tham số id tối thiểu phải là :min",
            'account_name.required' => 'Truyền thiếu tham số account_name',
            'account_number.required' => 'Truyền thiếu tham số account_number',
            'bank_code.required' => 'Truyền thiếu tham số bank_code',
            'bank_code.string' => 'Tham số bank_code phải là chuỗi',
            'agent_id.integer' => 'Tham số agent_id phải là số nguyên',
            'agent_id.min' => "Tham số agent_id tối thiểu phải là :min",
            'status.integer' => 'Tham số status phải là số nguyên',
            'status.in' => 'Tham số status không hợp lệ',
            'staff_id.integer' => 'Tham số staff_id phải là số nguyên',
            'staff_id.min' => "Tham số staff_id tối thiểu phải là :min",
            'type.required' => 'Truyền thiếu tham số type',
            'type.in' => 'Tham số type không hợp lệ',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = BankAccounts::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Tài khoản hưởng thụ đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy Tài khoản hưởng thụ');
            }

            switch ($this->request->get('type')) {
                case Constants::ACCOUNT_TYPE_STAFF:
                    if (empty($this->request->get('staff_id'))) {
                        $validator->errors()->add('check_exist_staff', 'Nhân viên không được bỏ trống');
                    }
                    break;
                case 'AGENCY':
                    if (empty($this->request->get('agent_id'))) {
                        $validator->errors()->add('check_exist_agent', 'Đại lý không được bỏ trống');
                    }
                    break;
                case 'FEE':
                    $fee = BankAccounts::where('type', 'FEE')->where('status', Constants::USER_STATUS_ACTIVE)->first();
                    if ($fee) {
                        $validator->errors()->add('check_exist', 'Tài khoản phí đã tồn tại');
                    }
                    break;
            }
            // Check theo email
            if ($this->request->get('account_name')) {
                $user = BankAccounts::where('account_name', $this->request->get('account_name'))
                    ->where('account_number', $this->request->get('account_number'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('account_name')
                    ->withTrashed()
                    ->first();
                if ($user && $user->account_name) {
                    $validator->errors()->add('check_exist', 'Tài khoản hưởng thụ đã được đăng ký');
                }
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
