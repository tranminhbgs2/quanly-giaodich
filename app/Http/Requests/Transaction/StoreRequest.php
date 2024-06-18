<?php

namespace App\Http\Requests\Transaction;

use App\Helpers\Constants;
use App\Models\BankAccounts;
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
            'method' => ['required', 'in:DAO_HAN,RUT_TIEN_MAT,ONLINE,QR_CODE'],
            'category_id' => ['integer', 'min:0'],
            'pos_id' => ['integer', 'min:0'],
            'fee' => ['numeric', 'min:0', 'max:99'],
            'time_payment' => ['date_format:Y/m/d H:i:s'],
            'customer_name' => ['required'],
            'price_nop' => ['numeric', 'min:0'],
            'price_rut' => ['numeric', 'min:0'],
            'price_fee' => ['numeric', 'min:0'],
            'price_transfer' => ['numeric', 'min:0'],
            'price_repair' => ['numeric'],
            'type_card' => ['required_if:bank_code,VIETCOMBANK', 'in:VISA,MASTER,NAPAS,AMEX,JCB'],


        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'bank_card' => 'Số thẻ',
            'method' => 'Phương thức',
            'category_id' => 'Danh mục',
            'pos_id' => 'Máy POS',
            'fee' => 'Phí',
            'time_payment' => 'Thời gian thanh toán',
            'customer_name' => 'Tên khách hàng',
            'price_nop' => 'Số tiền nộp',
            'price_rut' => 'Số tiền rút',
            'price_fee' => 'Phí rút',
            'price_transfer' => 'Số tiền chuyển',
            'price_repair' => 'Số tiền sửa',
            'type_card' => 'Loại thẻ',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'in' => ':attribute không hợp lệ',
            'numeric' => ':attribute phải là số',
            'min' => ':attribute không được nhỏ hơn :min',
            'max' => ':attribute không được lớn hơn :max',
            'date_format' => ':attribute không đúng định dạng Y/m/d H:i:s',
            'required_if' => ':attribute không được để trống',
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

            //     $dep_code = Department::where('code', $this->request->get('code'))->withTrashed()->first();
            //     if ($dep_code) {
            //         $validator->errors()->add('check_exist', 'Mã nhóm quyền đã tồn tại');
            //     }
            // });
            if (auth()->user()->account_type == Constants::ACCOUNT_TYPE_STAFF || auth()->user()->account_type == Constants::ACCOUNT_TYPE_ACCOUNTANT) {
                if ($this->request->get('method') == "DAO_HAN") {
                    $dep = BankAccounts::where('type', Constants::ACCOUNT_TYPE_STAFF)->where('staff_id', auth()->user()->id)->first();
                    if ($dep) {
                        if ($dep->balance < $this->request->get('price_nop') || $dep->balance < $this->request->get('price_transfer')) {
                            $validator->errors()->add('check_exist', 'Số dư không đủ');
                        }
                    } else {
                        $validator->errors()->add('check_exist', 'Nhân viên chưa thêm tài khoản ngân hàng');
                    }
                }
            } else if (auth()->user()->account_type == "SYSTEM") {
                $validator
                    ->errors()->add('check_exist', 'Chỉ nhân viên thực hiện giao dịch');
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
