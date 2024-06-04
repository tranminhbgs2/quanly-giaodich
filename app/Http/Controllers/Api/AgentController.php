<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\ChangeStatusRequest;
use App\Http\Requests\Agent\DeleteRequest;
use App\Http\Requests\Agent\GetDetailRequest;
use App\Http\Requests\Agent\ListingRequest;
use App\Http\Requests\Agent\StoreRequest;
use App\Http\Requests\Agent\UpdateRequest;
use App\Repositories\Agent\AgentRepo;
use App\Repositories\BankAccount\BankAccountRepo;

class AgentController extends Controller
{
    protected $agent_repo;
    protected $bankAccountRepo;

    public function __construct(AgentRepo $agentRepo, BankAccountRepo $bankAccountRepo)
    {
        $this->agent_repo = $agentRepo;
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
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);
        $params['account_type'] = auth()->user()->account_type;

        $data = $this->agent_repo->getListing($params, false);
        $total = $this->agent_repo->getListing($params, true);
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Đại lý',
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
            $data = $this->agent_repo->getDetail($params);
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
        $params['name'] = request('name', null); // ngân hàng
        $params['surrogate'] = strtoupper(request('surrogate', null)); // hình thức
        $params['phone'] = request('phone', 0); // máy pos
        $params['address'] = request('address', null); // phí
        $params['status'] = request('status', Constants::USER_STATUS_ACTIVE); // trạng thái
        $params['manager_id'] = auth()->user()->id; // người tạo
        $params['account_banks'] = request('account_banks', []); // phí
        $insert_banks = [];
        if (count($params['account_banks']) > 0) {
            foreach ($params['account_banks'] as $value) {
                $account_name = strtoupper($value['account_name']);
                $account_number = $value['account_number'];
                $bank_code = $value['bank_code'];
                $account_name = unsigned($account_name);

                if (empty($account_name) || empty($account_number) || empty($bank_code)) {

                    return response()->json([
                        'code' => 400,
                        'error' => 'Một hoặc nhiều trường thông tin tài khoản không được để trống',
                        'data' => null
                    ]);
                }

                $check_bank = $this->bankAccountRepo->checkAccount($account_name, $account_number, $bank_code);
                if ($check_bank) {
                    return response()->json([
                        'code' => 400,
                        'error' => 'Tài khoản '.$account_name .' đã tồn tại',
                        'data' => null
                    ]);
                }

                // Kiểm tra tài khoản đã tồn tại trong $insert_banks hay chưa
                $exists_in_insert_banks = array_filter($insert_banks, function ($existing_bank) use ($account_name, $account_number, $bank_code) {
                    return $existing_bank['account_name'] === $account_name &&
                        $existing_bank['account_number'] === $account_number &&
                        $existing_bank['bank_code'] === $bank_code;
                });

                if (!empty($exists_in_insert_banks)) {
                    return response()->json([
                        'code' => 200,
                        'error' => 'Tài khoản ' . $account_name . ' đã tồn tại trong danh sách thêm mới.',
                        'data' => null
                    ]);
                }
                // Thêm tài khoản vào mảng $insert_banks
                $insert_banks[] = [
                    'account_name' => $account_name,
                    'account_number' => $account_number,
                    'bank_code' => $bank_code,
                    'agent_id' => 0,
                    'balance' => 0,
                    'status' => Constants::USER_STATUS_ACTIVE
                ];
            }
        }

        $id = $this->agent_repo->store($params);

        if ($id > 0) {
            if (count($insert_banks) > 0) {
                foreach ($insert_banks as $key => $value) {
                    $value['agent_id'] = $id;
                    $this->bankAccountRepo->store($value);
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

            $params['name'] = request('name', null); // ngân hàng
            $params['surrogate'] = strtoupper(request('surrogate', null)); // hình thức
            $params['phone'] = request('phone', 0); // máy pos
            $params['address'] = request('address', null); // phí
            $params['status'] = request('status', Constants::USER_STATUS_ACTIVE);
            $params['manager_id'] = auth()->user()->id; // người tạo
            $params['account_banks'] = request('account_banks', []); // phí
            $insert_banks = [];

            $this->bankAccountRepo->deleteByAgent($params['id']);

            if (count($params['account_banks']) > 0) {
                foreach ($params['account_banks'] as $value) {
                    $account_name = strtoupper($value['account_name']);
                    $account_number = $value['account_number'];
                    $bank_code = $value['bank_code'];
                    $account_name = unsigned($account_name);

                    if (empty($account_name) || empty($account_number) || empty($bank_code)) {

                        return response()->json([
                            'code' => 400,
                            'error' => 'Một hoặc nhiều trường thông tin tài khoản không được để trống',
                            'data' => null
                        ]);
                    }

                    $check_bank = $this->bankAccountRepo->checkAccount($account_name, $account_number, $bank_code);
                    if ($check_bank) {
                        return response()->json([
                            'code' => 400,
                            'error' => 'Tài khoản '.$account_name .' đã tồn tại',
                            'data' => null
                        ]);
                    }

                    // Kiểm tra tài khoản đã tồn tại trong $insert_banks hay chưa
                    $exists_in_insert_banks = array_filter($insert_banks, function ($existing_bank) use ($account_name, $account_number, $bank_code) {
                        return $existing_bank['account_name'] === $account_name &&
                            $existing_bank['account_number'] === $account_number &&
                            $existing_bank['bank_code'] === $bank_code;
                    });

                    if (!empty($exists_in_insert_banks)) {
                        return response()->json([
                            'code' => 200,
                            'error' => 'Tài khoản ' . $account_name . ' đã tồn tại trong danh sách thêm mới.',
                            'data' => null
                        ]);
                    }
                    // Thêm tài khoản vào mảng $insert_banks
                    $insert_banks[] = [
                        'account_name' => $account_name,
                        'account_number' => $account_number,
                        'bank_code' => $bank_code,
                        'agent_id' => 0,
                        'balance' => 0,
                        'status' => Constants::USER_STATUS_ACTIVE
                    ];
                }
            }
            $resutl = $this->agent_repo->update($params, $params['id']);

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
                $data = $this->agent_repo->delete($params);
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

        $resutl = $this->agent_repo->changeStatus($params['status'], $params['id']);

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
        $data = $this->agent_repo->getAll();
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách Đại lý',
            'data' => $data
        ]);
    }
}
