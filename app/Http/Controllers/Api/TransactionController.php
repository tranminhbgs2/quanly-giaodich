<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\ChangeStatusRequest;
use App\Http\Requests\Transaction\DeleteRequest;
use App\Http\Requests\Transaction\GetDetailRequest;
use App\Http\Requests\Transaction\ListingRequest;
use App\Http\Requests\Transaction\StoreRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Repositories\MoneyComesBack\MoneyComesBackRepo;
use App\Repositories\Pos\PosRepo;
use App\Repositories\Transaction\TransactionRepo;
use App\Repositories\Upload\UploadRepo;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $tran_repo;
    protected $money_comes_back_repo;
    protected $pos_repo;

    public function __construct(TransactionRepo $tranRepo, MoneyComesBackRepo $moneyComesBackRepo, PosRepo $posRepo)
    {
        $this->tran_repo = $tranRepo;
        $this->money_comes_back_repo = $moneyComesBackRepo;
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
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['pos_id'] = request('pos_id', 0);
        $params['category_id'] = request('category_id', 0);
        $params['lo_number'] = request('lo_number', 0);
        $params['created_by'] = auth()->user()->id;
        $params['account_type'] = request('account_type', Constants::ACCOUNT_TYPE_STAFF);

        $data = $this->tran_repo->getListing($params, false);
        $total = $this->tran_repo->getListing($params, true);
        $export = $this->tran_repo->getTotal($params); //số liệu báo cáo
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Giao dịch Khách lẻ',
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
        $params['category_id'] = request('category_id', 0);
        $params['lo_number'] = request('lo_number', 0);
        $params['created_by'] = auth()->user()->id;
        $params['account_type'] = request('account_type', Constants::ACCOUNT_TYPE_STAFF);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);

        $data = $this->tran_repo->getListingCashBack($params, false);
        $total = $this->tran_repo->getListingCashBack($params, true);
        $export = $this->tran_repo->getTotalCashBack($params); //số liệu báo cáo
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
            $data = $this->tran_repo->getDetail($params);
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
        $params['bank_card'] = request('bank_card', null); // ngân hàng
        $params['method'] = request('method', null); // hình thức
        $params['category_id'] = request('category_id', 0); // danh mục
        $params['pos_id'] = request('pos_id', 0); // máy pos
        $params['fee'] = floatval(request('fee', 0)); // phí
        $params['original_fee'] = floatval(request('original_fee', 0)); // phí gốc
        $params['time_payment'] = request('time_payment', null); // thời gian thanh toán
        $params['customer_name'] = request('customer_name', null); // tên khách hàng
        $params['account_type'] = request('account_type', null); // loại tài khoản
        $params['price_nop'] = floatval(request('price_nop', 0)); // số tiền nộp
        $params['price_rut'] = floatval(request('price_rut', 0)); // số tiền rút
        $params['price_transfer'] = floatval(request('price_transfer', 0)); // số tiền chuyển
        $params['price_repair'] = floatval(request('price_repair', 0)); // số tiền bù
        $params['price_fee'] = floatval(request('price_fee', 0)); // số tiền bù
        $params['created_by'] = auth()->user()->id; // người tạo
        $params['status'] = Constants::USER_STATUS_ACTIVE; // trạng thái
        $params['customer_id'] = request('customer_id', 0); // id khách hàng
        $params['lo_number'] = request('lo_number', 0); // số lô

        $params['time_payment'] = str_replace('/', '-', $params['time_payment']);

        $params['price_fee'] = ($params['fee'] * $params['price_rut']) / 100 + $params['price_repair']; // số tiền phí
        $params['profit'] = ($params['fee'] - $params['original_fee']) * $params['price_rut'] / 100; // lợi nhuận

        $pos = $this->pos_repo->getById($params['pos_id'], false);

        if($pos) {
            $params['fee_cashback'] = $pos->fee_cashback;
        }

        $resutl = $this->tran_repo->store($params);

        if ($resutl) {
            if ($params['lo_number'] > 0) {
                if ($params['time_payment']) {
                    $time_process = date('Y-m-d', strtotime($params['time_payment']));
                } else {
                    $time_process = date('Y-m-d');
                }
                $money_come = $this->money_comes_back_repo->getByLoTime(['lo_number' => $params['lo_number'], 'time_process' => $time_process]);
                if ($money_come) {
                    $total_price = $money_come->total_price + $params['price_rut'];
                    $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']);
                    $money_comes_back = [
                        'pos_id' => $params['pos_id'],
                        'lo_number' => $params['lo_number'],
                        'time_end' => $params['time_payment'],
                        'time_process' => $time_process,
                        'fee' => $params['original_fee'],
                        'total_price' => $total_price,
                        'payment' => $payment,
                        'created_by' => auth()->user()->id,
                        'status' => Constants::USER_STATUS_ACTIVE,
                    ];
                    $this->money_comes_back_repo->update($money_comes_back, $money_come->id);
                } else {
                    $money_comes_back = [
                        'pos_id' => $params['pos_id'],
                        'lo_number' => $params['lo_number'],
                        'time_end' => $params['time_payment'],
                        'time_process' => $time_process,
                        'fee' => $params['original_fee'],
                        'total_price' => $params['price_rut'],
                        'payment' => ($params['price_rut'] - $params['price_fee']),
                        'created_by' => auth()->user()->id,
                        'status' => Constants::USER_STATUS_ACTIVE,
                    ];
                    $this->money_comes_back_repo->store($money_comes_back);
                }
            }
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
            $tran = $this->tran_repo->getById($params['id'], false);

            $params['bank_card'] = request('bank_card', null);
            $params['method'] = request('method', null);
            $params['category_id'] = request('category_id', 0);
            $params['pos_id'] = request('pos_id', 0);
            $params['fee'] = floatval(request('fee', 0));
            $params['original_fee'] = floatval(request('original_fee', 0));
            $params['time_payment'] = request('time_payment', null);
            $params['customer_name'] = request('customer_name', null);
            $params['account_type'] = request('account_type', null);
            $params['price_nop'] = floatval(request('price_nop', 0));
            $params['price_rut'] = floatval(request('price_rut', 0));
            $params['price_transfer'] = floatval(request('price_transfer', 0));
            $params['price_repair'] = floatval(request('price_repair', 0));
            $params['created_by'] = auth()->user()->id;
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
            $params['customer_id'] = request('customer_id', 0);
            $params['lo_number'] = request('lo_number', 0);
            $params['price_fee'] = ($params['fee'] * $params['price_rut']) / 100 + $params['price_repair'];
            $params['profit'] = ($params['fee'] - $params['original_fee']) * $params['price_rut'] / 100;


            $params['time_payment'] = str_replace('/', '-', $params['time_payment']);

            $pos = $this->pos_repo->getById($params['pos_id'], false);

            if($pos) {
                $params['fee_cashback'] = $pos->fee_cashback;
            }
            
            $resutl = $this->tran_repo->update($params);

            if ($resutl) {
                if ($params['lo_number'] > 0) {
                    if ($params['time_payment']) {
                        $time_process = date('Y-m-d', strtotime($params['time_payment']));
                    } else {
                        $time_process = date('Y-m-d');
                    }
                    $money_come = $this->money_comes_back_repo->getByLoTime(['lo_number' => $params['lo_number'], 'time_process' => $time_process]);
                    if ($money_come) {
                        if ($tran->lo_number > 0) {
                            // Do đã công 1 lần r nên phải trừ đi lần cũ rồi cộng lại
                            $total_price = $money_come->total_price + $params['price_rut'] - $tran->price_rut;
                            $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']) - ($tran->price_rut - $tran->price_fee);
                        } else {
                            // Chưa có lần nào cộng
                            $total_price = $money_come->total_price + $params['price_rut'];
                            $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']);
                        }
                        $money_comes_back = [
                            'pos_id' => $params['pos_id'],
                            'lo_number' => $params['lo_number'],
                            'time_end' => $params['time_payment'],
                            'time_process' => $time_process,
                            'fee' => $params['original_fee'],
                            'total_price' => $total_price,
                            'payment' => $payment,
                            'created_by' => auth()->user()->id,
                            'status' => $money_come->status,
                        ];
                        $this->money_comes_back_repo->update($money_comes_back, $money_come->id);
                    } else {
                        $money_comes_back = [
                            'pos_id' => $params['pos_id'],
                            'lo_number' => $params['lo_number'],
                            'time_end' => $params['time_payment'],
                            'time_process' => $time_process,
                            'fee' => $params['original_fee'],
                            'total_price' => $params['price_rut'],
                            'payment' => ($params['price_rut'] - $params['price_fee']),
                            'created_by' => auth()->user()->id,
                            'status' => Constants::USER_STATUS_ACTIVE,
                        ];
                        $this->money_comes_back_repo->store($money_comes_back);
                    }
                }
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
                $tran = $this->tran_repo->getById($params['id'], false);
                if ($tran->status != Constants::USER_STATUS_DELETED && $tran->lo_number > 0) {
                    $time_process = date('Y-m-d', strtotime($tran->time_payment));
                    $money_come = $this->money_comes_back_repo->getByLoTime(['lo_number' => $params['lo_number'], 'time_process' => $time_process]);
                    if ($money_come) {
                        if ($tran->lo_number > 0) {
                            // Do đã công 1 lần r nên phải trừ đi lần cũ rồi cộng lại
                            $total_price = $money_come->total_price + $params['price_rut'] - $tran->price_rut;
                            $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']) - ($tran->price_rut - $tran->price_fee);
                        } else {
                            // Chưa có lần nào cộng
                            $total_price = $money_come->total_price + $params['price_rut'];
                            $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']);
                        }
                        $money_comes_back = [
                            'pos_id' => $tran->pos_id,
                            'lo_number' => $tran->lo_number,
                            'time_end' => $tran->time_payment,
                            'time_process' => $time_process,
                            'fee' => $$tran->original_fee,
                            'total_price' => $total_price,
                            'payment' => $payment,
                            'created_by' => auth()->user()->id,
                            'status' => $money_come->status,
                        ];
                        $this->money_comes_back_repo->update($money_comes_back, $money_come->id);
                    }
                }
                $data = $this->tran_repo->delete($params);
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

        $resutl = $this->tran_repo->changeStatus($params['status'], $params['id']);

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
