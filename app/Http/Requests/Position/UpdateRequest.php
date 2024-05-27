<?php

namespace App\Http\Requests\Position;

use App\Helpers\Constants;
use App\Models\Position;
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
            'function_id' => ['required', 'integer'],
            'name' => ['required'],
            'code' => ['required'],
            'url' => ['required', ],
            'is_default' => ['required', 'boolean'],
            'status' => ['integer', 'in:' . Constants::USER_STATUS_ACTIVE . ',' . Constants::USER_STATUS_DELETED . ',' . Constants::USER_STATUS_LOCKED ],
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'id' => 'ID',
            'function_id' => 'function_id nhóm quyền',
            'name' => 'Tên hành động',
            'code' => 'Mã hành động',
            'url' => 'Đường dẫn',
            'is_default' => 'Mặc định',
            'status' => 'Trạng thái',];
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'boolean' => ':attribute phải là true hoặc false',
            'integer' => ':attribute phải là số nguyên',
            'in' => ':attribute không hợp lệ',];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = Position::where('id', $this->request->get('id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Danh mục đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy danh mục');
            }

            // Check theo email
            if ($this->request->get('name')) {
                $user = Position::where('name', $this->request->get('name'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('name')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Tên danh mục đã được đăng ký');
                }
            }

            // Check theo identifier
            if ($this->request->get('code')) {
                $user = Position::where('code', $this->request->get('code'))
                    ->whereNotIn('id', [$this->request->get('id')])
                    ->whereNotNull('code')
                    ->withTrashed()
                    ->first();
                if ($user) {
                    $validator->errors()->add('check_exist', 'Mã danh mục đã được đăng ký');
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
