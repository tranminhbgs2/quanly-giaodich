<?php

namespace App\Http\Requests\HoKinhDoanh;

use App\Models\HoKinhDoanh;
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
            'name' => ['required'],
            // 'surrogate' => ['required'],
            // 'phone' => ['numeric', 'digits:10'],

        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên hộ kinh doanh',
            // 'phone' => 'Số điện thoại',

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Truyền thiếu tham số name',
            // 'phone.numeric' => 'Tham số phone phải là số',
            // 'phone.digits' => "Tham số phone phải có :digits chữ số",
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check username
            $dep = HoKinhDoanh::where('name', $this->request->get('name'))->withTrashed()->first();

            if ($dep) {
                $validator->errors()->add('check_exist', 'Tên hộ kinh doanh đã tồn tại');
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
