<?php

namespace App\Http\Requests\MoneyComesBack;

use App\Helpers\Constants;
use App\Models\MoneyComesBack;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class KetToanLoRequest extends FormRequest
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
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'time_end' => 'Thời gian kết toán',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Mã giao dịch phải là số nguyên dương',
            'id.min' => 'Mã giao dịch phải là số nguyên dương, nhỏ nhất là 1',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = MoneyComesBack::where('id', $this->request->get('id'))->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Lô tiền về đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy lô tiền về');
            }
            // check định dạng của time_end
            if (!empty($this->request->get('time_end'))) {
                $time_end = strtotime($this->request->get('time_end'));
                if ($time_end === false) {
                    $validator->errors()->add('time_end', 'Thời gian kết toán không đúng định dạng Y/m/d H:i:s');
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
