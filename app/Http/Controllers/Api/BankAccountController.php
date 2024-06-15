<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccount\ChangeStatusRequest;
use App\Http\Requests\BankAccount\DeleteRequest;
use App\Http\Requests\BankAccount\GetDetailRequest;
use App\Http\Requests\BankAccount\ListingRequest;
use App\Http\Requests\BankAccount\StoreRequest;
use App\Http\Requests\BankAccount\UpdateRequest;
use App\Repositories\BankAccount\BankAccountRepo;

class BankAccountController extends Controller
{
    protected $bankacc_repo;

    public function __construct(BankAccountRepo $bankaccRepo)
    {
        $this->bankacc_repo = $bankaccRepo;
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/bank-account
     *
     * @param ListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(ListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['agent_id'] = request('agent_id', null);
        $params['bank_code'] = request('bank_code', null);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = auth()->user()->account_type;

        $data = $this->bankacc_repo->getListing($params, false);
        $total = $this->bankacc_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Tài khoản hưởng thụ',
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
     * API lấy thông tin chi tiết
     * URL: {{url}}/api/v1/bank-account/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(GetDetailRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            $data = $this->bankacc_repo->getDetail($params);
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
     * URL: {{url}}/api/v1/bank-account/store
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params['account_name'] = strtoupper(request('account_name', null));
        $params['account_number'] = request('account_number', null);
        $params['bank_code'] = request('bank_code', null);
        $params['agent_id'] = request('agent_id', null);
        $params['balance'] = request('balance', 0);
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['account_name'] = unsigned($params['account_name']);
        $params['type'] = request('type', null);
        $params['staff_id'] = request('staff_id', null);

        switch ($params['type']) {
            case Constants::ACCOUNT_TYPE_STAFF:
                $params['agent_id'] = 0;
                break;
            case 'AGENCY':
                $params['staff_id'] = 0;
                break;
            case 'FEE':
                $params['agent_id'] = 0;
                $params['staff_id'] = 0;
                break;
            case 'MASTER':
                $params['agent_id'] = 0;
                $params['staff_id'] = 0;
                break;
        }
        $resutl = $this->bankacc_repo->store($params);

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
            $params['account_name'] = request('account_name', null);
            $params['account_number'] = request('account_number', null);
            $params['bank_code'] = request('bank_code', null);
            $params['agent_id'] = request('agent_id', null);
            $params['balance'] = request('balance', 0);
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
            $params['account_name'] = unsigned($params['account_name']);
            $params['type'] = request('type', null);
            $params['staff_id'] = request('staff_id', null);

            switch ($params['type']) {
                case Constants::ACCOUNT_TYPE_STAFF:
                    $params['agent_id'] = 0;
                    break;
                case 'AGENCY':
                    $params['staff_id'] = 0;
                    break;
                case 'FEE':
                    $params['agent_id'] = 0;
                    $params['staff_id'] = 0;
                    break;
                case 'MASTER':
                    $params['agent_id'] = 0;
                    $params['staff_id'] = 0;
                    break;
            }

            $resutl = $this->bankacc_repo->update($params, $params['id']);

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
                $data = $this->bankacc_repo->delete($params);
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
        $params['status'] = request('status', null);

        $resutl = $this->bankacc_repo->changeStatus($params['status'], $params['id']);

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
        $type = request('type', null);
        $agent_id = request('agent_id', 0);
        $staff_id = request('staff_id', 0);
        $data = $this->bankacc_repo->getAll($type, $agent_id, $staff_id);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Tài khoản hưởng thụ',
            'data' => $data
        ]);
    }
}
