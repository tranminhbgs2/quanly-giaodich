<?php

namespace App\Http\Controllers\Dev;

use App\Helpers\Constants;
use App\Services\Notification\FcmService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PushController extends Controller
{
    protected $fcm;

    public function __construct(FcmService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * API bắn test thông báo
     * URL:
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pushFirebaseNotification(Request $request)
    {
        $rules = [
            'receiver_type' => 'required|in:ADMIN,CUSTOMER',
            'content' => 'required',
            'device_token' => 'required',
            'action_type' => 'required'
        ];

        $messages = [
            'receiver_type.required' => 'Truyền thiếu tham số receiver_type',
            'receiver_type.in' => 'Tham số receiver_type nhận một trong các giá trị: ADMIN/CUSTOMER',
            'content.required' => 'Truyền thiếu tham số content',
            'device_token.required' => 'Truyền thiếu tham số device_token',
            'action_type.required' => 'Truyền thiếu tham số action_type'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first(),
                'data' => null
            ]);
        }

        $receiver_type = $request->input('receiver_type');
        $content = $request->input('content');
        $device_token = $request->input('device_token');
        $action_type = $request->input('action_type');
        $record_id = $request->input('record_id');

        $data = [
            'title' => $content,
            'body' => $content,
            'action_type' => $action_type,
            'record_id' => $record_id,
        ];
        $registration_ids = [$device_token];

        $result = $this->fcm->multiplePusher($receiver_type, $data, $registration_ids);

        return response()->json([
            'code' => 200,
            'error' => 'Kết quả do firebase trả về',
            'data' => $result
        ]);
    }

    public function testReformat(Request $request)
    {
        die('a:'.$request->input('birthday'));
    }
}
