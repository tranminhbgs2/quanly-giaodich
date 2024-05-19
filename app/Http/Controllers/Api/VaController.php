<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Va\DetailVaRequest;
use App\Http\Requests\Va\VaListingRequest;
use App\Repositories\Va\VaRepo;
use Illuminate\Http\Request;

class VaController extends Controller
{
    protected $vaRepo;

    public function __construct(VaRepo $vaRepo)
    {
        $this->vaRepo = $vaRepo;
    }

    /**
     * API lấy ds thẻ ảo
     * URL: {{url}}/api/v1/banks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listing(VaListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['sscid'] = request('sscid', null);
        $params['bank_id'] = request('bank_id', -1);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->vaRepo->listing($params);
        $total = $this->vaRepo->listing($params, true);

        if ($data) {
            return response()->json([
                'code' => 200,
                'message' => 'Danh sách thẻ ảo',
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
            'message' => 'Không tìm thấy thông tin thẻ ảo',
            'data' => null
        ]);
    }

    /**
     * API lấy thông tin chi tiết 1 thẻ ảo
     * URL: {{url}}/api/v1/vas/detail
     *
     * @param DetailVaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(DetailVaRequest  $request)
    {
        $params['id'] = request('id', null);

        $data = $this->vaRepo->detail($params);

        if ($data) {
            return response()->json([
                'code' => 200,
                'message' => 'Thông tin thẻ ảo',
                'data' => $data
            ]);
        }

        return response()->json([
            'code' => 404,
            'message' => 'Không tìm thấy thông tin thẻ ảo',
            'data' => null
        ]);
    }

    public function store()
    {

    }

    public function changeStatus()
    {

    }

    public function cancel()
    {

    }

}
