<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\AssignAgentRequest;
use App\Http\Requests\Pos\DeleteRequest;
use App\Http\Requests\Pos\GetDetailRequest;
use App\Http\Requests\Pos\ListingRequest;
use App\Http\Requests\Pos\StoreRequest;
use App\Http\Requests\Pos\UpdateRequest;
use App\Repositories\Pos\PosRepo;

class PosController extends Controller
{
    protected $pos_repo;
    protected $hkd_repo;

    public function __construct(PosRepo $posRepo)
    {
        $this->pos_repo = $posRepo;
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
        $params['hkd_id'] = request('hkd_id', 0);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = request('account_type', Constants::ACCOUNT_TYPE_STAFF);
        $params['created_by'] = auth()->user()->id; // người tạo

        $data = $this->pos_repo->getListing($params, false);
        $total = $this->pos_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Máy Pos',
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
     * URL: {{url}}/api/v1/pos/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(GetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->pos_repo->getDetail($params);
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
        $params['bank_code'] = request('bank_code', 0); // máy pos
        $params['fee'] = floatval(request('fee', 0)); // phí
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['method'] = request('method', 0); // phương thức
        $params['hkd_id'] = request('hkd_id', 0); // hkd id
        $params['fee_cashback'] = floatval(request('fee_cashback', 0)); // phí cashback
        $params['total_fee'] = floatval(request('total_fee', 0)); // tổng phí
        $params['price_pos'] = floatval(request('price_pos', 0)); // tiền tồn pos
        $params['created_by'] = auth()->user()->id; // người tạo
        $params['updated_by'] = auth()->user()->id; // người cập nhật


        $resutl = $this->pos_repo->store($params);

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
            $params['bank_code'] = request('bank_code', 0); // máy pos
            $params['fee'] = floatval(request('fee', 0)); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
            $params['method'] = request('method', 0); // phương thức
            $params['hkd_id'] = request('hkd_id', 0); // hkd id
            $params['fee_cashback'] = floatval(request('fee_cashback', 0)); // phí cashback
            $params['total_fee'] = floatval(request('total_fee', 0)); // tổng phí
            $params['price_pos'] = floatval(request('price_pos', 0)); // tiền tồn pos
            $params['updated_by'] = auth()->user()->id; // người cập nhật

            $resutl = $this->pos_repo->update($params, $params['id']);

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
                $data = $this->pos_repo->delete($params);
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


    /**
     * API Gán POS cho đại lý
     * URL: {{url}}/api/v1/pos/assign-pos
     *
     * @param AssignAgentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPosToAgent(AssignAgentRequest $request)
    {
        $params['pos_id'] = request('pos_id', 0);
        $params['agent_id'] = request('pos_id', 0);
        $params['fee'] = request('fee', 0);
        if ($params['pos_id']) {

            $params['created_by'] = auth()->user()->id; // người cập nhật

            $resutl = $this->pos_repo->assignPosToAgent($params);

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
}
