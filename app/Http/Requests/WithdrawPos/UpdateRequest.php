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
            'price_withdraw' => ['required', 'numeric', 'min:0'],
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
            'min' => ':attribute phải lớn hơn hoặc bằng :min',
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
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy GD rút tiền');
            }

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
