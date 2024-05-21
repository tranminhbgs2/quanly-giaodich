<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\DeleteRequest;
use App\Http\Requests\Category\GetDetailRequest;
use App\Http\Requests\Category\ListingRequest;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Repositories\User\UserRepo;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $user_repo;

    public function __construct(UserRepo $cateRepo)
    {
        $this->user_repo = $cateRepo;
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/transaction
     *
     * @param ListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(ListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = request('account_type', Constants::ACCOUNT_TYPE_STAFF);

        $data = $this->user_repo->getListing($params, false);
        $total = $this->user_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách User',
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
     * API lấy thông tin chi tiết khách hàng
     * URL: {{url}}/api/v1/transaction/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfo()
    {
        $id = Auth::user()->id;
        if ($id) {
            $user = $this->user_repo->getById($id);
            $res = [
                'id' => $user->id,
                'account_type' => $user->account_type,
                'username' => $user->username,
                'fullname' => $user->fullname,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'phone' => $user->phone,
                'birthday' => $user->birthday,
                'display_name' => $user->display_name,
                'address' => $user->address
            ];

            return response()->json([
                'code' => 200,
                'error' => 'Thông tin user',
                'data' => $res
            ]);
        } else {
            $data = [
                'code' => 422,
                'error' => 'Vui lòng đăng nhập lại',
                'data' => null
            ];
            return response()->json($data);
        }

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
            $data = $this->user_repo->getDetail($params);
        } else {
            $data = [
                'code' => 422,
                'error' => 'Truyền thiếu ID',
                'data' => null
            ];
        }

        return response()->json($data);
    }

    /**
     * API thêm mới KH từ CMS
     * URL: {{url}}/api/v1/transaction/store
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params['name'] = request('name', null); // ngân hàng
        $params['code'] = strtoupper(request('code', null)); // hình thức
        $params['note'] = request('note', 0); // máy pos
        $params['fee'] = floatval(request('fee', 0)); // phí
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái


        $resutl = $this->user_repo->store($params);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Thêm mới thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Thêm mới không thành công',
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

            $params['name'] = request('name', null); // ngân hàng
            $params['code'] = strtoupper(request('code', null)); // hình thức
            $params['note'] = request('note', 0); // máy pos
            $params['fee'] = floatval(request('fee', 0)); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);

            $resutl = $this->user_repo->update($params, $params['id']);

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
                $data = $this->user_repo->delete($params);
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

    public function changeStatus()
    {
        $params['id'] = request('id', null);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);

        $resutl = $this->user_repo->changeStatus($params['status'], $params['id']);

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
}
