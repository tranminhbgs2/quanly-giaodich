<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\CashFlow\ChangeStatusRequest;
use App\Http\Requests\CashFlow\DeleteRequest;
use App\Http\Requests\CashFlow\GetDetailRequest;
use App\Http\Requests\CashFlow\ListingRequest;
use App\Http\Requests\CashFlow\StoreRequest;
use App\Http\Requests\CashFlow\UpdateRequest;
use App\Repositories\CashFlow\CashFlowRepo;
use App\Repositories\BankAccount\BankAccountRepo;

class CashFlowController extends Controller
{
    protected $cash_flow_repo;
    protected $bankAccountRepo;

    public function __construct(CashFlowRepo $agentRepo, BankAccountRepo $bankAccountRepo)
    {
        $this->cash_flow_repo = $agentRepo;
        $this->bankAccountRepo = $bankAccountRepo;
    }

    /**
     * API lấy ds đại lý
     * URL: {{url}}/api/v1/agent
     *
     * @param ListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(ListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['type'] = request('type', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = auth()->user()->account_type;

        $data = $this->cash_flow_repo->getListing($params, false);
        $total = $this->cash_flow_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách dòng tiền',
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
     * API lấy thông tin chi tiết đại lý
     * URL: {{url}}/api/v1/agent/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(GetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->cash_flow_repo->getDetail($params);
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
     * API thêm mới đại lý
     * URL: {{url}}/api/v1/agent/store
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params['type'] = request('type', null); // ngân hàng
        $params['acc_bank_id'] = request('acc_bank_id', 0); // hình thức
        $params['price'] = request('price', 0); // máy pos
        $params['note'] = request('note', null); // phí
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['manager_id'] = auth()->user()->id; // người tạo
        $params['time_payment'] = request('time_payment', null); // phí

        $params['time_payment'] = str_replace('/', '-', $params['time_payment']);

        $id = $this->cash_flow_repo->store($params);
        if ($id > 0) {
            if ($params['type'] == "RUT_TIEN") {
                $bank_from = $this->bankAccountRepo->getById($params['acc_bank_id']);
                $bank_from_balance = $bank_from->balance - $params['price'];
                $this->bankAccountRepo->updateBalance($params['acc_bank_id'], $bank_from_balance, "CREATED_CASH_FLOW_" . $id);
            } elseif ($params['type'] == "NAP_TIEN") {
                $bank_to_new = $this->bankAccountRepo->getById($params['acc_bank_id']);
                $bank_to_new_balance = $bank_to_new->balance + $params['price'];
                $this->bankAccountRepo->updateBalance($params['acc_bank_id'], $bank_to_new_balance, "CREATED_CASH_FLOW_" . $id);
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
     * URL: {{url}}/api/v1/agent/update/id
     *
     * @param UpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request)
    {
        $params['id'] = request('id', null);
        if ($params['id']) {
            $params['type'] = request('type', null); // ngân hàng
            $params['acc_bank_id'] = request('acc_bank_id', 0); // hình thức
            $params['price'] = request('price', 0); // máy pos
            $params['note'] = request('note', null); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
            $params['manager_id'] = auth()->user()->id; // người tạo
            $params['time_payment'] = request('time_payment', null); // phí

            $cash = $this->cash_flow_repo->getById($params['id']);

            $resutl = $this->cash_flow_repo->update($params, $params['id']);

            if ($resutl) {
                if ($cash->acc_bank_id != $params['acc_bank_id']) {
                    // cập nhật lại số dư đối với tk ngân hàng mới
                    if ($params['type'] != $cash->type) {
                        if ($params['type'] == "RUT_TIEN") {
                            $bank_from = $this->bankAccountRepo->getById($params['acc_bank_id']);
                            $bank_from_balance = $bank_from->balance -  $params['price'];
                            $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "UPDATED_CASH_FLOW_" . $params['id']);
                        } elseif ($params['type'] == "NAP_TIEN") {
                            $bank_to_new = $this->bankAccountRepo->getById($params['acc_bank_id']);
                            $bank_to_new_balance = $bank_to_new->balance + $params['price'];
                            $this->bankAccountRepo->updateBalance($params['acc_bank_id'], $bank_to_new_balance, "UPDATED_CASH_FLOW_" . $params['id']);
                        }
                        // cập nhật lại số dư đối với tk ngân hàng cũ về số ban đầu
                        if ($cash->type == "RUT_TIEN") {
                            $bank_from = $this->bankAccountRepo->getById($cash->acc_bank_id);
                            $bank_from_balance = $bank_from->balance + $cash->price;
                            $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "BACK_UPDATED_CASH_FLOW_" . $params['id']);
                        } elseif ($cash->type == "NAP_TIEN") {
                            $bank_to_new = $this->bankAccountRepo->getById($cash->acc_bank_id);
                            $bank_to_new_balance = $bank_to_new->balance - $cash->price;
                            $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_to_new_balance, "BACK_UPDATED_CASH_FLOW_" . $params['id']);
                        }
                    } else {
                        if ($params['type'] == "RUT_TIEN") {
                            $bank_from = $this->bankAccountRepo->getById($cash->acc_bank_id);
                            $bank_from_balance = $bank_from->balance + $cash->price;
                            $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "BACK_UPDATED_CASH_FLOW_" . $params['id']);

                            $bank_from_new = $this->bankAccountRepo->getById($params['acc_bank_id']);
                            $bank_from_balance_new = $bank_from_new->balance - $params['price'];
                            $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance_new, "UPDATED_CASH_FLOW_" . $params['id']);
                        } elseif ($params['type'] == "NAP_TIEN") {
                            $bank_to_new = $this->bankAccountRepo->getById($params['acc_bank_id']);
                            $bank_to_new_balance = $bank_to_new->balance  + $params['price'];
                            $this->bankAccountRepo->updateBalance($params['acc_bank_id'], $bank_to_new_balance, "UPDATED_CASH_FLOW_" . $params['id']);

                            $bank_from = $this->bankAccountRepo->getById($cash->acc_bank_id);
                            $bank_from_balance = $bank_from->balance - $cash->price;
                            $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "BACK_UPDATED_CASH_FLOW_" . $params['id']);
                        }
                    }
                } else {
                    // cập nhật lại số dư đối với tk ngân hàng mới
                    if ($params['type'] != $cash->type) {
                        $bank_from = $this->bankAccountRepo->getById($params['acc_bank_id']);
                        $bank_from_balance = $bank_from->balance;
                        if ($params['type'] == "RUT_TIEN") {
                            $bank_from_balance -= $params['price'];
                        } elseif ($params['type'] == "NAP_TIEN") {
                            $bank_from_balance += $params['price'];
                        }
                        // cập nhật lại số dư đối với tk ngân hàng cũ về số ban đầu
                        if ($cash->type == "RUT_TIEN") {
                            $bank_from_balance += $cash->price;
                        } elseif ($cash->type == "NAP_TIEN") {
                            $bank_from_balance -= $cash->price;
                        }
                        $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "UPDATED_CASH_FLOW_" . $params['id']);
                    } else {
                        $bank_from = $this->bankAccountRepo->getById($params['acc_bank_id']);
                        $bank_from_balance = 0;
                        if ($params['type'] == "RUT_TIEN") {
                            $bank_from_balance = $bank_from->balance + $cash->price - $params['price'];
                        } elseif ($params['type'] == "NAP_TIEN") {
                            $bank_from_balance = $bank_from->balance  + $params['price'] - $cash->price;
                        }
                        $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "UPDATED_CASH_FLOW_" . $params['id']);
                    }
                }
                return response()->json([
                    'code' => 200,
                    'error' => 'Cập nhật thông tin thành công',
                    'data' => null
                ]);
            }
        }
        return response()->json([
            'code' => 422,
            'error' => 'ID không hợp lệ',
            'data' => null
        ]);
    }

    /**
     * API xóa thông tin khách hàng, xóa trạng thái, ko xóa vật lý
     * URL: {{url}}/api/v1/agent/delete/1202112817000308
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
                $cash = $this->cash_flow_repo->getById($params['id']);
                $res = $this->cash_flow_repo->delete($params);
                if ($res) {
                    $bank_from = $this->bankAccountRepo->getById($cash->acc_bank_id);
                    $bank_from_balance = 0;
                    // cập nhật lại số dư đối với tk ngân hàng cũ về số ban đầu
                    if ($cash->type == "RUT_TIEN") {
                        $bank_from_balance = $bank_from->balance + $cash->price;
                    } elseif ($cash->type == "NAP_TIEN") {
                        $bank_from_balance = $bank_from->balance - $cash->price;
                    }
                    $this->bankAccountRepo->updateBalance($cash->acc_bank_id, $bank_from_balance, "UPDATED_CASH_FLOW_" . $params['id']);
                }
                return response()->json([
                    'code' => 200,
                    'error' => 'Xóa thành công',
                    'data' => null
                ]);
            } else {
                return response()->json([
                    'code' => 422,
                    'error' => 'ID không hợp lệ',
                    'data' => null
                ]);
            }
        } else {
            return response()->json([
                'code' => 422,
                'error' => 'Truyền thiếu ID',
                'data' => null
            ]);
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        $params['id'] = request('id', null);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);

        $resutl = $this->cash_flow_repo->changeStatus($params['status'], $params['id']);

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
        $data = $this->cash_flow_repo->getAll();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Đại lý',
            'data' => $data
        ]);
    }
}
