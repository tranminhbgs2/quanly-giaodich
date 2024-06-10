<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoneyComesBack\ChangeStatusRequest;
use App\Http\Requests\MoneyComesBack\DeleteRequest;
use App\Http\Requests\MoneyComesBack\GetDetailRequest;
use App\Http\Requests\MoneyComesBack\KetToanLoRequest;
use App\Http\Requests\MoneyComesBack\ListingRequest;
use App\Http\Requests\MoneyComesBack\StoreRequest;
use App\Http\Requests\MoneyComesBack\UpdateRequest;
use App\Models\Pos;
use App\Repositories\MoneyComesBack\MoneyComesBackRepo;
use App\Repositories\Pos\PosRepo;
use App\Repositories\Transfer\TransferRepo;

class MoneyComesBackController extends Controller
{
    protected $money_repo;
    protected $pos_repo;
    protected $transfer_repo;

    public function __construct(MoneyComesBackRepo $moneyRepo, PosRepo $posRepo, TransferRepo $transferRepo)
    {
        $this->money_repo = $moneyRepo;
        $this->pos_repo = $posRepo;
        $this->transfer_repo = $transferRepo;
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
        $params['account_type'] = auth()->user()->account_type;
        $params['lo_number'] = request('lo_number', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);
        $params['hkd_id'] = request('hkd_id', 0);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);


