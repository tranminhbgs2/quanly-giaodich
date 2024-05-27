<?php

namespace App\Http\Requests\MoneyComesBack;

use App\Models\MoneyComesBack;
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
            'pos_id' => ['required', 'numeric', 'min:1'],
            'lo_number' => ['required', 'numeric', 'min:1'],
            // 'fee' => ['required', 'numeric', 'min:0'],
            'total_price' => ['required', 'numeric', 'min:0'],
            // 'payment' => ['required', 'numeric', 'min:0'],
            'time_end' => 'required|date_format:Y-m-d H:i:s',
            'agent_id' => 'numeric|min:0',
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
            'time_end' => 'Thời gian kết toán',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'numeric' => ':attribute phải là số',
            'min' => ':attribute phải lớn hơn :min',
            'date_format' => ':attribute không đúng định dạng Y-m-d H:i:s',
            'agent_id.numeric' => 'ID đại lý phải là số',
            'agent_id.min' => 'ID đại lý phải lớn hơn 0',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check username
            $dep = MoneyComesBack::where('pos_id', $this->request->get('pos_id'))
                ->where('lo_number', $this->request->get('lo_number'))
                ->where('time_end', $this->request->get('time_end'))
                ->withTrashed()->first();

            if ($dep) {
                $validator->errors()->add('check_exist', 'Lô tiền về đã tồn tại');
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
