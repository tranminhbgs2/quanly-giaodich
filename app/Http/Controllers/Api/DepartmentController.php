<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepListingRequest;
use App\Repositories\Category\DepartmentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function listing(DepListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->department_repo->listing($params, false);
        $total = $this->department_repo->listing($params, true);

        return response()->json([
            'code' => 200,
            'message' => 'Danh sách Nhóm quyền',
            'data' => $data,
            'meta' => [
                'page_index' => intval($params['page_index']),
                'page_size' => intval($params['page_size']),
                'records' => $total,
                'pages' => ceil($total / $params['page_size'])
            ]
        ]);
    }

    /**
     * API thêm mới Nhóm quyền
     * URL: {{url}}/api/v1/departments/store
     *
     * @param CusStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CusStoreRequest $request)
    {
        $params['fullname'] = request('fullname', null);
        $params['phone'] = request('phone', null);
        $params['email'] = request('email', null);
        if ($request->hasFile('avatar')) {
            $params['avatar'] = $request->file('avatar');
        } else {
            $params['avatar'] = null;
        }
        $params['username'] = request('username', null);
        $params['password'] = request('password', null);
        $params['password_confirmation'] = request('password_confirmation', null);

        // Xử lý upload
        if ($params['avatar']) {
            $path = $this->upload_repo->processUploadAvatar([
                'scope' => 'UPLOAD_AVATAR',
                'field_name' => strtolower($params['username']),
            ], $request);

            $params['avatar'] = $path;
        }

        $resutl = $this->customer_repo->store($params);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'message' => 'Thêm mới nhân viên thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Thêm mới nhân viên không thành công',
            'data' => null
        ]);
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
                'message' => 'Gán quyền cho nhóm quyền thành công',
                'data' => null
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'message' => 'Đã có lỗi xảy ra',
                'data' => null
            ]);
        }
    }
}
