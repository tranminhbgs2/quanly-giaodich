<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Log\SessionLogRequest;
use App\Repositories\Log\LogRepo;
use Illuminate\Http\Request;

class LogController extends Controller
{
    protected $log_repo;

    public function __construct(LogRepo $logRepo)
    {
        $this->log_repo = $logRepo;
    }

    /**
     * API lấy ds lịch sử phiên làm việc
     * URL: {{url}}/api/v1/logs/sessions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessionListing(SessionLogRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['action_type'] = request('action_type', 'ALL');
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->log_repo->sessionListing($params);
        $total = $this->log_repo->sessionListing($params, true);

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Danh sách lịch sử phiên làm việc',
                'data' => $data,
                'meta' => [
                    'page_index' => intval($params['page_index']),
                    'page_size' => intval($params['page_size']),
                    'records' => $total,
                    'pages' => ceil($total / $params['page_size'])
                ]
            ]);
        }

        return response()->json([
            'code' => 404,
            'error' => 'Không tìm thấy thông tin',
            'data' => null
        ]);
    }

    public function actionListing()
    {

    }
}
