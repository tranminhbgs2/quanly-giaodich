<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\ChangeStatusRequest;
use App\Http\Requests\Transaction\DeleteRequest;
use App\Http\Requests\Transaction\GetDetailRequest;
use App\Http\Requests\Transaction\ListingRequest;
use App\Http\Requests\Transaction\PaymentFeeRequest;
use App\Http\Requests\Transaction\StoreRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Repositories\BankAccount\BankAccountRepo;
use App\Repositories\MoneyComesBack\MoneyComesBackRepo;
use App\Repositories\Pos\PosRepo;
use App\Repositories\Transaction\TransactionRepo;
use App\Repositories\Transfer\TransferRepo;
use App\Repositories\User\UserRepo;

class TransactionController extends Controller
{
    protected $tran_repo;
    protected $money_comes_back_repo;
    protected $pos_repo;
    protected $transfer_repo;
    protected $bankAccountRepo;
    protected $userRepo;

    public function __construct(TransactionRepo $tranRepo, MoneyComesBackRepo $moneyComesBackRepo, PosRepo $posRepo, TransferRepo $transferRepo, BankAccountRepo $bankAccountRepo, UserRepo $userRepo)
    {
        $this->tran_repo = $tranRepo;
        $this->money_comes_back_repo = $moneyComesBackRepo;
        $this->pos_repo = $posRepo;
        $this->transfer_repo = $transferRepo;
        $this->bankAccountRepo = $bankAccountRepo;
        $this->userRepo = $userRepo;
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
        $params['hkd_id'] = request('hkd_id', 0);
        $params['created_by'] = auth()->user()->id;
        $params['account_type'] = auth()->user()->account_type;
        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $params['method'] = request('method', null);
        $params['status_fee'] = request('status_fee', 1);

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
        $params['account_type'] = auth()->user()->account_type;

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
        $params['price_nop'] = floatval(request('price_nop', 0)); // số tiền nộp
        $params['price_rut'] = floatval(request('price_rut', 0)); // số tiền rút
        $params['price_transfer'] = floatval(request('price_transfer', 0)); // số tiền chuyển
        $params['price_repair'] = floatval(request('price_repair', 0)); // số tiền bù
        $params['price_fee'] = floatval(request('price_fee', 0)); // số tiền bù
        $params['created_by'] = auth()->user()->id; // người tạo
        $params['status'] = Constants::USER_STATUS_ACTIVE; // trạng thái
        $params['customer_id'] = request('customer_id', 0); // id khách hàng
        $params['lo_number'] = request('lo_number', 0); // số lô
        $params['note'] = request('note', null); // số lô

        $params['time_payment'] = str_replace('/', '-', $params['time_payment']);

        $pos = $this->pos_repo->getById($params['pos_id'], false);
        $params['hkd_id'] = 0;

        if ($pos) {
            $params['fee_cashback'] = $pos->fee_cashback;
            $params['original_fee'] = $pos->total_fee;
            $params['hkd_id'] = $pos->hkd_id;
        }

        $params['price_fee'] = ($params['fee'] * $params['price_rut']) / 100 + $params['price_repair']; // số tiền phí
        $params['profit'] = ($params['fee'] - $params['original_fee']) * $params['price_rut'] / 100; // lợi nhuận

        if ($params['lo_number'] > 0) {
            if ($params['time_payment']) {
                $time_process = date('Y-m-d', strtotime($params['time_payment']));
            } else {
                $time_process = date('Y-m-d');
            }
            $money_comeb = $this->money_comes_back_repo->getByLoTime(['lo_number' => $params['lo_number'], 'time_process' => $time_process]);
            if ($money_comeb && !empty($money_comeb->time_end)) {
                return response()->json([
                    'code' => 400,
                    'error' => 'Không thể thêm mới giao dịch cho lô đã kết toán',
                    'data' => null
                ]);
            }
        }

        if ($params['method'] == 'ONLINE' || $params['method'] == 'RUT_TIEN_MAT') {
            $params['price_nop'] = 0;
            $params['fee_paid'] = $params['price_fee'];
        } else {
            // $params['price_nop'] = $params['price_rut'];
            $params['fee_paid'] = 0;
            $params['price_transfer'] = 0;
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
                        'hkd_id' => $params['hkd_id'],
                        'lo_number' => $params['lo_number'],
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
                        'hkd_id' => $params['hkd_id'],
                        'lo_number' => $params['lo_number'],
                        'time_process' => $time_process,
                        'fee' => $params['original_fee'],
                        'total_price' => $params['price_rut'],
                        'payment' => ($params['price_rut'] - $params['price_fee']),
                        'created_by' => auth()->user()->id,
                        'status' => Constants::USER_STATUS_ACTIVE,
                    ];
                    $this->money_comes_back_repo->store($money_comes_back);
                }

