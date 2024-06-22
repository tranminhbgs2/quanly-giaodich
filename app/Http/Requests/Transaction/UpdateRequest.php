<?php

namespace App\Http\Requests\Transaction;

use App\Helpers\Constants;
use App\Models\BankAccounts;
use App\Models\Pos;
use App\Models\Transaction;
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
            'bank_card' => ['required'],
            'method' => ['required', 'in:DAO_HAN,RUT_TIEN_MAT,ONLINE,QR_CODE'],
            'category_id' => ['required', 'integer', 'min:1'],
            'pos_id' => ['required', 'integer', 'min:1'],
            'fee' => ['required', 'numeric', 'min:0', 'max:99'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            'customer_name' => ['required'],
            'price_nop' => ['required', 'numeric', 'min:0'],
            'price_rut' => ['required', 'numeric', 'min:0'],
            'price_fee' => ['required', 'numeric', 'min:0'],
            'price_transfer' => ['numeric', 'min:0'],
            'price_repair' => ['numeric'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED . ',' . Constants::USER_STATUS_DRAFT],
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
            'account_type' => 'Loại tài khoản',
            'price_nop' => 'Số tiền nộp',
            'price_rut' => 'Số tiền rút',
            'price_fee' => 'Số tiền phí',
            'price_transfer' => 'Số tiền chuyển',
            'profit' => 'Lợi nhuận',
            'price_repair' => 'Số tiền bù',
            'status' => 'Trạng thái',
            'type_card' => 'Loại thẻ',
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
            'status.integer' => 'Tham số status phải là số nguyên',
            'status.in' => 'Tham số status không hợp lệ',
            'fee.max' => 'Tham số fee tối đa phải là 99',
            'type_card.required_if' => 'Truyền thiếu tham số type_card',
            'type_card.in' => 'Tham số type_card không hợp lệ',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            //Kế toán này chỉ xem dc GD Online
            if (auth()->user()->id == 2370 && !in_array($this->request->get('method'), ['ONLINE', 'QR_CODE'])) {
                $validator->errors()->add('check_exist', 'Bạn chỉ được thực hiện giao dịch online hoặc qua mã QR');
            }
            // Check tồn tại
            $dep = Pos::where('id', $this->request->get('pos_id'))->first();

            if ($dep) {
                if ($dep->bank_code == "VIETCOMBANK") {
                    if (empty($this->request->get('bank_code'))) {
                        $validator->errors()->add('check_exist', 'Ngân hàng không được để trống');
                    } else {
                        if ($this->request->get('bank_code') == "VIETCOMBANK" && empty($this->request->get('type_card'))) {
                            $validator->errors()->add('check_exist', 'Loại thẻ không được để trống');
                        }
                    }
                }
            }
            $trans = Transaction::where('id', $this->request->get('id'))->first();
            if ($trans) {
                if ($trans->method == "DAO_HAN" && $trans->method != $this->request->get('method')) {
                    $validator->errors()->add('check_exist', 'Không thể thay đổi hình thức giao dịch đáo hạn');
                }elseif ($trans->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Giao dịch khách lẻ đã bị xóa');
                }elseif (auth()->user()->account_type == Constants::ACCOUNT_TYPE_STAFF) {
                    if ($this->request->get('method') == "DAO_HAN") {
                        $dep = BankAccounts::where('type', Constants::ACCOUNT_TYPE_STAFF)->where('staff_id', auth()->user()->id)->first();
                        if ($dep) {
                            if ($dep->balance < ($this->request->get('price_nop') - $dep->price_nop) || $dep->balance < ($this->request->get('price_transfer') - $dep->price_transfer)) {
                                $validator->errors()->add('check_exist', 'Số dư không đủ');
                            }
                        } else {
                            $validator->errors()->add('check_exist', 'Nhân viên chưa thêm tài khoản ngân hàng');
                        }
                    }
                } elseif (auth()->user()->account_type == "SYSTEM") {
                    $validator->errors()->add('check_exist', 'Chỉ nhân viên thực hiện giao dịch');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy giao dịch khách lẻ');
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
