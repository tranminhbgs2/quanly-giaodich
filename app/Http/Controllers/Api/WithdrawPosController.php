<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawPos\ChangeStatusRequest;
use App\Http\Requests\WithdrawPos\DeleteRequest;
use App\Http\Requests\WithdrawPos\GetDetailRequest;
use App\Http\Requests\WithdrawPos\ListingRequest;
use App\Http\Requests\WithdrawPos\StoreRequest;
use App\Http\Requests\WithdrawPos\UpdateRequest;
use App\Repositories\WithdrawPos\WithdrawPosRepo;

class WithdrawPosController extends Controller
{
    protected $cate_repo;

    public function __construct(WithdrawPosRepo $cateRepo)
    {
        $this->cate_repo = $cateRepo;
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
        $params['pos_id'] = request('pos_id', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['hkd_id'] = request('hkd_id', 0); // ngân hàng
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = auth()->user()->account_type;

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $data = $this->cate_repo->getListing($params, false);
        $total = $this->cate_repo->getListing($params, true);
        $export = $this->cate_repo->getTotal($params); //số liệu báo cáo
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Rút tiền máy pos',
            'data' => [
                "total_elements" => $total,
                "total_page" => ceil($total / $params['page_size']),
                "page_no" => intval($params['page_index']),
                "page_size" => intval($params['page_size']),
                "data" => $data,
                'total' => $export
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
            $data = $this->cate_repo->getDetail($params);
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
        $params['hkd_id'] = request('hkd_id', 0); // ngân hàng
        $params['account_bank_id'] = request('account_bank_id', 0); // hình thức
        $params['time_withdraw'] = request('time_withdraw', null); // máy pos
        $params['pos_id'] = request('pos_id', 0); // phí
        $params['price_withdraw'] = request('price_withdraw', 0); // tiền rút pos. Nếu nhập âm thì tiền đó là tiền nạp vào HKD
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['created_by'] = auth()->user()->id; // người tạo
        $params['time_withdraw'] = str_replace('/', '-', $params['time_withdraw']);

        $resutl = $this->cate_repo->store($params);

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

            $params['hkd_id'] = request('hkd_id', 0); // ngân hàng
            $params['account_bank_id'] = request('account_bank_id', 0); // hình thức
            $params['time_withdraw'] = request('time_withdraw', null); // máy pos
            $params['pos_id'] = request('pos_id', 0); // phí
            $params['price_withdraw'] = request('price_withdraw', 0); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
            $params['time_withdraw'] = str_replace('/', '-', $params['time_withdraw']);

            $resutl = $this->cate_repo->update($params, $params['id']);

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
                $data = $this->cate_repo->delete($params);
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

        $resutl = $this->cate_repo->changeStatus($params['status'], $params['id']);

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
        $hkd_id = request('hkd_id', null);
        $data = $this->cate_repo->getAll($hkd_id);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách rút tiền',
            'data' => $data
        ]);
    }
}
