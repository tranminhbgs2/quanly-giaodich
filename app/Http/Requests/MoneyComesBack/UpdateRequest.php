<?php

namespace App\Http\Requests\MoneyComesBack;

use App\Helpers\Constants;
use App\Models\MoneyComesBack;
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
            'pos_id' => ['required', 'numeric', 'min:1'],
            'lo_number' => ['required', 'numeric', 'min:1'],
            // 'fee' => ['required', 'numeric', 'min:0'],
            'total_price' => ['required', 'numeric', 'min:0'],
            // 'payment' => ['required', 'numeric', 'min:0'],
            'time_end' => 'date_format:Y/m/d H:i:s',
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'pos_id' => 'Mã POS',
            'lo_number' => 'Số Lô',
            'fee' => 'Phí gốc máy pos',
            'total_price' => 'Tổng tiền xử lý',
            'payment' => 'Thành tiền',
            'time_end' => 'Thời gian xử lý',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'numeric' => ':attribute phải là số',
            'min' => ':attribute phải lớn hơn :min',
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
            // Check username
            $dep = MoneyComesBack::where('id', $this->request->get('id'))->first();
            if ($dep) {

                if (!empty($dep->time_end) && $dep->agent_id = 0) {
                    $validator->errors()->add('check_exist', 'Lô tiền về đã kết toán');
                }
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Lô tiền về đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy lô tiền về');
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
