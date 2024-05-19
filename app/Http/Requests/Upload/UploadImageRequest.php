<?php

namespace App\Http\Requests\Upload;

use App\Helpers\Constants;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UploadImageRequest extends FormRequest
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
            //'scope' => 'required|in:VERIFY_USER,UPLOAD_AVATAR',
            'field_name' => 'required',
            //'image_file' => 'required|image|max:20480',
            'image_file' => 'required|image',
            'platform' => [
                'required',
                'in:' . Constants::PLATFORM
            ],
        ];
    }

    public function messages()
    {
        return [
            'scope.required' => "Truyền thiếu phạm vi scope",
            'scope.in' => "Truyền phạm vi scope không hợp lệ (VERIFY_USER/UPLOAD_AVATAR)",

            'field_name.required' => "Truyền thiếu tên trường field_name",

            'image_file.required' => "Truyền thiếu file ảnh image_file",
            'image_file.image' => "File ảnh phải có định dạng: jpeg/png/bmp",
            'image_file.max' => "Dung lượng ảnh không vượt quá :max",

            'platform.required' => 'Truyền thiếu tham số platform',
            'platform.in' => 'Platform là một trong các giá trị ' . Constants::PLATFORM,
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check sự tồn tại
            $customer = Customer::where('uid', Auth::user()->admin_id)->first();
            if (!$customer) {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin khách hàng');
            }
        });
    }

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
