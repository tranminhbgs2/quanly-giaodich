<?php

namespace App\Http\Requests\Transaction;

use App\Helpers\Constants;
use App\Models\BankAccounts;
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
            'bank_card' => ['required'],
            'method' => ['required', 'in:DAO_HAN,RUT_TIEN_MAT,ONLINE'],
            'category_id' => ['required', 'integer', 'min:1'],
            'pos_id' => ['required', 'integer', 'min:1'],
            'fee' => ['required', 'numeric', 'min:0', 'max:99'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            'customer_name' => ['required'],
            'price_nop' => ['required', 'numeric', 'min:0'],
            'price_rut' => ['required', 'numeric', 'min:0'],
            'price_fee' => ['required', 'numeric', 'min:0'],
            'price_transfer' => ['numeric', 'min:0'],
            'price_repair' => ['numeric', 'min:0'],

        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'bank_card' => 'Ngân hàng',
            'method' => 'Hình thức',
            'category_id' => 'Danh mục',
            'pos_id' => 'Máy Pos',
            'fee' => 'Phí',
            'time_payment' => 'Thời gian thanh toán',
            'customer_name' => 'Tên khách hàng',
            'price_nop' => 'Số tiền nộp',
            'price_rut' => 'Số tiền rút',
            'price_fee' => 'Số tiền phí',
            'price_transfer' => 'Số tiền chuyển',
            'price_repair' => 'Số tiền bù',
        ];
    }

    public function messages()
    {
        return [
            'bank_card.required' => 'Truyền thiếu tham số bank_card',
            'method.required' => 'Truyền thiếu tham số method',
            'category_id.required' => 'Truyền thiếu tham số category_id',
            'category_id.integer' => 'Tham số category_id phải là số nguyên',
            'category_id.min' => "Tham số category_id tối thiểu phải là :min",
            'pos_id.required' => 'Truyền thiếu tham số pos_id',
            'pos_id.integer' => 'Tham số pos_id phải là số nguyên',
            'pos_id.min' => "Tham số pos_id tối thiểu phải là :min",
            'fee.required' => 'Truyền thiếu tham số fee',
            'fee.numeric' => 'Tham số fee phải là số',
            'fee.min' => "Tham số fee tối thiểu phải là :min",
            'time_payment.date_format' => 'Tham số time_payment không đúng định dạng Y/m/d H:i:s',
            'customer_name.required' => 'Truyền thiếu tham số customer_name',
            'price_nop.required' => 'Truyền thiếu tham số price_nop',
            'price_nop.numeric' => 'Tham số price_nop phải là số',
            'price_nop.min' => "Tham số price_nop tối thiểu phải là :min",
            'price_rut.required' => 'Truyền thiếu tham số price_rut',
            'price_rut.numeric' => 'Tham số price_rut phải là số',
            'price_rut.min' => "Tham số price_rut tối thiểu phải là :min",
            'price_fee.required' => 'Truyền thiếu tham số price_fee',
            'price_fee.numeric' => 'Tham số price_fee phải là số',
            'price_fee.min' => "Tham số price_fee tối thiểu phải là :min",
            'price_transfer.numeric' => 'Tham số price_transfer phải là số',
            'price_transfer.min' => "Tham số price_transfer tối thiểu phải là :min",
            'price_repair.numeric' => 'Tham số price_repair phải là số',
            'price_repair.min' => "Tham số price_repair tối thiểu phải là :min",
            'fee.max' => 'Tham số fee tối đa phải là :max',

        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {
        //     // Check username
        //     $dep = Department::where('name', $this->request->get('name'))->withTrashed()->first();

        //     if ($dep) {
        //         $validator->errors()->add('check_exist', 'Tên nhóm quyền đã tồn tại');
        //     }

        //     $dep_code = Department::where('code', $this->request->get('code'))->withTrashed()->first();
        //     if ($dep_code) {
        //         $validator->errors()->add('check_exist', 'Mã nhóm quyền đã tồn tại');
        //     }
        // });
        if (auth()->user()->account_type == "STAFF") {
            $dep = BankAccounts::where('type', 'STAFF')->where('staff_id', auth()->user()->id)->first();
            if ($dep) {
                if ($dep->balance < $this->request->get('price_nop') || $dep->balance < $this->request->get('price_transfer')) {
                    $validator->errors()->add('check_exist', 'Số dư không đủ');
                }
            } else {
                $validator->errors()->add('check_exist', 'Nhân viên chưa thêm tài khoản ngân hàng');
            }
        } else if(auth()->user()->account_type == "SYSTEM") {
            $validator->errors()->add('check_exist', 'Chỉ nhân viên thực hiện giao dịch');
        }
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
