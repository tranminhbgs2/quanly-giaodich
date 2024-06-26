<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\HoKinhDoanh\DeleteRequest;
use App\Http\Requests\HoKinhDoanh\GetDetailRequest;
use App\Http\Requests\HoKinhDoanh\ListingRequest;
use App\Http\Requests\HoKinhDoanh\StoreRequest;
use App\Http\Requests\HoKinhDoanh\UpdateRequest;
use App\Http\Requests\HoKinhDoanh\ChangeStatusRequest;
use App\Repositories\HoKinhDoanh\HoKinhDoanhRepo;
use App\Repositories\MoneyComesBack\MoneyComesBackRepo;
use App\Repositories\WithdrawPos\WithdrawPosRepo;

class HoKinhDoanhController extends Controller
{
    protected $hkd_repo;
    protected $withdrawPosRepo;
    protected $money_repo;
    public function __construct(HoKinhDoanhRepo $hkdRepo, WithdrawPosRepo $withdrawPosRepo, MoneyComesBackRepo $money_repo)
    {
        $this->hkd_repo = $hkdRepo;
        $this->withdrawPosRepo = $withdrawPosRepo;
        $this->money_repo = $money_repo;
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
        $params['account_type'] = auth()->user()->account_type;

        $data = $this->hkd_repo->getListing($params, false);
        foreach ($data as $key => $value) {
            // $params_transfer['date_from'] = $params['date_from'];
            // $params_transfer['date_to'] = $params['date_to'];
            $params_transfer['hkd_id'] = $value['id'];
            $total_withdraw_fill = $this->withdrawPosRepo->getTotalByHkd($value['id'], $params_transfer); // toàn bộ GD rút tiền pos

            $params_transfer['is_all'] = true;
            $params_transfer['is_ket_toan'] = true;
            $total_money_ket_toan = $this->money_repo->getTotalPriceByHkd($value['id'], $params_transfer); // các GD đã kết toán

            $data[$key]['total_cash_ket_toan'] = (int)$total_money_ket_toan - (int)$total_withdraw_fill;
        }
        $total = $this->hkd_repo->getListing($params, true);
        $total_balance = $this->hkd_repo->getTotal($params);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Hộ kinh doanh',
            'data' => [
                "total_elements" => $total,
                "total_page" => ceil($total / $params['page_size']),
                "page_no" => intval($params['page_index']),
                "page_size" => intval($params['page_size']),
                "data" => $data,
                "total" => [
                    'total_balance' => $total_balance['balance'] + $total_balance['amount_old']
                ]
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
        $params['amount_old'] = request('amount_old', 0); // máy pos
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
            $params['amount_old'] = request('amount_old', 0); // máy pos
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

    public function changeStatus(ChangeStatusRequest $request) {
        $params['id'] = request('id', null);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);

        $resutl = $this->hkd_repo->changeStatus($params['status'], $params['id']);

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

    public function getAll()
    {
        $data = $this->hkd_repo->getAll();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách hộ kinh doanh',
            'data' => $data
        ]);
    }

    public function syncBalance()
    {
        $money_repo = new MoneyComesBackRepo();
        $withdraw_repo = new WithdrawPosRepo();

        $hkds = $this->hkd_repo->getAll();
        $data = [];
        foreach ($hkds as $hkd) {
            $total_money = $money_repo->getTotalPriceByHkd($hkd['id']);
            $total_money_withdraw = $withdraw_repo->getTotalByHkd($hkd['id']);
            $balance = $total_money - $total_money_withdraw;
            $data[] = ['total_money' => $total_money, 'total_money_withdraw' => $total_money_withdraw, 'id' => $hkd['id'], 'balance' => $balance];
            if($balance != $hkd['balance']){
                $this->hkd_repo->updateBalance($balance, $hkd['id'], "SYNC_BALANCE_" . $hkd['id']);
            }
        }
        return response()->json([
            'code' => 200,
            'error' => 'Đồng bộ số dư thành công',
            'data' => $data
        ]);
    }
}
