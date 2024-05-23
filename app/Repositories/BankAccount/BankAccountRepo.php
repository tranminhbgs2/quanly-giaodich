<?php

namespace App\Repositories\BankAccount;

use App\Events\ActionLogEvent;
use App\Models\BankAccounts;
use App\Helpers\Constants;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

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

        $query = BankAccounts::select()->with('agency');


        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('account_name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('account_name', 'LIKE', "%" . $keyword . "%");
            });
        }
        if ($date_from && $date_to && $date_from <= $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d', $date_to)->endOfDay();
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
            'status'
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
            'status'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

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

    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getDetail($params, $with_trashed = false)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $tran = BankAccounts::where('id', $id);

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
        $tran = BankAccounts::where('id', $id);

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

    public function getAll()
    {
        return BankAccounts::select('id', 'bank_code', 'account_name', 'account_number')->where('status', Constants::USER_STATUS_ACTIVE)->orderBy('id', 'DESC')->get()->toArray();
    }

    public function updateBalance($id, $balance, $action = "")
    {
        $bank = BankAccounts::where('id', $id)->withTrashed()->first();
        // Lưu log qua event
        event(new ActionLogEvent([
            'actor_id' => auth()->user()->id,
            'username' => auth()->user()->username,
            'action' => 'UPDATE_BANLANCE_ACC_BANK',
            'description' => $action. ' Cập nhật số tiền cho TKHT ' . $bank->account_number . ' từ ' . $bank->balance . ' thành ' . $balance,
            'data_new' => $balance,
            'data_old' => $bank->balance,
            'model' => 'BankAccounts',
            'table' => 'bank_accounts',
            'record_id' => $id,
            'ip_address' => request()->ip()
        ]));

        $update = ['balance' => $balance];
        return $bank->update($update);
    }
}