        $data = $this->money_repo->getListing($params, false);
        $total = $this->money_repo->getListing($params, true);
        $total_pay = $this->money_repo->getTotal($params);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Lô tiền về',
            'data' => [
                "total_payment" => $total_pay,
                "total_elements" => $total,
                "total_page" => ceil($total / $params['page_size']),
                "page_no" => intval($params['page_index']),
                "page_size" => intval($params['page_size']),
                "data" => $data
            ],
        ]);
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/transaction
     *
     * @param ListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListingAgency(ListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = auth()->user()->account_type;
        $params['lo_number'] = request('lo_number', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);
        $params['agent_id'] = request('agent_id', 0);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);

        $params_transfer['agent_id'] = $params['agent_id'];
        // $params_transfer['agent_date_from'] = request('agent_date_from', null);
        // $params_transfer['agent_date_to'] = request('agent_date_to', null);

        $params_transfer['date_from'] = str_replace('/', '-', $params['date_from']);
        $params_transfer['date_to'] = str_replace('/', '-', $params['date_to']);

        $data = $this->money_repo->getListingAgent($params, false, true);
        $total = $this->money_repo->getListingAgent($params, true, true);
        $total_transfer = $this->transfer_repo->getTotalAgent($params_transfer);
        $total_payment = $this->money_repo->getTotalAgent($params_transfer);
        if (count($total_transfer) > 0) {
            $total_payment['total_transfer'] = $total_transfer['total_transfer'];
            $total_payment['total_cash'] = $total_payment['total_payment_agent'] - $total_payment['total_transfer'];
        }
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Lô tiền về',
            'data' => [
                "total_payment" => $total_payment,
                "total_elements" => $total,
                "total_page" => ceil($total / $params['page_size']),
                "page_no" => intval($params['page_index']),
                "page_size" => intval($params['page_size']),
                "data" => $data
            ],
        ]);
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/transaction
     *
     * @param ListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListingCashBack(ListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);
        $params['lo_number'] = request('lo_number', 0);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);

        $data = $this->money_repo->getListingCashBack($params, false);
        $total = $this->money_repo->getListingCashBack($params, true);
        $export = $this->money_repo->getTotalCashBack($params); //số liệu báo cáo
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Giao dịch hoàn tiền',
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
        $params['lo_number'] = strtoupper(request('lo_number', null)); // hình thức
        $params['pos_id'] = request('pos_id', 0); // máy pos
        $params['fee'] = floatval(request('fee', 0)); // phí
        $params['total_price'] = floatval(request('total_price', 0)); // tổng tiền xử lý
        $params['payment'] = floatval(request('payment', 0)); // thành tiền
        $params['status'] = request('status', Constants::USER_STATUS_LOCKED); // trạng thái
        $params['created_by'] = auth()->user()->id;
        $params['balance'] = floatval(request('balance', 0)); // tiền  tổng
        $params['agent_id'] = request('agent_id', 0); // id đại lý
        $params['time_end'] = request('time_end', null); // id đại lý
        if ($params['time_end']) {
            $params['time_end'] = str_replace('/', '-', $params['time_end']);
            $params['time_process'] = date('Y-m-d', strtotime($params['time_end']));
        }

        if ($params['agent_id'] > 0) {
            $pos = $this->pos_repo->getById($params['pos_id']);
            if ($pos) {
                $params['hkd_id'] = $pos->hkd_id;
                $activeAgents = $pos->activeByAgentsDate($params['agent_id']);
                if ($activeAgents) {
                    $params['fee'] = $pos->total_fee;
                    $params['fee_agent'] = $activeAgents->fee;
                    $params['payment_agent'] = $params['total_price'] - $params['fee_agent']*$params['total_price']/100;
                    $params['payment'] = $params['total_price'] - $params['fee']*$params['total_price']/100;
                } else {
                    return response()->json([
                        'code' => 400,
                        'error' => 'Máy POS không thuộc đại lý này',
                        'data' => $activeAgents
                    ]);
                }
            } else {
                return response()->json([
                    'code' => 400,
                    'error' => 'Máy POS không thuộc đại lý này',
                    'data' => null
                ]);
            }
        }
        $params['time_end'] = null;
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

            $params['lo_number'] = strtoupper(request('lo_number', null)); // hình thức
            $params['pos_id'] = request('pos_id', 0); // máy pos
            $params['fee'] = floatval(request('fee', 0)); // phí
            $params['total_price'] = floatval(request('total_price', 0)); // phí
            $params['payment'] = floatval(request('payment', 0)); // phí
            $params['status'] = request('status', Constants::USER_STATUS_LOCKED); // trạng thái
            $params['created_by'] = auth()->user()->id;
            $params['balance'] = floatval(request('balance', 0)); // tiền  tổng
            $params['agent_id'] = request('agent_id', 0); // id đại lý
            $params['time_end'] = request('time_end', null); // id đại lý
            if ($params['time_end']) {
                $params['time_end'] = str_replace('/', '-', $params['time_end']);
                $params['time_process'] = date('Y-m-d', strtotime($params['time_end']));
            }
            if ($params['agent_id'] > 0) {
                $pos = $this->pos_repo->getById($params['pos_id']);
                if ($pos) {
                    $params['hkd_id'] = $pos->hkd_id;
                    $activeAgents = $pos->activeByAgentsDate($params['agent_id']);
                    if ($activeAgents) {
                        $params['fee'] = $pos->total_fee;
                        $params['fee_agent'] = $activeAgents->fee;
                        $params['payment_agent'] = $params['total_price'] - $params['fee_agent']*$params['total_price']/100;
                        $params['payment'] = $params['total_price'] - $params['fee']*$params['total_price']/100;
                    } else {
                        return response()->json([
                            'code' => 400,
                            'error' => 'Máy POS không thuộc đại lý này',
                            'data' => $activeAgents
                        ]);
                    }
                } else {
                    return response()->json([
                        'code' => 400,
                        'error' => 'Máy POS không thuộc đại lý này',
                        'data' => null
                    ]);
                }
            }
        $params['time_end'] = null;
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

    public function ketToanLo(KetToanLoRequest $request)
    {
        $params['id'] = request('id', null);
        $params['time_end'] = request('time_end', null); // id đại lý
        $params['time_end'] = str_replace('/', '-', $params['time_end']);
        if (request('time_end')) {
            $params['time_process'] = date('Y-m-d', strtotime(request('time_end')));
        }
        $resutl = $this->money_repo->ketToanLo($params['id'], $params['time_process'], $params['time_end']);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Kết toán lô thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Kết toán lô không thành công',
            'data' => null
        ]);
    }
}
