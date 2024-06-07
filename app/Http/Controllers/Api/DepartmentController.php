<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\ChangeStatusRequest;
use App\Http\Requests\Department\DepDeleteRequest;
use App\Http\Requests\Department\DepGetDetailRequest;
use App\Http\Requests\Department\DepListingRequest;
use App\Http\Requests\Department\DepStoreRequest;
use App\Http\Requests\Department\DepUpdateRequest;
use App\Repositories\Category\DepartmentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\TextUI\XmlConfiguration\Constant;

class DepartmentController extends Controller
{
    protected $department_repo;

    public function __construct(DepartmentRepo $departmentRepo)
    {
        $this->department_repo = $departmentRepo;
    }

    /**
     * API lấy ds phòng ban
     * URL: {{url}}/api/v1/departments
     *
     * @param DepListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(DepListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->department_repo->listing($params, false);
        $total = $this->department_repo->listing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Nhóm quyền',
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
     * URL: {{url}}/api/v1/departments/store
     *
     * @param DepStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DepStoreRequest $request)
    {
        $params['name'] = request('name', null);
        $params['code'] = request('code', null);
        $params['url'] = request('url', null);
        $params['is_default'] = request('is_default', false);
        $params['created_by'] = Auth::user()->id;
        $params['description'] = request('description', null);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);

        $resutl = $this->department_repo->store($params);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Thêm mới nhóm quyền thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Thêm mới nhóm quyền không thành công',
            'data' => null
        ]);
    }

    /**
     * API cập nhật thông tin KH theo id
     * URL: {{url}}/api/v1/transaction/update/id
     *
     * @param DepUpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DepUpdateRequest $request)
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

            $resutl = $this->department_repo->update($params, $params['id']);

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

    // Gán nhiều positions cho department
    public function attachPositionsToDepartment(Request $request, $departmentId)
    {
        $positions = $request->input('position_ids');
        $departmentId = $request->input('department_id');

        //Xóa hết quyền đi gán lại
        $this->department_repo->detachAllPositions($departmentId);

        if (is_array($positions)) {
            $this->department_repo->attachPositions($departmentId, $positions);
            return response()->json([
                'code' => 200,
                'error' => 'Gán quyền cho nhóm quyền thành công',
                'data' => null
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'error' => 'Đã có lỗi xảy ra',
                'data' => null
            ]);
        }
    }


    public function getAll()
    {
        $data = $this->department_repo->getAll();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách nhóm quyền',
            'data' => $data
        ]);
    }

    /**
     * API xóa thông tin khách hàng, xóa trạng thái, ko xóa vật lý
     * URL: {{url}}/api/v1/transaction/delete/1202112817000308
     *
     * @param DepDeleteRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DepDeleteRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            if ($id == $params['id']) {
                $data = $this->department_repo->delete($params);
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

        $resutl = $this->department_repo->changeStatus($params['status'], $params['id']);

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
     * @param DepGetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(DepGetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->department_repo->getDetail($params);
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
