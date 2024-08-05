<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\ChangeStatusRequest;
use App\Http\Requests\Transfer\DeleteRequest;
use App\Http\Requests\Transfer\GetDetailRequest;
use App\Http\Requests\Transfer\ListingRequest;
use App\Http\Requests\Transfer\StoreRequest;
use App\Http\Requests\Transfer\UpdateRequest;
use App\Repositories\Agent\AgentRepo;
use App\Repositories\BankAccount\BankAccountRepo;
use App\Repositories\Transfer\TransferRepo;
use App\Repositories\User\UserRepo;

class TransferController extends Controller
{
    protected $cate_repo;
    protected $bank_acc_repo;
    protected $agent_repo;
    protected $userRepo;

    public function __construct(TransferRepo $cateRepo, BankAccountRepo $bankAccRepo, AgentRepo $agentRepo, UserRepo $userRepo)
    {
        $this->cate_repo = $cateRepo;
        $this->bank_acc_repo = $bankAccRepo;
        $this->agent_repo = $agentRepo;
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
        $params['acc_bank_to_id'] = request('acc_bank_to_id', 0);
        $params['acc_bank_from_id'] = request('acc_bank_from_id', 0);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['date_from'] = request('date_from', null);
        $params['date_to'] = request('date_to', null);
        $params['account_type'] = auth()->user()->account_type;
        $params['type_to'] = request('type_to', null);
        $params['type_from'] = request('type_from', null);
        $params['id_from'] = request('id_from', 0);
        $params['id_to'] = request('id_to', 0);

        $params['date_from'] = str_replace('/', '-', $params['date_from']);
        $params['date_to'] = str_replace('/', '-', $params['date_to']);

        $data = $this->cate_repo->getListing($params, false);
        $total = $this->cate_repo->getListing($params, true);
        $export = $this->cate_repo->getTotal($params, false);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách chuyển tiền',
            'data' => [
                "total_elements" => $total,
                "total_page" => ceil($total / $params['page_size']),
                "page_no" => intval($params['page_index']),
                "page_size" => intval($params['page_size']),
                "data" => $data,
                "total" => $export
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
        $params['acc_bank_from_id'] = request('acc_bank_from_id', null); // ngân hàng
        $params['acc_bank_to_id'] = strtoupper(request('acc_bank_to_id', null)); // hình thức
        $params['type_to'] = request('type_to', null); // máy pos
        $params['type_from'] = request('type_from', null); // máy pos
        $params['price'] = request('price', 0); // phí
        $params['time_payment'] = request('time_payment', null); // trạng thái
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['created_by'] = auth()->user()->id; // trạng thái
        $params['created_name'] = auth()->user()->fullname; // trạng thái

        $bank_from = $this->bank_acc_repo->getById($params['acc_bank_from_id']);
        $params['acc_number_from'] = $bank_from->account_number;
        $params['acc_name_from'] = $bank_from->account_name;
        $params['bank_from'] = $bank_from->bank_code;
        $params['time_payment'] = str_replace('/', '-', $params['time_payment']);


        if ($bank_from->balance < $params['price']) {
            return response()->json([
                'code' => 400,
                'error' => 'Số dư không đủ',
                'data' => null
            ]);
        }

        $bank_to = $this->bank_acc_repo->getById($params['acc_bank_to_id']);
        $params['acc_number_to'] = $bank_to->account_number;
        $params['acc_name_to'] = $bank_to->account_name;
        $params['bank_to'] = $bank_to->bank_code;

        if ($bank_from->type == "AGENCY") {
            $params['from_agent_id'] = $bank_from->agent_id;
        } else if ($bank_from->type == Constants::ACCOUNT_TYPE_STAFF) {
            $params['from_agent_id'] = $bank_from->staff_id;
        }

        if ($bank_to->type == "AGENCY") {
            $params['to_agent_id'] = $bank_to->agent_id;
        } else if ($bank_to->type == Constants::ACCOUNT_TYPE_STAFF) {
            $params['to_agent_id'] = $bank_to->staff_id;
        }

        $resutl = $this->cate_repo->store($params);
        $data = [];
        if ($resutl) {
            //tính tiền nhận được và trừ đi của tk ngân hàng
            $bank_from_balance = $bank_from->balance - $params['price'];
            $data['bank_from_balance'] = $bank_from_balance;
            $data['bank_from_id'] = $bank_from->balance;
            $res = $this->bank_acc_repo->updateBalanceTransfer($params['acc_bank_from_id'], $params['price'], "CREATED_TRANSFER_" . $resutl->id);
            if ($res) {
                if ($bank_from->type == "AGENCY") {
                    $agent = $this->agent_repo->getById($bank_from->agent_id);
                    $agent_balance = $agent->balance + $params['price'];
                    $this->agent_repo->updateBalance($agent->id, $agent_balance, "CREATED_TRANSFER_" . $resutl->id);
                } else if ($bank_from->type == Constants::ACCOUNT_TYPE_STAFF) {
                    $user = $this->userRepo->getById($bank_from->staff_id);
                    $user_balance = $user->balance - $params['price'];
                    $this->userRepo->updateBalance($bank_from->staff_id, $user_balance, "CREATED_TRANSFER_" . $resutl->id);
                }

                $bank_to_balance = $bank_to->balance + $params['price'];
                $data['bank_to_balance'] = $bank_to_balance;
                $this->bank_acc_repo->updateBalance($params['acc_bank_to_id'], $bank_to_balance, "CREATED_TRANSFER_" . $resutl->id);
                if ($bank_to->type == "AGENCY") {
                    $agent = $this->agent_repo->getById($bank_to->agent_id);
                    $agent_balance = $agent->balance - $params['price'];
                    $this->agent_repo->updateBalance($agent->id, $agent_balance, "CREATED_TRANSFER_" . $resutl->id);
                } else if ($bank_to->type == Constants::ACCOUNT_TYPE_STAFF) {
                    $user = $this->userRepo->getById($bank_to->staff_id);
                    $user_balance = $user->balance + $params['price'];
                    $this->userRepo->updateBalance($bank_to->staff_id, $user_balance, "CREATED_TRANSFER_" . $resutl->id);
                }
            }

            return response()->json([
                'code' => 200,
                'error' => 'Thêm mới thành công',
                'data' => $data,
                'params' => $params
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Thêm mới không thành công',
            'data' => null
        ]);
    }

    // public function getAll()
    // {
    //     $data = $this->cate_repo->getAll();
    //     return response()->json([
    //         'code' => 200,
    //         'error' => 'Danh sách danh mục',
    //         'data' => $data
    //     ]);
    // }

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
            $params['acc_bank_from_id'] = request('acc_bank_from_id', null); // ngân hàng
            $params['acc_bank_to_id'] = strtoupper(request('acc_bank_to_id', null)); // hình thức
            $params['type_to'] = request('type_to', null); // máy pos
            $params['type_from'] = request('type_from', null); // máy pos
            $params['price'] = request('price', 0); // phí
            $params['time_payment'] = request('time_payment', null); // trạng thái
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
            $params['created_by'] = auth()->user()->id; // trạng thái
            $params['created_name'] = auth()->user()->fullname; // trạng thái

            $bank_from = $this->bank_acc_repo->getById($params['acc_bank_from_id']);
            $params['acc_number_from'] = $bank_from->account_number;
            $params['acc_name_from'] = $bank_from->account_name;
            $params['bank_from'] = $bank_from->bank_code;

            $params['time_payment'] = str_replace('/', '-', $params['time_payment']);
            if ($bank_from->balance < $params['price']) {
                return response()->json([
                    'code' => 400,
                    'error' => 'Số dư không đủ',
                    'data' => null
                ]);
            }

            $bank_to = $this->bank_acc_repo->getById($params['acc_bank_to_id']);
            $params['acc_number_to'] = $bank_to->account_number;
            $params['acc_name_to'] = $bank_to->account_name;
            $params['bank_to'] = $bank_to->bank_code;

            $transfer_old = $this->cate_repo->getById($params['id']);

            if ($bank_from->type == "AGENCY") {
                $params['from_agent_id'] = $bank_from->agent_id;
            } else if ($bank_from->type == Constants::ACCOUNT_TYPE_STAFF) {
                $params['from_agent_id'] = $bank_from->staff_id;
            }

            if ($bank_to->type == "AGENCY") {
                $params['to_agent_id'] = $bank_to->agent_id;
            } else if ($bank_to->type == Constants::ACCOUNT_TYPE_STAFF) {
                $params['to_agent_id'] = $bank_to->staff_id;
            }

            $resutl = $this->cate_repo->update($params, $params['id']);

            if ($resutl) {
                //tính lại tiền trong trường hợp thay đổi tài khoản gửi
                if ($transfer_old->acc_bank_from_id != $params['acc_bank_from_id']) {
                    //cộng lại tiền cho tk cũ
                    $bank_from_old = $this->bank_acc_repo->getById($transfer_old->acc_bank_from_id);
                    $bank_from_old_balance = $bank_from_old->balance + $transfer_old->price;
                    // $res = $this->bank_acc_repo->updateBalance($transfer_old->acc_bank_from_id, $bank_from_old_balance, "UPDATE_TRANSFER_" . $params['id']);
                    $res = $this->bank_acc_repo->updateBalanceTransfer($transfer_old->acc_bank_from_id, $transfer_old->price*(-1), "UPDATE_TRANSFER_" .$params['id']);
                    if ($res) {
                        // tiền nhận được sẽ trừ đi tiền cũ để lấy số âm làm dư nợ
                        if ($bank_from_old->type == "AGENCY") {
                            $agent_old = $this->agent_repo->getById($bank_from_old->agent_id);
                            $agent_balance = $agent_old->balance - $transfer_old->price;
                            $this->agent_repo->updateBalance($agent_old->id, $agent_balance, "UPDATE_TRANSFER_" . $params['id']);
                        } else if ($bank_from_old->type == Constants::ACCOUNT_TYPE_STAFF) {
                            $user = $this->userRepo->getById($bank_from_old->staff_id);
                            $user_balance = $user->balance + $transfer_old->price;
                            $this->userRepo->updateBalance($bank_from_old->staff_id, $user_balance, "UPDATE_TRANSFER_" . $params['id']);
                        }

                        $bank_from_new = $this->bank_acc_repo->getById($params['acc_bank_from_id']);
                        $bank_from_new_balance = $bank_from_new->balance - $params['price'];
                        // $ress = $this->bank_acc_repo->updateBalance($params['acc_bank_from_id'], $bank_from_new_balance, "UPDATE_TRANSFER_" . $params['id']);
                        $ress = $this->bank_acc_repo->updateBalanceTransfer($params['acc_bank_from_id'], $params['price'], "UPDATE_TRANSFER_" .$params['id']);

                        if ($ress) {
                            // tiền nhận được sẽ trừ đi tiền cũ để lấy số âm làm dư nợ
                            if ($bank_from_new->type == "AGENCY") {
                                $agent_new = $this->agent_repo->getById($bank_from_new->agent_id);
                                $agent_balance = $agent_new->balance + $params['price'];
                                $this->agent_repo->updateBalance($agent_new->id, $agent_balance, "UPDATE_TRANSFER_" . $params['id']);
                            } else if ($bank_from_new->type == Constants::ACCOUNT_TYPE_STAFF) {
                                $user_new = $this->userRepo->getById($bank_from_new->staff_id);
                                $user_new_balance = $user_new->balance - $params['price'];
                                $this->userRepo->updateBalance($bank_from_new->staff_id, $user_new_balance, "UPDATE_TRANSFER_" . $params['id']);
                            }
                        }
                    }
                } else {
                    // $bank_from_balance = $bank_from->balance - $params['price'] + $transfer_old->price;
                    // $this->bank_acc_repo->updateBalance($params['acc_bank_from_id'], $bank_from_balance, "UPDATE_TRANSFER_" . $params['id']);
                    $bank_from_balance =  ($transfer_old->price - $params['price'])*(-1); ;
                    $this->bank_acc_repo->updateBalanceTransfer($params['acc_bank_from_id'], $bank_from_balance, "UPDATE_TRANSFER_" .$params['id']);

                    // tiền nhận được sẽ trừ đi tiền cũ để lấy số âm làm dư nợ
                    if ($bank_from->type == "AGENCY") {
                        $agent = $this->agent_repo->getById($bank_from->agent_id);
                        $agent_balance = $agent->balance - $params['price'] + $transfer_old->price;
                        $this->agent_repo->updateBalance($agent->id, $agent_balance, "UPDATE_TRANSFER_" . $params['id']);
                    } else if ($bank_from->type == Constants::ACCOUNT_TYPE_STAFF) {
                        $user = $this->userRepo->getById($bank_from->staff_id);
                        $user_balance = $user->balance + $params['price'] - $transfer_old->price;
                        $this->userRepo->updateBalance($bank_from->staff_id, $user_balance, "UPDATE_TRANSFER_" . $params['id']);
                    }
                }

                // thay đổi tài khoản nhận tiền
                if ($transfer_old->acc_bank_to_id != $params['acc_bank_to_id']) {
                    $bank_to_old = $this->bank_acc_repo->getById($transfer_old->acc_bank_to_id);
                    $bank_to_old_balance = $bank_to_old->balance - $transfer_old->price;
                    $resup = $this->bank_acc_repo->updateBalance($transfer_old->acc_bank_to_id, $bank_to_old_balance, "UPDATE_TRANSFER_" . $params['id']);

                    if ($resup) {
                        // tiền nhận được sẽ trừ đi tiền cũ để lấy số âm làm dư nợ
                        if ($bank_to_old->type == "AGENCY") {
                            $agent_old = $this->agent_repo->getById($bank_to_old->agent_id);
                            $agent_balance = $agent_old->balance + $transfer_old->price;
                            $this->agent_repo->updateBalance($agent_old->id, $agent_balance, "UPDATE_TRANSFER_" . $params['id']);
                        } else if ($bank_to_old->type == Constants::ACCOUNT_TYPE_STAFF) {
                            $user = $this->userRepo->getById($bank_to_old->staff_id);
                            $user_balance = $user->balance - $transfer_old->price;
                            $this->userRepo->updateBalance($bank_to_old->staff_id, $user_balance, "UPDATE_TRANSFER_" . $params['id']);
                        }

                        $bank_to_new = $this->bank_acc_repo->getById($params['acc_bank_to_id']);
                        $bank_to_new_balance = $bank_to_new->balance + $params['price'];
                        $ep = $this->bank_acc_repo->updateBalance($params['acc_bank_to_id'], $bank_to_new_balance, "UPDATE_TRANSFER_" . $params['id']);
                        if ($ep) {
                            // tiền nhận được sẽ trừ đi tiền cũ để lấy số âm làm dư nợ
                            if ($bank_to_new->type == "AGENCY") {
                                $agent_new = $this->agent_repo->getById($bank_to_new->agent_id);
                                $agent_balance = $agent_new->balance - $params['price'];
                                $this->agent_repo->updateBalance($agent_new->id, $agent_balance, "UPDATE_TRANSFER_" . $params['id']);
                            } else if ($bank_to_new->type == Constants::ACCOUNT_TYPE_STAFF) {
                                $user = $this->userRepo->getById($bank_to_new->staff_id);
                                $user_balance = $user->balance + $params['price'];
                                $this->userRepo->updateBalance($bank_to_new->staff_id, $user_balance, "UPDATE_TRANSFER_" . $params['id']);
                            }
                        }
                    }
                } else {
                    $bank_to_balance = $bank_to->balance + $params['price'] - $transfer_old->price;
                    $this->bank_acc_repo->updateBalance($params['acc_bank_to_id'], $bank_to_balance, "UPDATE_TRANSFER_" . $params['id']);

                    // tiền nhận được sẽ trừ đi tiền cũ để lấy số âm làm dư nợ
                    if ($bank_to->type == "AGENCY") {
                        $agent = $this->agent_repo->getById($bank_to->agent_id);
                        $agent_balance = $agent->balance - $params['price'] + $transfer_old->price;
                        $this->agent_repo->updateBalance($agent->id, $agent_balance, "UPDATE_TRANSFER_" . $params['id']);
                    } elseif ($bank_to->type == Constants::ACCOUNT_TYPE_STAFF) {
                        $user = $this->userRepo->getById($bank_to->staff_id);
                        $user_balance = $user->balance + $params['price'] - $transfer_old->price;
                        $this->userRepo->updateBalance($bank_to->staff_id, $user_balance, "UPDATE_TRANSFER_" . $params['id']);
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
            $transfer = $this->cate_repo->getById($id);
            if ($id == $params['id']) {

                // Return balance to bank account
                $bank_from = $this->bank_acc_repo->getById($transfer->acc_bank_from_id);
                $bank_from_balance = $bank_from->balance + $transfer->price;
                // $res = $this->bank_acc_repo->update($update, $transfer->acc_bank_from_id);
                $res = $this->bank_acc_repo->updateBalanceTransfer($transfer->acc_bank_from_id, $transfer->price*(-1), "DELETE_TRANSFER_" .$params['id']);
                // $res = $this->bank_acc_repo->updateBalance($transfer->acc_bank_from_id, $bank_from_balance, "DELETE_TRANSFER_" . $params['id']);

                if ($res) {
                    if ($bank_from->type == "AGENCY") {
                        $agent = $this->agent_repo->getById($bank_from->agent_id);
                        $agent_balance = $agent->balance - $transfer->price;
                        $this->agent_repo->updateBalance($agent->id, $agent_balance, "DELETE_TRANSFER_" . $params['id']);
                    } else if ($bank_from->type == Constants::ACCOUNT_TYPE_STAFF) {
                        $user = $this->userRepo->getById($bank_from->staff_id);
                        $user_balance = $user->balance + $transfer->price;
                        $this->userRepo->updateBalance($bank_from->staff_id, $user_balance, "DELETE_TRANSFER_" . $params['id']);
                    }

                    $bank_to = $this->bank_acc_repo->getById($transfer->acc_bank_to_id);
                    $bank_to_balance = $bank_to->balance - $transfer->price;
                    $ress = $this->bank_acc_repo->updateBalance($transfer->acc_bank_to_id, $bank_to_balance, "DELETE_TRANSFER_" . $params['id']);

                    if ($ress) {
                        if ($bank_to->type == "AGENCY") {
                            $agent = $this->agent_repo->getById($bank_to->agent_id);
                            $agent_balance = $agent->balance + $transfer->price;
                            $this->agent_repo->updateBalance($agent->id, $agent_balance, "DELETE_TRANSFER_" . $params['id']);
                        } elseif ($bank_to->type == Constants::ACCOUNT_TYPE_STAFF) {
                            $user = $this->userRepo->getById($bank_to->staff_id);
                            $user_balance = $user->balance - $transfer->price;
                            $this->userRepo->updateBalance($bank_to->staff_id, $user_balance, "DELETE_TRANSFER_" . $params['id']);
                        }
                    }
                }
                $data = $this->cate_repo->delete($params);
            } else {
                $data = [
                    'code' => 400,
                    'error' => 'ID không hợp lệ',
                    'data' => null
                ];
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
}
