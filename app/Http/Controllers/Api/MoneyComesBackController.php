<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoneyComesBack\ChangeStatusRequest;
use App\Http\Requests\MoneyComesBack\DeleteRequest;
use App\Http\Requests\MoneyComesBack\GetDetailRequest;
use App\Http\Requests\MoneyComesBack\ListingRequest;
use App\Http\Requests\MoneyComesBack\StoreRequest;
use App\Http\Requests\MoneyComesBack\UpdateRequest;
use App\Repositories\MoneyComesBack\MoneyComesBackRepo;

class MoneyComesBackController extends Controller
{
    protected $money_repo;

    public function __construct(MoneyComesBackRepo $moneyRepo)
    {
        $this->money_repo = $moneyRepo;
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
        $params['lo_number'] = request('lo_number', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);


        $data = $this->money_repo->getListing($params, false);
        $total = $this->money_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Lô tiền về',
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
    public function getDetail(GetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->money_repo->getDetail($params);
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
        $params['lo_number'] = strtoupper(request('lo_number', null)); // hình thức
        $params['pos_id'] = request('pos_id', 0); // máy pos
        $params['fee'] = floatval(request('fee', 0)); // phí
        $params['total_price'] = floatval(request('total_price', 0)); // phí
        $params['payment'] = floatval(request('payment', 0)); // phí
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['created_by'] = auth()->user()->id;
        if (request('time_process')) {
            $params['time_process'] = date('Y-m-d', strtotime(request('time_end')));
        }

        $resutl = $this->money_repo->store($params);

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
            $params['lo_number'] = strtoupper(request('lo_number', null)); // hình thức
            $params['pos_id'] = request('pos_id', 0); // máy pos
            $params['fee'] = floatval(request('fee', 0)); // phí
            $params['total_price'] = floatval(request('total_price', 0)); // phí
            $params['payment'] = floatval(request('payment', 0)); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
            $params['created_by'] = auth()->user()->id;
            if (request('time_process')) {
                $params['time_process'] = date('Y-m-d', strtotime(request('time_end')));
            }

            $resutl = $this->money_repo->update($params, $params['id']);

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
                $data = $this->money_repo->delete($params);
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

        $resutl = $this->money_repo->changeStatus($params['status'], $params['id']);

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
