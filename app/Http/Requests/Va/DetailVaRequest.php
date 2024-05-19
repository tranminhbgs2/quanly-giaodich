<?php

namespace App\Http\Requests\Va;

use App\Helpers\Constants;
use App\Models\Va;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DetailVaRequest extends FormRequest
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
        return [
            'id' => [
                'required', 'integer', 'min:1',
                function ($attribute, $value, $fail) {
                    $va = Va::where('id', $value)->first();
                    if (! $va) {
                        return $fail('Không tìm thấy thông tin thẻ ảo');
                    }
                },
            ],
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'id.required' => 'Truyền thiếu tham số id',
            'id.integer' => 'Id thẻ ảo phải là số nguyên dương',
            'id.min' => 'Id thẻ ảo phải là số nguyên dương, nhỏ nhất là 1',

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM,
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //
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
