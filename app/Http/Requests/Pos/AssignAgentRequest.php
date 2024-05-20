<?php

namespace App\Http\Requests\Pos;

use App\Helpers\Constants;
use App\Models\Agent;
use App\Models\Pos;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssignAgentRequest extends FormRequest
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
            'pos_id' => ['required', 'integer', 'min:1'],
            'agent_id' => ['required'],
            'fee' => ['required', 'numeric', 'min:0']
        ];

        return $rule;
    }

    public function attributes()
    {
        return [
            'pos_id' => 'ID máy POS',
            'agent_id' => 'ID đại lý',
            'fee' => 'Phí'
        ];
    }

    public function messages()
    {
        return [
            'pos_id.required' => 'Truyền thiếu tham số pos_id',
            'agent_id.required' => 'Truyền thiếu tham số agent_id',
            'fee.required' => 'Truyền thiếu tham số fee',
            'fee.numeric' => 'Tham số fee phải là số',
            'fee.min' => "Tham số fee tối thiểu phải là :min",
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check tồn tại
            $dep = Pos::where('id', $this->request->get('pos_id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Bản ghi đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin máy POS');
            }
            // Check tồn tại
            $dep = Agent::where('id', $this->request->get('agent_id'))->withTrashed()->first();
            if ($dep) {
                if ($dep->status == Constants::USER_STATUS_DELETED) {
                    $validator->errors()->add('check_exist', 'Bản ghi đã bị xóa');
                }
            } else {
                $validator->errors()->add('check_exist', 'Không tìm thấy thông tin đại lý');
            }
        });
        // $pos = Pos::find($this->request->get('pos_id')); // Giả sử bạn đang tìm Pos với id 1
        // $activeAgent = $pos->activeByAgents($this->request->get('agent_id'));
        //     print_r($activeAgent);die;
        // if ($activeAgent && $activeAgent->status == Constants::USER_STATUS_ACTIVE) {
        //     $validator->errors()->add('check_exist', 'Đại lý đã được gán cho máy POS này');
        // }
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
