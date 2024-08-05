<?php

namespace App\Repositories\BankAccount;

use App\Events\ActionLogEvent;
use App\Models\BankAccounts;
use App\Helpers\Constants;
use App\Repositories\BaseRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankAccountRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $bank_code = $params['bank_code'] ?? null;
        $agent_id = $params['agent_id'] ?? null;
        $staff_id = $params['staff_id'] ?? null;
        $type = $params['type'] ?? null;

        $query = BankAccounts::select()->with('agency')->with('staff');


        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('account_name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('account_name', 'LIKE', "%" . $keyword . "%");
            });
        }
        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('created_at', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        if ($bank_code) {
            $query->where('bank_code', $bank_code);
        }

        if ($agent_id) {
            $query->where('agent_id', $agent_id);
        }

        if ($staff_id) {
            $query->where('staff_id', $staff_id);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($is_counting) {
            return $query->count();
        } else {
            $offset = ($page_index - 1) * $page_size;
            if ($page_size > 0 && $offset >= 0) {
                $query->take($page_size)->skip($offset);
            }
        }

        $query->orderBy('id', 'DESC');

        return $query->get()->toArray();
    }

    public function store($params)
    {
        $fillable = [
            'agent_id',
            'bank_code',
            'account_number',
            'account_name',
            'balance',
            'balances',
            'status',
            'staff_id',
            'type',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['account_number']) && !empty($insert['bank_code']) && !empty($insert['account_name'])) {
            return BankAccounts::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'agent_id',
            'bank_code',
            'account_number',
            'account_name',
            'balance',
            'balances',
            'status',
            'staff_id',
            'type',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        Log::info('$bank->balance: update ' . $update['balance'] . ' $id: ' . $id);
        return BankAccounts::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $bankAccount = BankAccounts::find($id);

        if ($bankAccount) {
            $bankAccount->status = Constants::USER_STATUS_DELETED;
            $bankAccount->deleted_at = Carbon::now();

            if ($bankAccount->save()) {
                return [
                    'code' => 200,
                    'error' => 'Xóa tài khoản ngân hàng thành công',
                    'data' => null
                ];
            } else {
                return [
                    'code' => 400,
                    'error' => 'Xóa tài khoản ngân hàng không thành công',
                    'data' => null
                ];
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy tài khoản ngân hàng',
                'data' => null
            ];
        }
    }

    public function deleteByAgent($agent_id)
    {
        $bankAccounts = BankAccounts::where('agent_id', $agent_id)->get();


        if (count($bankAccounts) > 0) {
            foreach ($bankAccounts as $bankAccount) {
                $bankAccount->status = Constants::USER_STATUS_DELETED;
                $bankAccount->deleted_at = Carbon::now();
                $bankAccount->save();
            }
        }
        return true;
    }

    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getDetail($params, $with_trashed = false)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $tran = BankAccounts::select()->with('agency')->with('staff')->where('id', $id);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        $data = $tran->first();

        if ($data) {

            return [
                'code' => 200,
                'error' => 'Thông tin chi tiết',
                'data' => $data
            ];
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin chi tiết ',
                'data' => null
            ];
        }
    }

    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getById($id, $with_trashed = false)
    {
        $tran = BankAccounts::select()->with('agency')->with('staff')->where('id', $id);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return BankAccounts::where('id', $id)->update($update);
    }

    public function getAll($type = null, $agent_id = 0, $staff_id = 0)
    {
        $query = BankAccounts::select('id', 'bank_code', 'account_name', 'account_number', 'type')->where('status', Constants::USER_STATUS_ACTIVE);

        if ($type) {
            $query->where('type', $type);
        }

        if ($agent_id > 0) {
            $query->where('agent_id', $agent_id);
        }

        if ($staff_id > 0) {
            $query->where('staff_id', $staff_id);
        }

        return $query->orderBy('id', 'DESC')->get()->toArray();
    }

    public function updateBalance($id, $balance, $action = "")
    {
        Log::info('Dữ liệu đầu vào - ID: ' . $id . ', Balance: ' . $balance. ', Action: ' . $action);

        $bank = BankAccounts::find($id);

        if (!$bank) {
            Log::error('Không tìm thấy tài khoản ngân hàng với ID: ' . $id);
            return false;
        }

        // Ghi log trước khi thực hiện cập nhật
        Log::info('Trước khi cập nhật - Số dư cũ: ' . $bank->balance . ', Số dư mới: ' . $balance . ', ID: ' . $id);

        // Thực hiện cập nhật

        // Ghi log sau khi cập nhật
        Log::info('Sau khi cập nhật - ID: ' . $id . ', Số dư mới: ' . $balance);

        // Lưu log qua event
        event(new ActionLogEvent([
            'actor_id' => auth()->user()->id ?? 0,
            'username' => auth()->user()->username ?? 'unknown',
            'action' => 'UPDATE_BALANCE_ACC_BANK',
            'description' => $action . ' Cập nhật số tiền cho TKHT ' . $bank->account_number . ' từ ' . $bank->balance . ' thành ' . $balance,
            'data_new' => $balance,
            'data_old' => $bank->balance,
            'model' => 'BankAccounts',
            'table' => 'bank_accounts',
            'record_id' => $id,
            'ip_address' => request()->ip()
        ]));

        // Thực hiện cập nhật bằng câu lệnh SQL
        $bank->update(['balances' => $balance, 'balance' => $balance]);

        // Tải lại dữ liệu từ database để kiểm tra
        $bank->refresh();

        // Ghi log giá trị sau khi tải lại từ database
        Log::info('Giá trị sau khi tải lại từ database - ID: ' . $id . ', Số dư: ' . $bank->balance);

        return true;
    }

    public function updateBalanceTransfer($id, $price_transfer, $action = "")
    {
        Log::info('Dữ liệu đầu vào updateBalanceTransfer - ID: ' . $id . ', price_transfer: ' . $price_transfer);

        $bank = BankAccounts::find($id);

        if (!$bank) {
            Log::error('Không tìm thấy tài khoản ngân hàng với ID: ' . $id);
            return false;
        }
        $bank_balance = $bank->balances;
        $balance = $bank_balance - $price_transfer;
        // Ghi log trước khi thực hiện cập nhật
        Log::info('Trước khi cập nhật - Số dư cũ: ' . $bank_balance . ', Số dư mới: ' . $balance . ', ID: ' . $id);

        // Ghi log sau khi cập nhật
        Log::info('Sau khi cập nhật - ID: ' . $id . ', Số dư mới: ' . $balance);

        // Lưu log qua event
        event(new ActionLogEvent([
            'actor_id' => auth()->user()->id ?? 0,
            'username' => auth()->user()->username ?? '0',
            'action' => 'UPDATE_BALANCE_ACC_BANK',
            'description' => $action . ' Cập nhật số tiền cho TKHT ' . $bank->account_number . ' từ ' . $bank_balance . ' thành ' . $balance,
            'data_new' => $balance,
            'data_old' => $bank_balance,
            'model' => 'BankAccounts',
            'table' => 'bank_accounts',
            'record_id' => $id,
            'ip_address' => request()->ip()
        ]));

        // Thực hiện cập nhật bằng câu lệnh SQL
        $bank->update(['balances' => $balance, 'balance' => $balance]);

        // Tải lại dữ liệu từ database để kiểm tra
        $bank->refresh();

        // Ghi log giá trị sau khi tải lại từ database
        Log::info('Giá trị sau khi tải lại từ database updateBalanceTransfer - ID: ' . $id . ', Số dư: ' . $bank->balances);

        return true;
    }

    public function checkAccount($account_name, $account_number, $bank_code)
    {
        $dep = BankAccounts::where('account_name', $account_name)
            ->where('account_number', $account_number)
            ->where('bank_code', $bank_code)
            ->first();

        return $dep;
    }

    public function getAccountFee()
    {
        $dep = BankAccounts::where('type', 'FEE')->first();

        return $dep;
    }
    public function getAccountStaff($id)
    {
        $dep = BankAccounts::where('type', Constants::ACCOUNT_TYPE_STAFF)->where('staff_id', $id)->where('status', Constants::USER_STATUS_ACTIVE)->first();

        return $dep;
    }
    public function getAccountAgency($id)
    {
        $dep = BankAccounts::where('type', Constants::ACCOUNT_TYPE_AGENCY)->where('agent_id', $id)->where('status', Constants::USER_STATUS_ACTIVE)->get()->toArray();

        return $dep;
    }
}
