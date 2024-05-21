<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\HoKinhDoanh\DeleteRequest;
use App\Http\Requests\HoKinhDoanh\GetDetailRequest;
use App\Http\Requests\HoKinhDoanh\ListingRequest;
use App\Http\Requests\HoKinhDoanh\StoreRequest;
use App\Http\Requests\HoKinhDoanh\UpdateRequest;
use App\Repositories\HoKinhDoanh\HoKinhDoanhRepo;

class HoKinhDoanhController extends Controller
{
    protected $hkd_repo;

    public function __construct(HoKinhDoanhRepo $hkdRepo)
    {
        $this->hkd_repo = $hkdRepo;
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/ho-kinh-doanh
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

        $data = $this->hkd_repo->getListing($params, false);
        $total = $this->hkd_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Hộ kinh doanh',
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
     * URL: {{url}}/api/v1/ho-kinh-doanh/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(GetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->hkd_repo->getDetail($params);
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
     * URL: {{url}}/api/v1/ho-kinh-doanh/store
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params['name'] = request('name', null); // ngân hàng
        $params['surrogate'] = strtoupper(request('surrogate', null)); // hình thức
        $params['phone'] = request('phone', 0); // máy pos
        $params['address'] = request('address', null); // phí
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái


        $resutl = $this->hkd_repo->store($params);

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
     * API cập nhật thông tin HKD
     * URL: {{url}}/api/v1/ho-kinh-doanh/update/id
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
            $params['surrogate'] = strtoupper(request('surrogate', null)); // hình thức
            $params['phone'] = request('phone', 0); // máy pos
            $params['address'] = request('address', null); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái

            $resutl = $this->hkd_repo->update($params, $params['id']);

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
     * URL: {{url}}/api/v1/ho-kinh-doanh/delete/1202112817000308
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
                $data = $this->hkd_repo->delete($params);
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
}