                //Xử lý trừ tiền của nhân viên
                if ($params['method'] == 'ONLINE' || $params['method'] == 'RUT_TIEN_MAT') {
                    $user = $this->userRepo->getById(auth()->user()->id);
                    $user_balance = $user->balance - $params['price_transfer'];
                    $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "CREATE_TRANSACTION_" . $resutl->id);
                    //cộng tiền vào tài khoản ngân hàng hưởng thụ phí
                    $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
                    if ($bank_account) {
                        $bank_account->balance -= $params['price_transfer'];
                        $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "CREATE_TRANSACTION_" . $resutl->id);
                    }
                } else {
                    $user = $this->userRepo->getById(auth()->user()->id);
                    $user_balance = $user->balance - $params['price_nop'];
                    $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "CREATE_TRANSACTION_" . $resutl->id);
                    //cộng tiền vào tài khoản ngân hàng hưởng thụ nhân viên
                    $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
                    if ($bank_account) {
                        $bank_account->balance -= $params['price_nop'];
                        $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "CREATE_TRANSACTION_" . $resutl->id);
                    }
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
            $params['time_payment'] = request('time_payment', null);
            $params['customer_name'] = request('customer_name', null);
            $params['price_nop'] = floatval(request('price_nop', 0));
            $params['price_rut'] = floatval(request('price_rut', 0));
            $params['price_transfer'] = floatval(request('price_transfer', 0));
            $params['price_repair'] = floatval(request('price_repair', 0));
            $params['created_by'] = auth()->user()->id;
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
            $params['customer_id'] = request('customer_id', 0);
            $params['lo_number'] = request('lo_number', 0);
            $params['note'] = request('note', null); // số lô
            $params['time_payment'] = str_replace('/', '-', $params['time_payment']);

            if ($params['lo_number'] > 0) {
                if ($params['time_payment']) {
                    $time_process = date('Y-m-d', strtotime($params['time_payment']));
                } else {
                    $time_process = date('Y-m-d');
                }
                $money_come = $this->money_comes_back_repo->getByLoTime(['lo_number' => $params['lo_number'], 'time_process' => $time_process]);
                if ($money_come && !empty($money_come->time_end)) {
                    return response()->json([
                        'code' => 400,
                        'error' => 'Không thể thêm mới giao dịch cho lô đã kết toán',
                        'data' => null
                    ]);
                }
            }

            $tran_old = $this->tran_repo->getById($params['id'], false);

            $pos = $this->pos_repo->getById($params['pos_id'], false);

            if ($pos) {
                $params['fee_cashback'] = $pos->fee_cashback;
                $params['original_fee'] = $pos->total_fee;
                $params['hkd_id'] = $pos->hkd_id;
            }
            $params['price_fee'] = ($params['fee'] * $params['price_rut']) / 100 + $params['price_repair'];
            $params['profit'] = ($params['fee'] - $params['original_fee']) * $params['price_rut'] / 100;


            if ($params['method'] == 'ONLINE' || $params['method'] == 'RUT_TIEN_MAT') {
                $params['price_nop'] = 0;
                $params['fee_paid'] = $params['price_fee'];
            } else {
                // $params['price_nop'] = $params['price_rut'];
                $params['fee_paid'] = 0;
                $params['price_transfer'] = 0;
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
                            'hkd_id' => $params['hkd_id'],
                            'lo_number' => $params['lo_number'],
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
                            'hkd_id' => $params['hkd_id'],
                            'lo_number' => $params['lo_number'],
                            'time_process' => $time_process,
                            'fee' => $params['original_fee'],
                            'total_price' => $params['price_rut'],
                            'payment' => ($params['price_rut'] - $params['price_fee']),
                            'created_by' => auth()->user()->id,
                            'status' => Constants::USER_STATUS_ACTIVE,
                        ];
                        $this->money_comes_back_repo->store($money_comes_back);
                    }

                    //Xử lý trừ tiền của nhân viên
                    if ($params['method'] == 'ONLINE' || $params['method'] == 'RUT_TIEN_MAT') {
                        $user = $this->userRepo->getById(auth()->user()->id);
                        $user_balance = $user->balance + $tran_old->price_transfer - $params['price_transfer'];
                        $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "UPDATE_TRANSACTION_" . $params['id']);
                        //cộng tiền vào tài khoản ngân hàng hưởng thụ nhân viên
                        $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
                        if ($bank_account) {
                            $bank_account->balance += $tran_old->price_transfer - $params['price_transfer'];
                            $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "UPDATE_TRANSACTION_" . $params['id']);
                        }
                    } else {
                        $user = $this->userRepo->getById(auth()->user()->id);
                        $user_balance = $user->balance + $tran_old->price_nop - $params['price_nop'];
                        $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "UPDATE_TRANSACTION_" . $params['id']);
                        //cộng tiền vào tài khoản ngân hàng hưởng thụ nhân viên
                        $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
                        if ($bank_account) {
                            $bank_account->balance += $tran_old->price_nop - $params['price_nop'];
                            $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "UPDATE_TRANSACTION_" . $params['id']);
                        }
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
            $tran = $this->tran_repo->getById($id, false);
            if ($tran->status != Constants::USER_STATUS_DELETED && $tran->lo_number > 0) {
                $time_process = date('Y-m-d', strtotime($tran->time_payment));
                $money_come = $this->money_comes_back_repo->getByLoTime(['lo_number' => $tran->lo_number, 'time_process' => $time_process]);
                if ($money_come) {
                    if ($tran->lo_number > 0) {
                        // Do đã công 1 lần r nên phải trừ đi lần cũ rồi cộng lại
                        $total_price = $money_come->total_price + $tran->price_rut - $tran->price_rut;
                        $payment = $money_come->payment + ($tran->price_rut - $tran->price_fee) - ($tran->price_rut - $tran->price_fee);
                    } else {
                        // Chưa có lần nào cộng
                        $total_price = $money_come->total_price + $tran->price_rut;
                        $payment = $money_come->payment + ($tran->price_rut - $tran->price_fee);
                    }
                    $money_comes_back = [
                        'pos_id' => $tran->pos_id,
                        'hkd_id' => $money_come->hkd_id,
                        'lo_number' => $tran->lo_number,
                        'time_process' => $time_process,
                        'fee' => $tran->original_fee,
                        'total_price' => $total_price,
                        'payment' => $payment,
                        'created_by' => auth()->user()->id,
                        'status' => $money_come->status,
                    ];
                    $this->money_comes_back_repo->update($money_comes_back, $money_come->id);
                }

                    //Xử lý trừ tiền của nhân viên
                    if ($tran->method == 'ONLINE' || $tran->method == 'RUT_TIEN_MAT') {
                        $user = $this->userRepo->getById(auth()->user()->id);
                        $user_balance = $user->balance + $tran->price_transfer;
                        $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "DELETE_TRANSACTION_" . $id);
                        //cộng tiền vào tài khoản ngân hàng hưởng thụ nhân viên
                        $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
                        if ($bank_account) {
                            $bank_account->balance += $tran->price_transfer;
                            $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "DELETE_TRANSACTION_" . $id);
                        }
                    } else {
                        $user = $this->userRepo->getById(auth()->user()->id);
                        $user_balance = $user->balance + $tran->price_nop;
                        $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "DELETE_TRANSACTION_" . $id);
                        //cộng tiền vào tài khoản ngân hàng hưởng thụ nhân viên
                        $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
                        if ($bank_account) {
                            $bank_account->balance += $tran->price_nop;
                            $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "DELETE_TRANSACTION_" . $id);
                        }
                    }
            }
            $data = $this->tran_repo->delete(['id' => $id]);
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

    public function ReportDashboard()
    {
        $tran_day = $this->tran_repo->ReportDashboard([]);
        $data_day_agent = $this->money_comes_back_repo->ReportDashboardAgent([]);
        $transfer_day = $this->transfer_repo->getTotalMaster([]);

        $data_day = [
            'san_luong' => $tran_day['san_luong'] + $data_day_agent['san_luong'], // tổng số tiền GD trong ngày
            'tien_nhan' => $tran_day['tien_nhan'] + $data_day_agent['tien_nhan'], // tổng tiền thực nhận của pos sau khi trừ phí gốc
            'profit' => $tran_day['profit'] + $data_day_agent['profit'], // tổng lợi nhuận theo GD và lô tiền về
            'tien_chuyen' => $transfer_day['total_transfer'],
        ];

        $params['date_from'] = date('Y-m-d H:i:s', strtotime('first day of this month'));
        $params['date_to'] = date('Y-m-d H:i:s', strtotime('last day of this month'));
        $tran_month = $this->tran_repo->ReportDashboard($params);
        $data_month_agent = $this->money_comes_back_repo->ReportDashboardAgent($params);
        $transfer_month = $this->transfer_repo->getTotalMaster($params);

        $data_month = [
            'san_luong' => $tran_month['san_luong'] + $data_month_agent['san_luong'], // tổng số tiền GD trong tháng
            'tien_nhan' => $tran_month['tien_nhan'] + $data_month_agent['tien_nhan'], // tổng tiền thực nhận của pos sau khi trừ phí gốc
            'profit' => round($tran_month['profit'] + $data_month_agent['profit'], 2), // tổng lợi nhuận theo GD và lô tiền về
            'tien_chuyen' => $transfer_month['total_transfer'],
        ];

        return response()->json([
            'code' => 200,
            'error' => 'Báo cáo Dashboard',
            'data' => [
                'day' => $data_day,
                'month' => $data_month
            ],
        ]);
    }

    public function PaymentFee(PaymentFeeRequest $request)
    {
        $id = request('id', null);
        $fee_paid = request('fee_paid', 0);
        $tran = $this->tran_repo->changeFeePaid($fee_paid, $id);

        if ($tran) {
            //cộng tiền vào tài khoản ngân hàng hưởng thụ phí
            $bank_account = $this->bankAccountRepo->getAccountFee();
            if ($bank_account) {
                $bank_account->balance += $fee_paid;
                $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "PAYMENT_FEE_TRANSACTION_" . $id);
            }
            return response()->json([
                'code' => 200,
                'error' => 'Thanh toán phí thành công',
                'data' => null
            ]);
        }
        return response()->json([
            'code' => 400,
            'error' => 'Thanh toán phí thất bại',
            'data' => null,
        ]);
    }

    public function ChartDashboard()
    {
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $data = $this->tran_repo->ChartDashboard($params);
        $data_agent = $this->money_comes_back_repo->ChartDashboardAgent($params);


        // Chuyển đổi mảng dữ liệu thành các collection để dễ xử lý
        $collection1 = collect($data['data']);
        $collection2 = collect($data_agent['data']);
        // Kết hợp các collection
        $merged = $collection1->concat($collection2);

        // Nhóm theo ngày và tính tổng
        $result = $merged->groupBy('date')->map(function ($group, $date) {
            return [
                'date' => $date,
                'total_price_rut' => $group->sum('total_price_rut'),
                'total_profit' => $group->sum('total_profit')
            ];
        })->values()->toArray();

        // Tính tổng hợp của tất cả các ngày
        $total = [
            'total_price_rut' => array_sum(array_column($result, 'total_price_rut')),
            'total_profit' => round(array_sum(array_column($result, 'total_profit')), 2)
        ];

        $final_result = [
            'data' => $result,
            'total' => $total
        ];

        return response()->json([
            'code' => 200,
            'error' => 'Biểu đồ Dashboard',
            'data' => $final_result,
        ]);
    }

    public function RestoreFee()
    {
        $id = request('id', null);
        $tran_fee = $this->tran_repo->getById($id, false);
        $fee_paid = 0;
        $fee_paid_balance = 0;
        if ($tran_fee && $tran_fee->fee_paid > 0) {
            $fee_paid = $tran_fee->fee_paid * (-1);
            $fee_paid_balance = $tran_fee->fee_paid;
        } else {
            return response()->json([
                'code' => 400,
                'error' => 'Không tìm thấy giao dịch hoặc phí đã được hoàn',
                'data' => null,
            ]);
        }
        $tran = $this->tran_repo->changeFeePaid($fee_paid, $id);

        if ($tran) {
            //cộng tiền vào tài khoản ngân hàng hưởng thụ phí
            $bank_account = $this->bankAccountRepo->getAccountFee();
            if ($bank_account) {
                $bank_account->balance -= $fee_paid_balance;
                $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "RESTORE_FEE_TRANSACTION_" . $id);
            }
            return response()->json([
                'code' => 200,
                'error' => 'Hoàn phí thành công',
                'data' => null
            ]);
        }
        return response()->json([
            'code' => 400,
            'error' => 'Hoàn phí thất bại',
            'data' => null,
        ]);
    }

    public function GetAllHkd()
    {
        $params['keyword'] = request('keyword', null);
        $params['hkd_id'] = request('hkd_id', 0);
        $params['lo_number'] = request('lo_number', 0);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);

        $data = $this->tran_repo->getAllByHkd($params);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách giao dịch theo Hkd',
            'data' => $data,
        ]);
    }
}
