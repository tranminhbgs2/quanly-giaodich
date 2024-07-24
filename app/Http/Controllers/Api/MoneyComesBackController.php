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
use App\Repositories\Transaction\TransactionRepo;
use App\Repositories\Transfer\TransferRepo;
use App\Repositories\WithdrawPos\WithdrawPosRepo;
use Carbon\Carbon;

class MoneyComesBackController extends Controller
{
    protected $money_repo;
    protected $pos_repo;
    protected $transfer_repo;
    protected $transaction_repo;
    protected $withdrawPosRepo;

    public function __construct(MoneyComesBackRepo $moneyRepo, PosRepo $posRepo, TransferRepo $transferRepo, TransactionRepo $transactionRepo, WithdrawPosRepo $withdrawPosRepo)
    {
        $this->money_repo = $moneyRepo;
        $this->pos_repo = $posRepo;
        $this->transfer_repo = $transferRepo;
        $this->transaction_repo = $transactionRepo;
        $this->withdrawPosRepo = $withdrawPosRepo;
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
        $params_transfer['agent_date_to'] =  $params['date_to'];
        $params_transfer['agent_date_from'] =  $params['date_from'];

        $data = $this->money_repo->getListingAgent($params, false, true);
        $total = $this->money_repo->getListingAgent($params, true, true);
        $total_transfer = $this->transfer_repo->getTotalAgent($params_transfer);

        $params_transfer_from['agent_id'] = $params['agent_id'];
        $params_transfer_from['type'] = "FROM";
        $total_transfer_from = $this->transfer_repo->getTotalAgent($params_transfer_from); // tiền chuyển

        $total_payment = $this->money_repo->getTotalAgent($params);
        if (count($total_transfer) > 0) {
            $total_payment['total_transfer'] = (int)$total_transfer['total_transfer'];
            $total_payment['total_cash'] = $total_payment['total_payment_agent'] - $total_payment['total_transfer'] + $total_transfer_from['total_transfer'];
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
        $params['fee_agent'] = floatval(request('fee_agent', 0)); // phí
        $params['total_price'] = floatval(request('total_price', 0)); // tổng tiền xử lý
        $params['payment'] = floatval(request('payment', 0)); // thành tiền
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['created_by'] = auth()->user()->id;
        $params['balance'] = floatval(request('balance', 0)); // tiền  tổng
        $params['agent_id'] = request('agent_id', 0); // id đại lý
        $params['time_end'] = request('time_end', null); // id đại lý
        if ($params['time_end']) {
            $params['time_end'] = str_replace('/', '-', $params['time_end']);
            $params['time_process'] = date('Y-m-d', strtotime($params['time_end']));
        }
        if ($params['agent_id'] > 0) {
            if ($params['fee_agent'] > 0) {
                $params['payment_agent'] = $params['total_price'] - $params['fee_agent'] * $params['total_price'] / 100;
            } else {
                $params['payment_agent'] = 0;
            }
            $pos = $this->pos_repo->getById($params['pos_id']);
            if ($pos) {
                $params['hkd_id'] = $pos->hkd_id;
                $params['fee'] = $pos->total_fee;
                $params['payment'] = $params['total_price'] - $params['fee'] * $params['total_price'] / 100;
            } else {
                return response()->json([
                    'code' => 400,
                    'error' => 'Không tìm thấy máy POS',
                    'data' => null
                ]);
            }
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

            $params['lo_number'] = strtoupper(request('lo_number', null)); // hình thức
            $params['pos_id'] = request('pos_id', 0); // máy pos
            $params['fee'] = floatval(request('fee', 0)); // phí
            $params['fee_agent'] = floatval(request('fee_agent', 0)); // phí
            $params['total_price'] = floatval(request('total_price', 0)); // phí
            $params['payment'] = floatval(request('payment', 0)); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
            $params['created_by'] = auth()->user()->id;
            $params['balance'] = floatval(request('balance', 0)); // tiền  tổng
            $params['agent_id'] = request('agent_id', 0); // id đại lý
            $params['time_end'] = request('time_end', null); // id đại lý
            if ($params['time_end']) {
                $params['time_end'] = str_replace('/', '-', $params['time_end']);
                $params['time_process'] = date('Y-m-d', strtotime($params['time_end']));
            }
            if ($params['agent_id'] > 0) {
                if ($params['fee_agent'] > 0) {
                    $params['payment_agent'] = $params['total_price'] - $params['fee_agent'] * $params['total_price'] / 100;
                } else {
                    $params['payment_agent'] = 0;
                }
                $pos = $this->pos_repo->getById($params['pos_id']);
                if ($pos) {
                    $params['hkd_id'] = $pos->hkd_id;
                    $params['fee'] = $pos->total_fee;
                    $params['payment'] = $params['total_price'] - $params['fee'] * $params['total_price'] / 100;
                } else {
                    return response()->json([
                        'code' => 400,
                        'error' => 'Không tìm thấy máy POS',
                        'data' => null
                    ]);
                }
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

    public function ketToanLo(KetToanLoRequest $request)
    {
        $params['id'] = request('id', null);
        $params['time_end'] = request('time_end', null); // id đại lý
        if (!empty($params['time_end'])) {
            $params['time_end'] = str_replace('/', '-', $params['time_end']);
            if (request('time_end')) {
                $params['time_process'] = date('Y-m-d', strtotime(request('time_end')));
            }
        } else {
            $money = $this->money_repo->getById($params['id']);
            $params['time_process'] = $money->time_process;
            $params['time_end'] = null;
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

    public function getTopAgency()
    {
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);

        $data = $this->money_repo->getTopAgency($params);
        $total_rut = 0;
        $total_cash = 0;
        foreach ($data as $key => $value) {
            $total_rut += $value['total_price_rut'];
            $total_cash += $value['total_cash'];
        }
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách top đại lý',
            'data' => $data,
            'total' => [
                'total_rut' => $total_rut,
                'total_cash' => $total_cash,

            ],
        ]);
    }
    public function getAllAgency()
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['lo_number'] = request('lo_number', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);
        $params['agent_id'] = request('agent_id', 0);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $params_transfer['agent_date_to'] =  $params['date_to'];
        $params_transfer['agent_date_from'] =  $params['date_from'];
        $params_transfer['agent_id'] = $params['agent_id'];
        $data = $this->money_repo->getListingAllAgent($params);
        $data_agent = $this->transfer_repo->getListAgent($params_transfer);

        // Merge $data and $data_agent
        $mergedData = $this->mergeDataArrays($data, $data_agent);
        $total_transfer = $this->transfer_repo->getTotalAgent($params_transfer);

        $params_transfer_from['agent_id'] = $params['agent_id'];
        $params_transfer_from['type'] = "FROM";
        $total_transfer_from = $this->transfer_repo->getTotalAgent($params_transfer_from); // tiền chuyển

        $total_payment = $this->money_repo->getTotalAgent($params);
        // if (count($total_transfer) > 0) {
        $total_payment['total_transfer'] = (int)$total_transfer['total_transfer'];
        $total_payment['total_transfer_from'] = (int)$total_transfer_from['total_transfer'];
        $total_payment['total_cash'] = $total_payment['total_payment_agent'] - $total_payment['total_transfer'] + $total_transfer_from['total_transfer'];
        // }

        return response()->json([
            'code' => 200,
            'error' => 'Danh sách đại lý',
            'total' => [
                'total_number_doi_ung' => count($data),
                'total_number_transfer' => count($data_agent),
                'total_payment' => $total_payment,
            ],
            'data' => $mergedData,
        ]);
    }

    public function getAllHKd()
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['lo_number'] = request('lo_number', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);
        $params['agent_id'] = request('agent_id', 0);
        $params['hkd_id'] = request('hkd_id', 0);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $data = $this->money_repo->getListingAllHkd($params);
        $data_hkd = $this->withdrawPosRepo->getListByHkd($params);
        $data_new = [];
        $total_payments = 0;
        foreach ($data as $key => $values) {
            $datav = [];
            foreach ($values as $value) {
                $value['payment'] = (int)($value['total_price'] - (int)($value['total_price'] * $value['fee'] / 100));
                $datav[] = $value;
                $total_payments = (int)($total_payments + $value['payment']);
            }
            $data_new[$key] = $datav;
        }
        // Merge $data and $data_agent
        $mergedData = $this->mergeDataArraysHkd($data_new, $data_hkd);
        $params_transfer['date_from'] = $params['date_from'];
        $params_transfer['date_to'] = $params['date_to'];
        $params_transfer['hkd_id'] = $params['hkd_id'];

        $total_withdraw = $this->withdrawPosRepo->getTotalByHkd($params['hkd_id']);
        $total_money = $this->money_repo->getTotalPriceByHkd($params['hkd_id']);
        $total_withdraw_fill = $this->withdrawPosRepo->getTotalByHkd($params['hkd_id'], $params_transfer);
        $params_transfer['is_all'] = true;
        $total_money_ket_toan = $this->money_repo->getTotalPriceByHkd($params['hkd_id'], $params_transfer); // các GD đã kết toán
        $total_payment = $this->money_repo->getTotalHkd($params_transfer);

        $total_payment['total_money'] = (int)$total_money; // tổng tiền thành tiền từ trước đến nay
        $total_payment['total_withdraw_pos'] = (int)$total_withdraw_fill; // tiền rút pos theo lọc ngày
        $total_payment['total_cash'] = (int)$total_money - (int)$total_withdraw; // tiền tồn pos thực tế
        $total_payment['total_cash_ket_toan'] = (int)$total_money_ket_toan - (int)$total_withdraw_fill; // tiền tồn pos thực tế
        $total_payment['total_money_ket_toan'] = (int)$total_money_ket_toan; // tổng tiền thành tiền từ trước đến nay
        $total_payment['total_withdraw_fill'] = (int)$total_withdraw_fill; // tiền rút pos theo lọc ngày
        $total_payment['total_payment'] = (int)$total_payments; // tổng tiền thành tiền tính lại

        return response()->json([
            'code' => 200,
            'error' => 'Danh sách đại lý',
            'total' => [
                'total_payment' => $total_payment,
            ],
            'data' => $mergedData,
            '$total_payment' => $total_payments
        ]);
    }

    public function syncMoneyComesBack()
    {
        $time_process = request('time_process', date('Y-m-d'));
        if (strlen($time_process) == 10) {
            // Nếu không, thêm giờ mặc định là 00:00:00
            $time_process .= ' 00:00:00';
        }

        $data = $this->money_repo->getByTimeProcess($time_process);
        $total = [];
        foreach ($data as $item) {
            $params['id'] = $item['id'];
            // Sử dụng Carbon để thiết lập date_from và date_to
            $params['date_from'] = Carbon::parse($item['time_process'])->startOfDay();
            $params['date_to'] = Carbon::parse($item['time_process'])->endOfDay();
            $params['hkd_id'] = $item['hkd_id'];
            $params['pos_id'] = $item['pos_id'];
            $params['lo_number'] = $item['lo_number'];
            $total_tran = $this->transaction_repo->getPriceLoNumber($params);
            $update = [];
            if ($total_tran) {
                if ($item['total_price'] != $total_tran['price_rut']) {
                    $update['total_price'] = $total_tran['price_rut'];
                    $update['payment'] = $total_tran['price_rut'] - $total_tran['price_fee'];
                }
            }
            $total[] = [$total_tran, $params['lo_number']];
            if (count($update) > 0) {
                $this->money_repo->updateSync($update, $params['id']);
            }
        }
        return response()->json([
            'code' => 200,
            'error' => 'Đồng bộ dữ liệu lô tiền về thành công',
            'total' => $total,
            'data' => $data,
            'time_process' => $time_process
        ]);
    }

    public function syncLoKetToan()
    {
        $time_process = date('Y-m-d');
        $data = $this->money_repo->getByTimeEnd($time_process);
        foreach ($data as $item) {
            $time_end = date('Y-m-d H:i:s');
            $this->money_repo->ketToanLo($item['id'], $time_process, $time_end);
        }
        return response()->json([
            'code' => 200,
            'error' => 'Đồng bộ dữ liệu lô kết toán thành công',
            'data' => null
        ]);
    }

    /**
     * Merge two arrays into one with the length of the longest array
     *
     * @param array $data
     * @param array $data_agent
     * @return array
     */
    private function mergeDataArrays(array $data, array $data_agent)
    {
        // Specify the fields you want to merge from $data_agent
        $fieldsToMerge = ['type_to', 'to_agent_id', 'id', 'price'];

        // Determine the length of the longest array
        $maxLength = max(count($data), count($data_agent));
        $merged = [];

        for ($i = 0; $i < $maxLength; $i++) {
            $mergedItem = [];

            // If the $data array has an item at this index, merge it
            if (isset($data[$i])) {
                $mergedItem['doi_ung'] = $data[$i];
            } else {
                $mergedItem['doi_ung'] = null;
            }

            // If the $data_agent array has an item at this index, merge the specific fields
            if (isset($data_agent[$i])) {
                $arr_agent = [];
                foreach ($fieldsToMerge as $field) {
                    if (isset($data_agent[$i][$field])) {
                        if ($field == 'to_agent_id') {
                            $arr_agent['agent_id'] = $data_agent[$i][$field];
                        } else {
                            $arr_agent[$field] = $data_agent[$i][$field];
                        }
                    }
                }
                $mergedItem['transfer'] = $arr_agent;
            }

            $merged[] = $mergedItem;
        }

        return $merged;
    }

    /**
     * Merge two arrays into one with the length of the longest array
     *
     * @param array $data
     * @param array $data_hkd
     * @return array
     */
    private function mergeDataArraysHkd(array $groupedMoneyComesBack, array $groupedWithdrawPos)
    {

        $mergedResults = [];
        $allDates = array_unique(array_merge(array_keys($groupedMoneyComesBack), array_keys($groupedWithdrawPos)));
        sort($allDates); // Sort the dates to ensure order

        foreach ($allDates as $date) {
            $mergedResults[$date] = [];

            // Get items for this date from MoneyComesBack
            $moneyComesBackItems = $groupedMoneyComesBack[$date] ?? [];

            // Get items for this date from WithdrawPos
            $withdrawPosItems = $groupedWithdrawPos[$date] ?? [];

            // Determine the length of the longest array for this date
            $maxLength = max(count($moneyComesBackItems), count($withdrawPosItems));

            for ($i = 0; $i < $maxLength; $i++) {
                $mergedItem = [];

                // If the $moneyComesBackItems array has an item at this index, merge it
                $mergedItem['money_comes_back'] = $moneyComesBackItems[$i] ?? null;

                // If the $withdrawPosItems array has an item at this index, merge it
                if (isset($withdrawPosItems[$i])) {
                    $mergedItem['withdraw'] = [
                        'id' => $withdrawPosItems[$i]['id'],
                        'price_withdraw' => $withdrawPosItems[$i]['price_withdraw'],
                        'time_withdraw' => $withdrawPosItems[$i]['time_withdraw'],
                        'hkd_id' => $withdrawPosItems[$i]['hkd_id'],
                    ];
                } else {
                    $mergedItem['withdraw'] = null;
                }

                $mergedResults[$date][] = $mergedItem;
            }
        }

        return $this->flattenMergedResults($mergedResults);
    }
    /**
     * Flatten the nested arrays into a single array
     *
     * @param array $mergedResults
     * @return array
     */
    private function flattenMergedResults(array $mergedResults)
    {
        $flattenedResults = [];

        foreach ($mergedResults as $date => $items) {
            foreach ($items as $item) {
                $flattenedResults[] = [
                    'date' => $date,
                    'money_comes_back' => $item['money_comes_back'],
                    'withdraw' => $item['withdraw'],
                ];
            }
        }

        return $flattenedResults;
    }


    public function updateNote()
    {
        $id = request('id', null);
        $note = request('note', null);

        $resutl = $this->money_repo->updateNote($note, $id);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Cập nhật ghi chú thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Cập nhật ghi chú không thành công',
            'data' => null
        ]);
    }

    // Controller method
    public function getProfit()
    {

        $startDate = request('date_from', null);
        $endDate = request('date_to', null);
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $startDate, 'UTC')->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $endDate, 'UTC')->endOfDay();
        $date_from = date('Y-m-d H:i:s', strtotime($startDate));
        $date_to = date('Y-m-d H:i:s', strtotime($endDate));

        // Khởi tạo mảng kết quả với các ngày từ $startDate đến $endDate
        $output = [];
        $totalProfit = 0; // Biến để lưu tổng lợi nhuận
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $output[$date->format('d/m/Y')] = [
                'date' => $date->format('d/m/Y'),
                'profit_trans' => 0,
                'profit_online' => 0,
                'profit_qr' => 0,
                'profit_money' => 0,
                'total_profit' => 0,
            ];
        }

        $transactions = $this->transaction_repo->getProfitTrans($date_from, $date_to, ['DAO_HAN', 'RUT_TIEN_MAT']);
        $transOnline = $this->transaction_repo->getProfitTrans($date_from, $date_to, ['ONLINE']);
        $transQR = $this->transaction_repo->getProfitTrans($date_from, $date_to, ['QR_CODE']);
        $moneyComesBack = $this->money_repo->getProfitsMoney($date_from, $date_to);

        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->date)->format('d/m/Y');
            if (isset($output[$date])) {
                $output[$date]['profit_trans'] = (int)$transaction->profit_trans;
                $output[$date]['total_profit'] += (int)$transaction->profit_trans;
                $totalProfit += (int)$transaction->profit_trans;
            }
        }

        foreach ($transOnline as $tranOnline) {
            $date = Carbon::parse($tranOnline->date)->format('d/m/Y');
            if (isset($output[$date])) {
                $output[$date]['profit_online'] = (int)$tranOnline->profit_trans;
                $output[$date]['total_profit'] += (int)$tranOnline->profit_trans;
                $totalProfit += (int)$tranOnline->profit_trans;
            }
        }

        foreach ($transQR as $tranQR) {
            $date = Carbon::parse($tranQR->date)->format('d/m/Y');
            if (isset($output[$date])) {
                $output[$date]['profit_qr'] = (int)$tranQR->profit_trans;
                $output[$date]['total_profit'] += (int)$tranQR->profit_trans;
                $totalProfit += (int)$tranQR->profit_trans;
            }
        }

        foreach ($moneyComesBack as $money) {
            $date = Carbon::parse($money->date)->format('d/m/Y');
            if (isset($output[$date])) {
                $output[$date]['profit_money'] = (int)$money->profit_money;
                $output[$date]['total_profit'] += (int)$money->profit_money;
                $totalProfit += (int)$money->profit_money;
            }
        }

        return response()->json([
            'code' => 200,
            'error' => 'Danh sách lợi nhuận',
            'data' => array_values($output),
            'total' => $totalProfit, // Tổng lợi nhuận
        ]);
    }
}
