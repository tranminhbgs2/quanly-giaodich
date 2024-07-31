<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ChangePasswordRequest;
use App\Http\Requests\Customer\DeleteRequest;
use App\Http\Requests\Customer\GetDetailRequest;
use App\Http\Requests\Customer\GetListingRequest;
use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Http\Requests\User\ChangeStatusRequest;
use App\Repositories\BankAccount\BankAccountRepo;
use App\Repositories\Customer\CustomerRepo;
use App\Repositories\Transaction\TransactionRepo;
use App\Repositories\Transfer\TransferRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    protected $user_repo;
    protected $cus_repo;

    public function __construct(UserRepo $cateRepo, CustomerRepo $cusRepo)
    {
        $this->user_repo = $cateRepo;
        $this->cus_repo = $cusRepo;
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/transaction
     *
     * @param GetListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(GetListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        //$params['account_type'] = auth()->user()->account_type;

        $data = $this->cus_repo->getListing($params, false);
        $total = $this->cus_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách User',
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
    public function getInfo()
    {
        $id = Auth::user()->id;
        if ($id) {
            $user = $this->user_repo->getById($id);
            $permissions = $this->user_repo->getUserPermissions($id);
            $res = [
                'id' => $user->id,
                'account_type' => $user->account_type,
                'username' => $user->username,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone' => $user->phone,
                'birthday' => $user->birthday,
                'display_name' => $user->display_name,
                'address' => $user->address,
                'balance' => $user->balance,
                'user_permissions' => $permissions,
            ];

            return response()->json([
                'code' => 200,
                'error' => 'Thông tin user',
                'data' => $res
            ]);
        } else {
            $data = [
                'code' => 422,
                'error' => 'Vui lòng đăng nhập lại',
                'data' => null
            ];
            return response()->json($data);
        }
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
            $data = $this->user_repo->getDetail($params);
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
        $params['username'] = request('username', null);
        $params['password'] = request('password', null);
        $params['fullname'] = request('fullname', null);
        $params['email'] = request('email', null);
        $params['phone'] = request('phone', null);
        $params['birthday'] = request('birthday', null);
        $params['address'] = request('address', null);
        $params['account_type'] = request('account_type', Constants::ACCOUNT_TYPE_STAFF);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
        $params['gender'] = request('gender', "P");
        $params['display_name'] = request('display_name', $params['fullname']);
        // Chuyển đổi định dạng ngày tháng
        $birthday = Carbon::createFromFormat('d/m/Y', $params['birthday'])->format('Y-m-d');

        // Gán lại giá trị vào mảng
        $params['birthday'] = $birthday;
        $resutl = $this->cus_repo->store($params);

        if ($resutl) {
            $this->cus_repo->attachPositions($resutl, request('action_ids', []) ?? []);
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

            $params['fullname'] = request('fullname', null);
            $params['email'] = request('email', null);
            $params['phone'] = request('phone', null);
            $params['address'] = request('address', null);
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
            $params['gender'] = request('gender', "P");
            $params['display_name'] = request('display_name', $params['fullname']);
            $params['account_type'] = request('account_type', Constants::ACCOUNT_TYPE_STAFF);
            $params['birthday'] = request('birthday', null);
            // Chuyển đổi định dạng ngày tháng
            $birthday = Carbon::createFromFormat('d/m/Y', $params['birthday'])->format('Y-m-d');

            // Gán lại giá trị vào mảng
            $params['birthday'] = $birthday;

            $action_ids = request('action_ids', []) ?? [];
            $resutl = $this->cus_repo->update($params, $params['id']);

            if ($resutl) {
                //Xóa các permission cũ
                $this->cus_repo->detachAllPositions($params['id']);
                $this->cus_repo->attachPositions($params['id'], $action_ids);
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
                $data = $this->user_repo->delete($params);
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

        $resutl = $this->user_repo->changeStatus($params['status'], $params['id']);

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

    public function getAllStaff()
    {
        $data = $this->user_repo->getAllStaff();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách nhân viên',
            'data' => $data
        ]);
    }

    /**
     * API thay đổi mật khẩu
     * URL: {{url}}/api/v1/me/change-password
     *
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $params['password'] = request('password', null);

        $result = $this->cus_repo->changePassword($params, Auth::user());

        if ($result) {
            return response()->json([
                'code' => 200,
                'error' => 'Thay đổi mật khẩu thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
            'data' => null
        ]);
    }

    public function syncBalance()
    {
        //Lấy tổng số tiền đã được chuyển khoản và số tiền đã chuyển khoản đi
        $transfer = new TransferRepo();
        $bank_acc = new BankAccountRepo();
        $tran_repo = new TransactionRepo();
        $users = $this->user_repo->getAllStaff();
        $data = [];
        foreach ($users as $user) {
            $total_to = $transfer->getBalanceTransferStaff($user['id']);
            $total_from = $transfer->getBalanceTransferStaff($user['id'], "FROM");
            $balance = $total_to - $total_from;

            $total_price_nop = $tran_repo->getPriceNop($user['id']);
            $total_price_transfer = $tran_repo->getPriceTransfer($user['id']);
            $total_transfer = $total_price_nop + $total_price_transfer;
            $balance_new = $balance - $total_transfer;

            if ($balance_new != $user['balance']) {
                $data[] = ['user_name' => $user['fullname'], 'balance_old' => $user['balance'], '$balance_new' => $balance_new ];
                $this->user_repo->updateBalance($user['id'], $balance_new, "SYNC_BALANCE_USER");
            }

            $bank_account = $bank_acc->getAccountStaff($user['id']);
            if ($bank_account && $bank_account->balance != $balance_new) {
                $data[] = ['user_name' => $bank_account->account_name, 'balance_old' => $bank_account->balance, '$balance_new' => $balance_new ];
                $bank_acc->updateBalance($bank_account->id, $balance_new, "SYNC_BALANCE_BANK_". $bank_account->id);
            }
        }
            return response()->json([
                'code' => 200,
                'error' => 'Đồng bộ số dư nhân viên thành công',
                'data' => $data
            ]);
    }
}
