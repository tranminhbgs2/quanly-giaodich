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
        $params['created_by'] = request('created_by', 0);
        $params['account_type'] = auth()->user()->account_type;
        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $params['method'] = request('method', null);
        $params['status_fee'] = request('status_fee', 1);

        if ($params['account_type'] == Constants::ACCOUNT_TYPE_STAFF) {
            $params['created_by'] = auth()->user()->id;
        }

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
        $params['original_fee'] = floatval(request('original_fee', 0)); // phí tổng
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
        $params['type_card'] = request('type_card', null); // số lô
        $params['bank_code'] = request('bank_code', null); // số lô
        $params['price_array'] = request('price_array', null); // số tiền bù

        if (is_array($params['price_array'])) {
            // Chuyển đổi mảng thành chuỗi JSON
            $params['price_array'] = json_encode($params['price_array']);
        }

        if ($params['time_payment']) {
            $params['time_payment'] = str_replace('/', '-', $params['time_payment']);
        }
        $params['hkd_id'] = 0;
        $pos = $this->pos_repo->getById($params['pos_id'], false);

        $check = 0;
        if ($pos) {
            $params['fee_cashback'] = $pos->fee_cashback; // phí hoàn
            $params['original_fee'] = $pos->total_fee;
            $params['hkd_id'] = $pos->hkd_id;
            if ($pos->bank_code == "VIETCOMBANK" && $params['bank_code'] == "VIETCOMBANK") {
                switch (trim($params['type_card'])) {
                    case 'JCB':
                        $check = 1;
                        $params['original_fee'] = $pos->fee_jcb + $pos->fee_cashback;
                        break;
                    case 'VISA':
                        $check = 2;
                        $params['original_fee'] = $pos->fee_visa + $pos->fee_cashback;
                        break;
                    case 'MASTER':
                        $check = 3;
                        $params['original_fee'] = $pos->fee_master + $pos->fee_cashback;
                        break;
                    case 'NAPAS':
                        break;
                    case 'AMEX':
                        $check = 4;
                        $params['original_fee'] = $pos->fee_amex + $pos->fee_cashback;
                        break;
                }
            }

            if ($pos->bank_code == "VIETCOMBANK" && $params['bank_code'] == "VIB" && $params['type_card'] == "AMEX") {
                $check = 5;
                $params['original_fee'] = $pos->fee_napas + $pos->fee_cashback;
            }
        }

        if ($params['price_fee'] == 0) {
            $params['price_fee'] = ($params['fee'] * $params['price_rut']) / 100; // số tiền phí
        }

        //tổng phí
        $params['total_fee'] = $params['price_fee'] + $params['price_repair'];

        $params['profit'] = $params['price_fee'] - ($params['original_fee'] * $params['price_rut'] / 100); // lợi nhuận

        if ($params['lo_number'] <= 0 && $params['pos_id'] == 0) {
            $params['status'] = Constants::USER_STATUS_DRAFT;
        } else {
            if ($params['method'] == 'ONLINE' || $params['method'] == 'RUT_TIEN_MAT' || $params['method'] == 'QR_CODE') {
                $params['price_nop'] = 0;
                $params['fee_paid'] = $params['total_fee'];
            } else {
                $params['fee_paid'] = 0;
                $params['price_transfer'] = 0;
                $params['transfer_by'] = auth()->user()->id;
            }
            if ($pos->method == "GATEWAY" || $pos->method == "QR_CODE") {
                if ($params['time_payment']) {
                    $time_lo = date('dmy', strtotime($params['time_payment']));
                } else {
                    $time_lo = date('dmy');
                }
                if ($check > 0) {
                    $params['lo_number'] = $check . $params['pos_id'] . $time_lo;
                } else {
                    $params['lo_number'] = $params['pos_id'] . $time_lo;
                }
            }

            if ($this->isLotClosed($params)) {
                return response()->json([
                    'code' => 400,
                    'error' => 'Không thể thêm mới giao dịch cho lô đã kết toán',
                    'data' => null
                ]);
            }
        }

        $resutl = $this->tran_repo->store($params);

        if ($resutl) {
            if ($params['lo_number'] > 0 && $params['pos_id'] > 0) {
                if ($params['time_payment']) {
                    $time_process = date('Y-m-d', strtotime($params['time_payment']));
                } else {
                    $time_process = date('Y-m-d');
                }
                $money_come = $this->money_comes_back_repo->getByLoTime(['pos_id' => $params['pos_id'], 'lo_number' => $params['lo_number'], 'time_process' => $time_process]);
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
                        'status' => Constants::USER_STATUS_LOCKED,
                    ];
                    $price_rut = ($params['price_rut'] - ($params['original_fee'] * $params['price_rut']) / 100); // Tính số tiền cộng cho HKD
                    $this->money_comes_back_repo->updateKL($money_comes_back, $money_come->id, $price_rut, "CREATED");
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
                        'status' => Constants::USER_STATUS_LOCKED,
                    ];
                    $this->money_comes_back_repo->store($money_comes_back);
                }
            }

            //Xử lý trừ tiền của nhân viên đối với đáo hạn
            if ($params['method'] == 'DAO_HAN') {
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
        $params = $this->getRequestParams();

        if ($params['id']) {

            $tran_old = $this->tran_repo->getById($params['id'], false);
            $this->prepareParamsForUpdate($params, $tran_old);

            if ($this->isLotClosed($params)) {
                return response()->json([
                    'code' => 400,
                    'error' => 'Không thể thêm mới giao dịch cho lô đã kết toán',
                    'data' => null
                ]);
            }
            $resutl = $this->tran_repo->update($params);

            if ($resutl) {
                $this->handleMoneyComesBackUpdates($params, $tran_old);
                $this->handleUserBalanceUpdates($params, $tran_old);

                return response()->json([
                    'code' => 200,
                    'error' => 'Cập nhật thông tin thành công',
                    'data' => null,
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

    private function getRequestParams()
    {
        $params = request()->all();
        $params['fee'] = floatval(request('fee', 0));
        $params['price_nop'] = floatval(request('price_nop', 0));
        $params['price_rut'] = floatval(request('price_rut', 0));
        $params['price_fee'] = floatval(request('price_fee', 0));
        $params['price_transfer'] = floatval(request('price_transfer', 0));
        $params['price_repair'] = floatval(request('price_repair', 0));
        $params['category_id'] = request('category_id', 0);
        $params['pos_id'] = request('pos_id', 0);

        $params['customer_id'] = request('customer_id', 0);
        $params['lo_number'] = request('lo_number', 0);
        $params['time_payment'] = str_replace('/', '-', $params['time_payment']);

        if (is_array(request('price_array'))) {
            $params['price_array'] = json_encode(request('price_array'));
        }

        return $params;
    }

    /**
     * Kiểm tra lô đã kết toán hay chưa
     */
    private function isLotClosed($params)
    {
        if ($params['lo_number'] > 0) {
            $time_process = $params['time_payment'] ? date('Y-m-d', strtotime($params['time_payment'])) : date('Y-m-d');
            $money_come = $this->money_comes_back_repo->getByLoTime([
                'pos_id' => $params['pos_id'],
                'lo_number' => $params['lo_number'],
                'time_process' => $time_process
            ]);
            return $money_come && !empty($money_come->time_end);
        }
        return false;
    }

    // Xử lý param đầu vào
    private function prepareParamsForUpdate(&$params, $tran_old)
    {
        if ($tran_old->status == Constants::USER_STATUS_DRAFT) {
            $params['status'] = Constants::USER_STATUS_ACTIVE;
        }

        $pos = $this->pos_repo->getById($params['pos_id'], false);
        $params['hkd_id'] = 0;
        $check = 0;

        if ($pos) {
            $params['fee_cashback'] = $pos->fee_cashback;
            $params['original_fee'] = $pos->total_fee;
            $params['hkd_id'] = $pos->hkd_id;

            // Check phí theo loại thẻ của Vietcombank
            if ($pos->bank_code == "VIETCOMBANK" && $params['bank_code'] == "VIETCOMBANK") {
                switch (trim($params['type_card'])) {
                    case 'JCB':
                        $check = 1;
                        $params['original_fee'] = $pos->fee_jcb + $pos->fee_cashback;
                        break;
                    case 'VISA':
                        $check = 2;
                        $params['original_fee'] = $pos->fee_visa + $pos->fee_cashback;
                        break;
                    case 'MASTER':
                        $check = 3;
                        $params['original_fee'] = $pos->fee_master + $pos->fee_cashback;
                        break;
                    case 'AMEX':
                        $check = 4;
                        $params['original_fee'] = $pos->fee_amex + $pos->fee_cashback;
                        break;
                }
            }

            if ($pos->bank_code == "VIETCOMBANK" && $params['bank_code'] == "VIB" && $params['type_card'] == "AMEX") {
                $check = 5;
                $params['original_fee'] = $pos->fee_napas + $pos->fee_cashback;
            }
        }

        // Tính phí
        if ($params['price_fee'] == 0) {
            $params['price_fee'] = ($params['fee'] * $params['price_rut']) / 100 + $params['price_repair'];
        }

        $params['total_fee'] = $params['price_fee'] + $params['price_repair'];
        $params['profit'] = $params['price_fee'] - $params['original_fee'] * $params['price_rut'] / 100;

        if (in_array($params['method'], ['ONLINE', 'RUT_TIEN_MAT', 'QR_CODE'])) {
            $params['price_nop'] = 0;
            $params['fee_paid'] = $params['total_fee'];
        } else {
            $params['fee_paid'] = 0;
            $params['price_transfer'] = 0;
            $params['transfer_by'] = auth()->user()->id;
        }

        // Xử lý tạo số lô
        if ($pos && in_array($pos->method, ["GATEWAY", "QR_CODE"])) {
            $time_lo = $params['time_payment'] ? date('dmy', strtotime($params['time_payment'])) : date('dmy');
            $params['lo_number'] = $check > 0 ? $check . $params['pos_id'] . $time_lo : $params['pos_id'] . $time_lo;
        }
    }

    // Xử lý tạo mới thông tin lô tiền về
    private function handleMoneyComesBackUpdates($params, $tran_old)
    {
        if ($params['lo_number'] > 0) {
            $time_process = $params['time_payment'] ? date('Y-m-d', strtotime($params['time_payment'])) : date('Y-m-d');
            $time_process_old = $tran_old->time_payment ? date('Y-m-d', strtotime($tran_old->time_payment)) : date('Y-m-d');

            if ($tran_old->pos_id != $params['pos_id']) {
                $this->handleMoneyComesBack($tran_old, $params, $time_process, $time_process_old, 'pos_id');
            } elseif ($tran_old->lo_number != $params['lo_number']) {
                $this->handleMoneyComesBack($tran_old, $params, $time_process, $time_process_old, 'lo_number');
            } elseif ($tran_old->time_payment != $params['time_payment']) {
                $this->handleMoneyComesBack($tran_old, $params, $time_process, $time_process_old, 'time_payment');
            } else {
                $money_come = $this->money_comes_back_repo->getByLoTime([
                    'pos_id' => $params['pos_id'],
                    'lo_number' => $params['lo_number'],
                    'time_process' => $time_process
                ]);

                $this->CreateMoneyComesBack($money_come, $tran_old, $params, $time_process, "UPDATED");
            }
        }
    }

    // Xử lý cập nhật hoặc tạo mới thông tin lô tiền về
    private function handleMoneyComesBack($tran_old, $params, $time_process, $time_process_old, $type)
    {
        $money_come_old = $this->money_comes_back_repo->getByLoTime([
            'pos_id' => $tran_old->pos_id,
            'lo_number' => $tran_old->lo_number,
            'time_process' => $time_process_old
        ]);

        $money_come_new = $this->money_comes_back_repo->getByLoTime([
            'pos_id' => ($type == 'pos_id') ? $params['pos_id'] : $tran_old->pos_id,
            'lo_number' => ($type == 'lo_number') ? $params['lo_number'] : $tran_old->lo_number,
            'time_process' => $time_process
        ]);

        if ($money_come_old) {
            $total_price = $money_come_old->total_price - $tran_old->price_rut;
            $payment = $money_come_old->payment - ($tran_old->price_rut - $tran_old->price_fee);

            $money_comes_back = [
                'pos_id' => $tran_old->pos_id,
                'hkd_id' => $money_come_old->hkd_id,
                'lo_number' => $tran_old->lo_number,
                'time_process' => $time_process_old,
                'fee' => $tran_old->original_fee,
                'total_price' => $total_price,
                'payment' => $payment,
                'created_by' => auth()->user()->id,
                'status' => $money_come_old->status,
            ];

            $pos = $this->pos_repo->getById($tran_old->pos_id, false);
            $price_rut = 0;

            if ($pos) {
                $price_rut = ($tran_old->price_rut - ($tran_old->original_fee * $tran_old->price_rut) / 100) * (-1);
            }

            $this->money_comes_back_repo->updateKL($money_comes_back, $money_come_old->id, $price_rut, 'UPDATED');
        }

        $this->CreateMoneyComesBack($money_come_new, $tran_old, $params, $time_process, "NEW");
    }

    // Xử lý cập nhật tiền cho user và bank account
    private function handleUserBalanceUpdates($params, $tran_old)
    {
        if ($params['method'] == 'DAO_HAN') {
            $user = $this->userRepo->getById(auth()->user()->id);
            $user_balance = ($tran_old->method == 'DAO_HAN')
                ? $user->balance + $tran_old->price_nop - $params['price_nop']
                : $user->balance - $params['price_nop'];

            $this->userRepo->updateBalance(auth()->user()->id, $user_balance, "UPDATE_TRANSACTION_" . $params['id']);

            $bank_account = $this->bankAccountRepo->getAccountStaff(auth()->user()->id);
            if ($bank_account) {
                $bank_account->balance += $tran_old->price_nop - $params['price_nop'];
                $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "UPDATE_TRANSACTION_" . $params['id']);
            }
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
                $money_come = $this->money_comes_back_repo->getByLoTime(['pos_id' => $tran->pos_id, 'lo_number' => $tran->lo_number, 'time_process' => $time_process]);
                if ($money_come) {
                    // Do đã công 1 lần r nên phải trừ đi lần cũ rồi cộng lại
                    $total_price = $money_come->total_price - $tran->price_rut;
                    $payment = $money_come->payment - ($tran->price_rut - $tran->price_fee);
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
                    $pos = $this->pos_repo->getById($tran->pos_id, false);
                    $price_rut = 0;
                    if ($pos) {
                        $price_rut = ($tran->price_rut - ($tran->original_fee * $tran->price_rut) / 100) * (-1);
                    }
                    $this->money_comes_back_repo->updateKL($money_comes_back, $money_come->id, $price_rut, 'DELETED');
                }

                //Xử lý trừ tiền của nhân viên
                if (($tran->method == 'ONLINE' || $tran->method == 'RUT_TIEN_MAT' || $tran->method == 'QR_CODE') && $tran->status_fee == 3) {
                    //đã xác nhận chuyển tiền thì mới thực hiện trừ cộng lại tiền
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

                if ($tran->status_fee == 3) {
                    $bank_account = $this->bankAccountRepo->getAccountFee();
                    if ($bank_account) {
                        $bank_account->balance -= $tran->fee_paid;
                        $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "DELETE_TRANSACTION_" . $tran->id);
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
        $transfer_day = $this->transfer_repo->getTotalMaster([]);

        if (!in_array(auth()->user()->account_type, [Constants::ACCOUNT_TYPE_SYSTEM, Constants::ACCOUNT_TYPE_ACCOUNTANT])) {
            $data_day = [
                'san_luong' => $tran_day['san_luong'], // tổng số tiền GD trong ngày
                'tien_nhan' => (int)$transfer_day['total_transfer'], // Tiền master chuyển khoản
                'profit' => (int)$tran_day['profit'], // tổng lợi nhuận theo GD và lô tiền về
                'tien_chuyen' => (int)$tran_day['price_nop'] + (int)$tran_day['price_transfer'], // Tiền chuyển và tiền nộp cho KH
            ];
        } else {
            $data_day_agent = $this->money_comes_back_repo->ReportDashboardAgent([]);
            $data_day = [
                'san_luong' => $tran_day['san_luong'] + $data_day_agent['san_luong'], // tổng số tiền GD trong ngày
                'tien_nhan' => $tran_day['tien_nhan'] + $data_day_agent['tien_nhan'], // tổng tiền thực nhận của pos sau khi trừ phí gốc
                'profit' => (int)($tran_day['profit'] + $data_day_agent['profit']), // tổng lợi nhuận theo GD và lô tiền về
                'tien_chuyen' => (int)$transfer_day['total_transfer'],
            ];
        }

        $params['date_from'] = date('Y-m-d H:i:s', strtotime('first day of this month'));
        $params['date_to'] = date('Y-m-d H:i:s', strtotime('last day of this month'));
        $tran_month = $this->tran_repo->ReportDashboard($params);
        $transfer_month = $this->transfer_repo->getTotalMaster($params);
        $data_month_agent = $this->money_comes_back_repo->ReportDashboardAgent($params);

        if (!in_array(auth()->user()->account_type, [Constants::ACCOUNT_TYPE_SYSTEM, Constants::ACCOUNT_TYPE_ACCOUNTANT])) {
            $data_month = [
                'san_luong' => $tran_month['san_luong'], // tổng số tiền GD trong tháng
                'tien_nhan' => (int)$transfer_month['total_transfer'], // Tiền master chuyển khoản
                'profit' => (int)$tran_month['profit'], // tổng lợi nhuận theo GD và lô tiền về
                'tien_chuyen' => (int)$tran_month['price_nop'] + (int)$tran_month['price_transfer'], // Tiền chuyển và tiền nộp cho KH
            ];
        } else {
            $data_month = [
                'san_luong' => $tran_month['san_luong'] + $data_month_agent['san_luong'], // tổng số tiền GD trong tháng
                'tien_nhan' => (int)($tran_month['tien_nhan'] + $data_month_agent['tien_nhan']), // tổng tiền thực nhận của pos sau khi trừ phí gốc
                'profit' => (int)($tran_month['profit'] + $data_month_agent['profit']), // tổng lợi nhuận theo GD và lô tiền về
                'tien_chuyen' => (int)$transfer_month['total_transfer'],
            ];
        }

        return response()->json([
            'code' => 200,
            'error' => 'Báo cáo Dashboard',
            'data' => [
                'day' => $data_day,
                'month' => $data_month,
                'data_month_agent' => $data_month_agent,
                'tran_month' => $tran_month,
            ],
        ]);
    }

    public function PaymentFee(PaymentFeeRequest $request)
    {
        $id = request('id', null);
        $fee_paid = request('fee_paid', 0);
        $tran_detail = $this->tran_repo->getById($id);
        $transfer_by = 0;
        if ($tran_detail->method == 'ONLINE' || $tran_detail->method == 'RUT_TIEN_MAT' || $tran_detail->method == 'QR_CODE') {
            if ($tran_detail->fee_paid != $tran_detail->total_fee) {
                $fee_paid = $tran_detail->total_fee;
            }
            $transfer_by = auth()->user()->id;
        } else {
            $transfer_by = $tran_detail->transfer_by;
        }
        $tran = $this->tran_repo->changeFeePaid($fee_paid, $id, $transfer_by);

        if ($tran) {
            if ($tran_detail->method == 'ONLINE' || $tran_detail->method == 'RUT_TIEN_MAT' || $tran_detail->method == 'QR_CODE') {
                // Đối với GD rút tiền thì xác nhận phí là thực hiện trừ tiền của nhân viên
                $user = $this->userRepo->getById($transfer_by);
                if ($user) {
                    $user_balance = $user->balance - $tran_detail->price_transfer;
                    $this->userRepo->updateBalance($transfer_by, $user_balance, "PAYMENT_FEE_TRANSACTION_" . $tran_detail->id);
                }

                $bank_account = $this->bankAccountRepo->getAccountStaff($transfer_by);
                if ($bank_account) {
                    $bank_account->balance -= $tran_detail->price_transfer;
                    $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "PAYMENT_FEE_TRANSACTION_" . $tran_detail->id);
                }
            } else {
                //cộng tiền vào tài khoản ngân hàng hưởng thụ phí
                $bank_account = $this->bankAccountRepo->getAccountFee();
                if ($bank_account) {
                    if ($tran_detail->method == 'DAO_HAN') {
                        $bank_account->balance += $fee_paid;
                    }
                    $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "PAYMENT_FEE_TRANSACTION_" . $id);
                }
            }
            if ($tran_detail->time_payment) {
                $time_process = date('Y-m-d', strtotime($tran_detail->time_payment));
            } else {
                $time_process = date('Y-m-d');
            }
            $money_come = $this->money_comes_back_repo->getByLoTime(['pos_id' => $tran_detail->pos_id, 'lo_number' => $tran_detail->lo_number, 'time_process' => $time_process]);
            if (!$money_come) {
                $money_comes_back = [
                    'pos_id' => $tran_detail->pos_id,
                    'hkd_id' => $tran_detail->hkd_id,
                    'lo_number' => $tran_detail->lo_number,
                    'time_process' => $time_process,
                    'fee' => $tran_detail->original_fee,
                    'total_price' => $tran_detail->price_rut,
                    'payment' => ($tran_detail->price_rut - $tran_detail->price_fee),
                    'created_by' => auth()->user()->id,
                    'status' => Constants::USER_STATUS_LOCKED,
                ];
                $this->money_comes_back_repo->store($money_comes_back);
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
            'total_profit' => (int)array_sum(array_column($result, 'total_profit'))
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
        if ($tran_fee->status_fee == 2) {
            return response()->json([
                'code' => 400,
                'error' => 'Không tìm thấy giao dịch hoặc phí đã được hoàn',
                'data' => null,
            ]);
        }
        if ($tran_fee && $tran_fee->fee_paid > 0 && $tran_fee->method == 'DAO_HAN') {
            $fee_paid = $tran_fee->fee_paid * (-1);
            $fee_paid_balance = $tran_fee->fee_paid;
        }
        $transfer_by = 0;
        if ($tran_fee->method == 'ONLINE' || $tran_fee->method == 'RUT_TIEN_MAT' || $tran_fee->method == 'QR_CODE') {
            $transfer_by = 0;
        } else {
            $transfer_by = $tran_fee->transfer_by;
        }
        $tran = $this->tran_repo->changeFeePaid($fee_paid, $id, $transfer_by, "RESTORE");

        if ($tran) {
            if ($tran_fee->method == 'ONLINE' || $tran_fee->method == 'RUT_TIEN_MAT' || $tran_fee->method == 'QR_CODE') {
                // Đối với GD rút tiền thì xác nhận phí là thực hiện trừ tiền của nhân viên
                $user = $this->userRepo->getById($tran_fee->transfer_by);
                if ($user) {
                    $user_balance = $user->balance + $tran_fee->price_transfer;
                    $this->userRepo->updateBalance($tran_fee->transfer_by, $user_balance, "RESTORE_FEE_TRANSACTION_" . $tran_fee->id);
                }

                $bank_account = $this->bankAccountRepo->getAccountStaff($tran_fee->transfer_by);
                if ($bank_account) {
                    $bank_account->balance += $tran_fee->price_transfer;
                    $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "RESTORE_FEE_TRANSACTION_" . $tran_fee->id);
                }
            } else {
                //cộng tiền vào tài khoản ngân hàng hưởng thụ phí
                $bank_account = $this->bankAccountRepo->getAccountFee();
                if ($bank_account) {
                    $bank_account->balance -= $fee_paid_balance;
                    $this->bankAccountRepo->updateBalance($bank_account->id, $bank_account->balance, "RESTORE_FEE_TRANSACTION_" . $id);
                }
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
        $params['pos_id'] = request('pos_id', 0);
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

    public function GetTopStaff()
    {
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);
        $params['account_type'] = auth()->user()->account_type;
        $params['created_by'] = auth()->user()->id;
        $data = $this->tran_repo->topStaffTransaction($params);
        $total_rut = 0;
        foreach ($data as $key => $value) {
            $total_rut += $value['total_price_rut'];
        }
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách top nhân viên',
            'data' => $data,
            'total' => [
                'total_rut' => $total_rut

            ],
        ]);
    }

    private function CreateMoneyComesBack($money_come, $tran_old, $params, $time_process, $type = "UPDATED")
    {
        if ($money_come) {
            if ($tran_old->lo_number > 0 && $type == "UPDATED") {
                // Do đã công 1 lần r nên phải trừ đi lần cũ rồi cộng lại
                $total_price = $money_come->total_price + $params['price_rut'] - $tran_old->price_rut;
                $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']) - ($tran_old->price_rut - $tran_old->price_fee);
                $price_rut = ($params['price_rut'] - ($params['original_fee'] * $params['price_rut']) / 100) - ($tran_old->price_rut - ($params['original_fee'] * $tran_old->price_rut) / 100); // Tính số tiền cộng cho HKD
            } else {
                // Chưa có lần nào cộng
                $total_price = $money_come->total_price + $params['price_rut'];
                $payment = $money_come->payment + ($params['price_rut'] - $params['price_fee']);
                $price_rut = ($params['price_rut'] - ($params['original_fee'] * $params['price_rut']) / 100); // Tính số tiền cộng cho HKD
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
            $this->money_comes_back_repo->updateKL($money_comes_back, $money_come->id, $price_rut, "UPDATED");
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
                'status' => Constants::USER_STATUS_LOCKED,
            ];
            $this->money_comes_back_repo->store($money_comes_back);
        }
    }
}
