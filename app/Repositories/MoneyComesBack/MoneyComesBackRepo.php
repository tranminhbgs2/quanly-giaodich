<?php

namespace App\Repositories\MoneyComesBack;

use App\Events\ActionLogEvent;
use App\Models\MoneyComesBack;
use App\Helpers\Constants;
use App\Models\Pos;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class MoneyComesBackRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $agent_id = $params['agent_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = MoneyComesBack::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'user' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            }
        ]);

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if ($date_from && $date_to && $date_from <= $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d', $date_to)->endOfDay();
                $query->whereBetween('time_end', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($lo_number > 0) {
            $query->where('lo_number', $lo_number);
        }


        if ($agent_id > 0) {
            $query->where('agent_id', $agent_id);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
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

    /**
     * Tạo GD lô tiền về
     */
    public function store($params)
    {
        $fillable = [
            'agent_id',
            'pos_id',
            'lo_number',
            'time_end',
            'time_process',
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['pos_id']) && !empty($insert['payment'])) {
            $res = MoneyComesBack::create($insert) ? true : false;
            // Xử lý cộng tiền máy Pos
            if ($res) {
                $pos = Pos::where('id', $insert['pos_id']);
                if ($pos) {
                    $pos_balance = $pos->balance + $insert['payment'];
                    // Lưu log qua event
                    event(new ActionLogEvent([
                        'actor_id' => auth()->user()->id,
                        'username' => auth()->user()->username,
                        'action' => 'UPDATE_BANLANCE_POS',
                        'description' => 'Cập nhật số tiền cho máy Pos ' . $pos->name . ' từ ' . $pos->balance . ' thành ' . $pos_balance,
                        'data_new' => $pos_balance,
                        'data_old' => $pos->balance,
                        'model' => 'Pos',
                        'table' => 'pos',
                        'record_id' => $pos->id,
                        'ip_address' => request()->ip()
                    ]));

                    $pos->balance = $pos_balance;
                    $pos->save();
                }
            }
            return $res;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'agent_id',
            'pos_id',
            'lo_number',
            'time_end',
            'time_process', // 'time_process' => 'date_format:Y-m-d'
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }
        $old_money = MoneyComesBack::where('id', $id)->first();
        $balance_change = $params['payment'] - $old_money->payment;
        $res = MoneyComesBack::where('id', $id)->update($update);
        // Xử lý cộng tiền máy Pos
        if ($res) {

            // Lưu log qua event
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id,
                'username' => auth()->user()->username,
                'action' => 'UPDATE_BANLANCE_POS',
                'description' => 'Cập nhật số tiền lô tiền về ' . $old_money->lo_number . ' từ ' . $old_money->payment . ' thành ' . $params['payment'],
                'data_new' => $old_money->payment,
                'data_old' => $params['payment'],
                'model' => 'MoneyComesBack',
                'table' => 'money_comes_back',
                'record_id' => $id,
                'ip_address' => request()->ip()
            ]));

            $pos = Pos::where('id', $params['pos_id']);
            if ($pos) {
                $pos_balance = $pos->balance + $balance_change;
                // Lưu log qua event
                event(new ActionLogEvent([
                    'actor_id' => auth()->user()->id,
                    'username' => auth()->user()->username,
                    'action' => 'UPDATE_BANLANCE_POS',
                    'description' => 'Cập nhật số tiền cho máy Pos ' . $pos->name . ' từ ' . $pos->balance . ' thành ' . $pos_balance,
                    'data_new' => $pos_balance,
                    'data_old' => $pos->balance,
                    'model' => 'Pos',
                    'table' => 'pos',
                    'record_id' => $pos->id,
                    'ip_address' => request()->ip()
                ]));

                $pos->balance = $pos_balance;
                $pos->save();
            }
        }
        return $res;
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $moneyComesBack = MoneyComesBack::where('id', $id)->withTrashed()->first();
        $balance_change = $moneyComesBack->payment;

        if ($moneyComesBack) {
            if ($moneyComesBack->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Lô tiền về đã bị xóa',
                    'data' => null
                ];
            } else {
                $moneyComesBack->status = Constants::USER_STATUS_DELETED;
                $moneyComesBack->deleted_at = Carbon::now();

                if ($moneyComesBack->save()) {
                    // Xóa lô tiền về thì trừ đi tiền pos tồn
                    $pos = Pos::where('id', $params['pos_id']);
                    if ($pos && $pos->balance >= $balance_change) {
                        $pos_balance = $pos->balance - $balance_change;
                        // Lưu log qua event
                        event(new ActionLogEvent([
                            'actor_id' => auth()->user()->id,
                            'username' => auth()->user()->username,
                            'action' => 'UPDATE_BANLANCE_POS',
                            'description' => 'Cập nhật số tiền cho máy Pos ' . $pos->name . ' từ ' . $pos->balance . ' thành ' . $pos_balance,
                            'data_new' => $pos_balance,
                            'data_old' => $pos->balance,
                            'model' => 'Pos',
                            'table' => 'pos',
                            'record_id' => $pos->id,
                            'ip_address' => request()->ip()
                        ]));

                        $pos->balance = $pos_balance;
                        $pos->save();
                    }
                    return [
                        'code' => 200,
                        'error' => 'Xóa lô tiền về thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'error' => 'Xóa lô tiền về không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy giao dịch',
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
        $tran = MoneyComesBack::select()->where('id', $id);
        $tran->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'user' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            }
        ]);

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
    public function getByLoTime($params, $with_trashed = false)
    {
        $lo_number = isset($params['lo_number']) ? $params['lo_number'] : 0;
        $time_process = isset($params['time_process']) ? $params['time_process'] : 0;
        $tran = MoneyComesBack::where('lo_number', $lo_number, 'time_process', $time_process);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getById($id, $with_trashed = false)
    {
        $tran = MoneyComesBack::where('id', $id);
        $tran->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'user' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            }
        ]);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return MoneyComesBack::where('id', $id)->update($update);
    }
}
