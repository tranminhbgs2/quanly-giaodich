<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Position\ChangeStatusRequest;
use App\Http\Requests\Position\DeleteRequest;
use App\Http\Requests\Position\GetDetailRequest;
use App\Http\Requests\Position\ListingRequest;
use App\Http\Requests\Position\StoreRequest;
use App\Http\Requests\Position\UpdateRequest;
use App\Repositories\Category\DepartmentRepo;
use App\Repositories\Position\PositionRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    protected $Position_repo;
    protected $Department_repo;

    public function __construct(PositionRepo $PositionRepo, DepartmentRepo $DepartmentRepo)
    {
        $this->Position_repo = $PositionRepo;
        $this->Department_repo = $DepartmentRepo;
    }

    /**
     * API lấy ds phòng ban
     * URL: {{url}}/api/v1/Positions
     *
     * @param ListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(ListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->Position_repo->listing($params, false);
        $total = $this->Position_repo->listing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách hành động',
            'data' => [
                "total_elements" => $total,
                "total_page" => ceil($total / $params['page_size']),
                "page_no" => intval($params['page_index']),
                "page_size" => intval($params['page_size']),
                "data" => $data
            ],
        ]);
    }

    /**
     * API thêm mới Nhóm quyền
     * URL: {{url}}/api/v1/Positions/store
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params['name'] = request('name', null);
        $params['code'] = request('code', null);
        $params['url'] = request('url', null);
        $params['is_default'] = request('is_default', false);
        $params['created_by'] = Auth::user()->id;
        $params['description'] = request('description', null);
        $params['function_id'] = request('function_id', null);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
        $func = $this->Department_repo->getById(['id' => $params['function_id']]);

        if (!$func) {
            return response()->json([
                'code' => 422,
                'error' => 'Nhóm quyền không tồn tại',
                'data' => $func
            ]);
        }
        $resutl = $this->Position_repo->store($params);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Thêm mới hành động thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Thêm mới hành động không thành công',
            'data' => null
        ]);
    }

    /**
     * API cập nhật thông tin KH theo id
     * URL: {{url}}/api/v1/transaction/update/id
     *
     * @param UpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request)
    {
        $params['id'] = request('id', null);
        if ($params['id']) {

            $params['name'] = request('name', null);
            $params['code'] = request('code', null);
            $params['url'] = request('url', null);
            $params['is_default'] = request('is_default', false);
            $params['created_by'] = Auth::user()->id;
            $params['description'] = request('description', null);
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
            $params['function_id'] = request('function_id', null);
            $func = $this->Department_repo->getById(['id' => $params['function_id']]);
            if (!$func) {
                return response()->json([
                    'code' => 422,
                    'error' => 'Nhóm quyền không tồn tại',
                    'data' => $func
                ]);
            }
            $resutl = $this->Position_repo->update($params, $params['id']);

            if ($resutl) {
                return response()->json([
                    'code' => 200,
                    'error' => 'Cập nhật thông tin thành công',
                    'data' => null
                ]);
            }

            return response()->json([
                'code' => 400,
                'error' => 'Cập nhật thông tin không thành công',
                'data' => null
            ]);
        } else {
            return response()->json([
                'code' => 422,
                'error' => 'ID không hợp lệ',
                'data' => null
            ]);
        }
    }

    public function getAll()
    {
        $data = $this->Position_repo->getAll();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách hành động',
            'data' => $data
        ]);
    }

    public function getAllByFunc($func_id)
    {
        if($func_id > 0) {
            $data = $this->Position_repo->getAllByFunc($func_id);
            return response()->json([
                'code' => 200,
                'error' => 'Danh sách hành động theo nhóm quyền',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'code' => 422,
                'error' => 'ID Function không hợp lệ',
                'data' => null
            ]);

        }
    }
    /**
     * API xóa thông tin khách hàng, xóa trạng thái, ko xóa vật lý
     * URL: {{url}}/api/v1/transaction/delete/1202112817000308
     *
     * @param DeleteRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            if ($id == $params['id']) {
                $data = $this->Position_repo->delete($params);
            } else {
                return response()->json([
                    'code' => 422,
                    'error' => 'ID không hợp lệ',
                    'data' => null
                ]);
            }
        } else {
            $data = [
                'code' => 422,
                'error' => 'Truyền thiếu ID',
                'data' => null
            ];
        }

        return response()->json($data);
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        $params['id'] = request('id', null);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);

        $resutl = $this->Position_repo->changeStatus($params['status'], $params['id']);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Cập nhật trạng thái thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Cập nhật trạng thái không thành công',
            'data' => null
        ]);
    }

    /**
     * API lấy thông tin chi tiết khách hàng
     * URL: {{url}}/api/v1/transaction/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(GetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->Position_repo->getDetail($params);
        } else {
            $data = [
                'code' => 422,
                'error' => 'Truyền thiếu ID',
                'data' => null
            ];
        }

        return response()->json($data);
    }
}
