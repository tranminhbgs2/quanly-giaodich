<?php

namespace App\Http\Requests\HoKinhDoanh;

use App\Helpers\Constants;
use App\Models\Categories;
use App\Models\HoKinhDoanh;
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
            'name' => ['required'],
            // 'phone' => ['numeric', 'digits:10'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên hộ kinh doanh',
            // 'phone' => 'Số điện thoại',
            'status' => 'Trạng thái',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Truyền thiếu tham số name',
            // 'phone.numeric' => 'Tham số phone phải là số',
            // 'phone.digits' => "Tham số phone phải có :digits chữ số",
            'status.integer' => 'Tham số status phải là số nguyên',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = HoKinhDoanh::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Hộ kinh doanh đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy hộ kinh doanh');
            }

            // if (! validateMobile($this->request->get('phone'))) {
            //     $validator->errors()->add('check_exist', 'Số điện thoại không đúng định dạng (09x/9x/849x)');
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
