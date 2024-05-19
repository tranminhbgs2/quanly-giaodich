<?php

namespace App\Http\Requests\Announcement;

use App\Helpers\Constants;
use App\Models\Announcement;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AnnounStoreRequest extends FormRequest
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
            'app_id' => 'nullable|int',
            'school_id' => 'nullable|int',
            'name' => [
                'required',
                function ($attribute, $value, $fail) {
                    $count = Announcement::where(['name' => $value])->first();
                    if ($count) {
                        return $fail('Tên thông báo đã tồn tại. Bạn vui lòng, chọn tên thông báo khác.');
                    }
                }
            ],
            'summary' => 'required',
            'content' => 'required',
            'start_date' => 'nullable|date_format:d/m/Y',
            'end_date' => 'nullable|date_format:d/m/Y',
            'notes' => 'nullable',
            'status' => 'in:0,1,2',
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ]
        ];
    }

    public function attributes()
    {
        return [];
    }

    public function messages()
    {
        return [
            'name.required' => 'Truyền thiếu tham số name',
            'summary.required' => 'Truyền thiếu tham số summary',
            'content.required' => 'Truyền thiếu tham số content',
            'status.in' => 'Status là một trong các giá trị 0/1/2',
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
