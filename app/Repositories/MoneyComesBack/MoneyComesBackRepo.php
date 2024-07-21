<?php

namespace App\Repositories\MoneyComesBack;

use App\Events\ActionLogEvent;
use App\Models\MoneyComesBack;
use App\Helpers\Constants;
use App\Models\Agent;
use App\Models\Pos;
use App\Repositories\Agent\AgentRepo;
use App\Repositories\BaseRepo;
use App\Repositories\HoKinhDoanh\HoKinhDoanhRepo;
use App\Repositories\Pos\PosRepo;
use App\Repositories\Transfer\TransferRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MoneyComesBackRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false, $is_agent = false)
    {
        $keyword = $params['keyword'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $hkd_id = $params['hkd_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = MoneyComesBack::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'bank_code']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            },
            'user' => function ($sql) {
                $sql->select(['id', 'status', 'username', 'email', 'fullname']);
            },
            'hkd' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            }
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }
        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        $query->where('total_price', '>', 0);
        // }
        if ($status == Constants::USER_STATUS_ACTIVE) {
            if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
                try {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                    if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                    }
                    $query->whereBetween('time_end', [$date_from, $date_to]);
                } catch (\Exception $e) {
                    // Handle invalid date format
                }
            }
        } else {
            if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
                try {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                    if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                    }
                    $query->whereBetween('created_at', [$date_from, $date_to]);
                } catch (\Exception $e) {
                    // Handle invalid date format
                }
            }
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($lo_number > 0) {
            $query->where('lo_number', $lo_number);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        // $query->whereNull('agent_id');

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

        $query->orderBy('lo_number', 'ASC');

        return $query->get()->toArray();
    }

    public function getListingAgent($params, $is_counting = false, $is_agent = false)
    {
        $keyword = $params['keyword'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $agent_id = $params['agent_id'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = MoneyComesBack::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'bank_code']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            },
            'user' => function ($sql) {
                $sql->select(['id', 'status', 'username', 'email', 'fullname']);
            }
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();

                if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                }
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


        $query->whereNotNull('agent_id');
        if ($agent_id > 0) {
            $query->where('agent_id', $agent_id);
        } else {
            $query->where('agent_id', '!=', 0);
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

        $query->orderBy('lo_number', 'ASC');

        return $query->get()->toArray();
    }

    /**
     * Hàm lấy ds gi, có tìm kiếm và phân trang
     *
     * @param $params
     * @param false $is_counting
     *
     * @return mixed
     */
    public function getListingCashBack($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $pos_id = $params['pos_id'] ?? 0;

        $query = MoneyComesBack::select(["total_price", "time_process as time_payment", "pos_id"])->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_process', [$date_from, $date_to]);
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

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        $query->where('total_price', '>', 0);
        $query->orderBy('id', 'DESC');
        // Lấy kết quả và nhóm theo pos_id và ngày
        $transactions = $query->with('pos')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->pos_id . '_' . Carbon::parse($transaction->time_payment)->format('Y-m-d');
            })
            ->map(function ($group) {
                $pos = $group->first()->pos;
                $date = Carbon::parse($group->first()->time_payment)->format('Y-m-d');
                $total_price_rut = $group->sum('total_price');
                $total_payment_cashback = intval($total_price_rut * $pos->fee_cashback / 100);

                return [
                    'pos_id' => $pos->id,
                    'date' => $date,
                    'total_price_rut' => $total_price_rut,
                    'total_payment_cashback' => $total_payment_cashback,
                    'pos' => [
                        'id' => $pos->id,
                        'name' => $pos->name,
                        'fee' => $pos->fee,
                        'total_fee' => $pos->total_fee,
                        'fee_cashback' => $pos->fee_cashback
                    ]
                ];
            })
            ->values();

        // $results = $query->get();
        // Tính toán tổng số lượng kết quả đã nhóm
        $total = $transactions->count();

        // Nếu đang đếm số lượng, trả về tổng số lượng
        if ($is_counting) {
            return $total;
        }

        // Xử lý phân trang trên kết quả đã nhóm
        $offset = ($page_index - 1) * $page_size;
        $pagedTransactions = $transactions->slice($offset, $page_size);

        return $pagedTransactions->values();
    }

    /**
     * Hàm lấy tổng số giao dịch
     *
     * @param $params
     * @return array
     */
    public function getTotalCashBack($params)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $pos_id = $params['pos_id'] ?? 0;

        $query = MoneyComesBack::select(["total_price", "time_process as time_payment", "pos_id"])->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_process', [$date_from, $date_to]);
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

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        $query->where('total_price', '>', 0);
        $query->orderBy('id', 'DESC');


        // Lấy kết quả và nhóm theo pos_id và ngày
        $transactions = $query->with('pos')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->pos_id . '_' . Carbon::parse($transaction->time_payment)->format('Y-m-d');
            })
            ->map(function ($group) {
                $pos = $group->first()->pos;
                $date = Carbon::parse($group->first()->time_payment)->format('Y-m-d');
                $total_price_rut = $group->sum('total_price');
                $total_payment_cashback = $total_price_rut * $pos->fee_cashback / 100;

                return [
                    'pos_id' => $pos->id,
                    'date' => $date,
                    'total_price_rut' => $total_price_rut,
                    'total_payment_cashback' => $total_payment_cashback,
                    'pos' => [
                        'id' => $pos->id,
                        'name' => $pos->name,
                        'fee' => $pos->fee,
                        'total_fee' => $pos->total_fee,
                        'fee_cashback' => $pos->fee_cashback
                    ]
                ];
            })
            ->values();

        // Tính tổng total_payment_cashback
        $total_cashback_sum = $transactions->sum('total_payment_cashback');
        // Tính tổng của từng trường cần thiết
        $total = [
            'payment_cashback' => $total_cashback_sum
        ];

        return $total;
    }

    /**
     * Tạo GD lô tiền về
     */
    public function store($params)
    {
        $fillable = [
            'agent_id',
            'pos_id',
            'hkd_id',
            'lo_number',
            'time_end',
            'time_process',
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status',
            'fee_agent',
            'payment_agent',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['pos_id']) && !empty($insert['hkd_id']) && !empty($insert['payment'])) {
            $res = MoneyComesBack::create($insert);
            // Xử lý cộng tiền máy Pos
            if ($res) {
                $pos = Pos::where('id', $insert['pos_id'])->first();
                if ($pos) {
                    $pos_balance = $pos->price_pos + ($insert['total_price'] - ($res->fee * $insert['total_price']) / 100);
                    $pos_repo = new PosRepo();
                    $pos_repo->updatePricePos($pos_balance, $pos->id, "CREATE_MONEY_COMES_BACK_" . $res->id);
                }
                $hkd_repo = new HoKinhDoanhRepo();
                $hkd = $hkd_repo->getById($insert['hkd_id']);
                if ($hkd) {
                    $hkd_balance = $hkd->balance + ($insert['total_price'] - ($res->fee * $insert['total_price']) / 100);
                    $hkd_repo->updateBalance($hkd_balance, $hkd->id, "CREATE_MONEY_COMES_BACK_" . $res->id);
                }
                if (isset($insert['agent_id']) && $insert['agent_id'] > 0) {
                    $agent = Agent::where('id', $insert['agent_id'])->first();
                    if ($agent) {
                        $agent_balance = $agent->balance - $insert['payment_agent'];
                        $agent_repo = new AgentRepo();
                        $agent_repo->updateBalance($agent->id, $agent_balance, "CREATE_MONEY_COMES_BACK_" . $res->id);
                    }
                }
            }
            return $res ? true : false;
        }

        return false;
    }

    public function update($params, $id, $total_price_hkd = 0)
    {
        $fillable = [
            'agent_id',
            'pos_id',
            'hkd_id',
            'lo_number',
            'time_end',
            'time_process', // 'time_process' => 'date_format:Y/m/d'
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status',
            'fee_agent',
            'payment_agent',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }
        $old_money = MoneyComesBack::where('id', $id)->first();
        $balance_change =  $old_money->payment_agent - $params['payment'];
        $res = MoneyComesBack::where('id', $id)->update($update);
        // Xử lý cộng tiền máy Pos
        if ($res) {

            // Lưu log qua event
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id ?? 0,
                'username' => auth()->user()->username ?? 0,
                'action' => 'UPDATE_BANLANCE_MONEY_COMES_BACK',
                'description' => 'Cập nhật số tiền lô tiền về ' . $old_money->lo_number . ' từ ' . $old_money->total_price . ' thành ' . $params['total_price'],
                'data_new' => $params['total_price'],
                'data_old' => $old_money->total_price,
                'model' => 'MoneyComesBack',
                'table' => 'money_comes_back',
                'record_id' => $id,
                'ip_address' => request()->ip()
            ]));

            if ($params['pos_id'] != $old_money->pos_id) {
                $pos_old = Pos::where('id', $old_money->pos_id)->first();
                if ($pos_old) {
                    $pos_balance = $pos_old->price_pos - ($old_money->total_price - ($old_money->fee * $old_money->total_price) / 100);
                    $pos_repo = new PosRepo();
                    $pos_repo->updatePricePos($pos_balance, $pos_old->id, "UPDATE_MONEY_COMES_BACK_" . $id);
                }

                $pos = Pos::where('id', $params['pos_id'])->first();
                if ($pos) {
                    $pos_balance = $pos->price_pos + ($params['total_price'] - ($params['fee'] * $params['total_price']) / 100);
                    $pos_repo = new PosRepo();
                    $pos_repo->updatePricePos($pos_balance, $pos->id, "UPDATE_MONEY_COMES_BACK_" . $id);
                }
            } else {
                $pos = Pos::where('id', $params['pos_id'])->first();
                if ($pos) {
                    $pos_balance = $pos->price_pos - ($old_money->total_price - ($old_money->fee * $old_money->total_price) / 100) + ($params['total_price'] - ($old_money->fee * $params['total_price']) / 100);
                    $pos_repo = new PosRepo();
                    $pos_repo->updatePricePos($pos_balance, $pos->id, "UPDATE_MONEY_COMES_BACK_" . $id);
                }
            }

            if ($params['hkd_id'] != $old_money->hkd_id) {
                $hkd_repo = new HoKinhDoanhRepo();
                $hkd_old = $hkd_repo->getById($old_money->hkd_id);
                $pos_old = Pos::where('id', $old_money->pos_id)->first();
                if ($hkd_old) {
                    $hkd_balance = $hkd_old->balance - ($old_money->total_price - ($old_money->fee * $old_money->total_price) / 100);
                    $hkd_repo = new HoKinhDoanhRepo();
                    $hkd_repo->updateBalance($hkd_balance, $hkd_old->id, "UPDATE_MONEY_COMES_BACK_" . $id);
                }

                $hkd = $hkd_repo->getById($params['hkd_id']);
                $pos = Pos::where('id', $params['pos_id'])->first();
                if ($hkd) {
                    $hkd_balance = $hkd->balance + ($params['total_price'] - ($params['fee'] * $params['total_price']) / 100);
                    $hkd_repo = new HoKinhDoanhRepo();
                    $hkd_repo->updateBalance($hkd_balance, $hkd->id, "UPDATE_MONEY_COMES_BACK_" . $id);
                }
            } else {
                $hkd_repo = new HoKinhDoanhRepo();
                $hkd = $hkd_repo->getById($params['hkd_id']);
                $pos = Pos::where('id', $params['pos_id'])->first();
                if ($hkd) {
                    $hkd_balance = $hkd->balance - ($old_money->total_price - ($old_money->fee * $old_money->total_price) / 100) + ($params['total_price'] - ($old_money->fee * $params['total_price']) / 100);
                    $hkd_repo = new HoKinhDoanhRepo();
                    $hkd_repo->updateBalance($hkd_balance, $hkd->id, "UPDATE_MONEY_COMES_BACK_" . $id);
                }
            }

            if (isset($params['agent_id']) && $params['agent_id'] > 0) {
                if ($params['agent_id'] != $old_money->agent_id) {
                    $agent_old = Agent::where('id', $old_money->agent_id)->first();
                    if ($agent_old) {
                        $agent_balance = $agent_old->balance + $old_money->payment_agent;
                        $agent_repo = new AgentRepo();
                        $agent_repo->updateBalance($agent_old->id, $agent_balance, "UPDATE_MONEY_COMES_BACK_" . $id);
                    }

                    $agent = Agent::where('id', $params['agent_id'])->first();
                    if ($agent) {
                        $agent_balance = $agent->balance - $params['payment_agent'];
                        $agent_repo = new AgentRepo();
                        $agent_repo->updateBalance($agent->id, $agent_balance, "UPDATE_MONEY_COMES_BACK_" . $id);
                    }
                } else {
                    $agent = Agent::where('id', $params['agent_id'])->first();
                    if ($agent) {
                        $agent_balance = $agent->balance + $balance_change;
                        $agent_repo = new AgentRepo();
                        $agent_repo->updateBalance($agent->id, $agent_balance, "UPDATE_MONEY_COMES_BACK_" . $id);
                    }
                }
            }
        }
        return $res;
    }


    public function updateKL($params, $id, $total_price_hkd = 0, $type = "CREATED")
    {
        $fillable = [
            'pos_id',
            'hkd_id',
            'lo_number',
            'time_end',
            'time_process', // 'time_process' => 'date_format:Y/m/d'
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status',
            'fee_agent',
            'payment_agent',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }
        $old_money = MoneyComesBack::where('id', $id)->first();

        $res = MoneyComesBack::where('id', $id)->update($update);
        // Xử lý cộng tiền máy Pos
        if ($res) {
            // Lưu log qua event
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id ?? 0,
                'username' => auth()->user()->username ?? 0,
                'action' => 'UPDATE_BANLANCE_MONEY_COMES_BACK',
                'description' => 'Cập nhật số tiền lô tiền về KL ' . $old_money->lo_number . ' từ ' . $old_money->total_price . ' thành ' . $params['total_price'],
                'data_new' => $params['total_price'],
                'data_old' => $old_money->total_price,
                'model' => 'MoneyComesBack',
                'table' => 'money_comes_back',
                'record_id' => $id,
                'ip_address' => request()->ip()
            ]));


            $pos = Pos::where('id', $params['pos_id'])->first();
            if ($pos) {
                $pos_balance = $pos->price_pos + $total_price_hkd;
                $pos_repo = new PosRepo();
                $pos_repo->updatePricePos($pos_balance, $pos->id, "UPDATE_KL_MONEY_COMES_BACK_" . $id);
            }


            $hkd_repo = new HoKinhDoanhRepo();
            $hkd = $hkd_repo->getById($params['hkd_id']);
            $pos = Pos::where('id', $params['pos_id'])->first();
            if ($hkd) {
                $hkd_balance = $hkd->balance + $total_price_hkd;
                $hkd_repo = new HoKinhDoanhRepo();
                $hkd_repo->updateBalance($hkd_balance, $hkd->id, "UPDATE_KL_MONEY_COMES_BACK_" . $id);
            }
        }
        return $res;
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $moneyComesBack = MoneyComesBack::where('id', $id)->first();

        if ($moneyComesBack) {
            if ($moneyComesBack->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Lô tiền về đã bị xóa',
                    'data' => null
                ];
            } else {
                $balance_change = $moneyComesBack->payment;
                $moneyComesBack->status = Constants::USER_STATUS_DELETED;
                $moneyComesBack->deleted_at = Carbon::now();

                if ($moneyComesBack->save()) {
                    // Xóa lô tiền về thì trừ đi tiền pos tồn
                    $pos = Pos::where('id', $moneyComesBack->pos_id)->first();
                    if ($pos) {
                        $pos_balance = $pos->price_pos - ($moneyComesBack->total_price - ($moneyComesBack->fee * $moneyComesBack->total_price) / 100);
                        $pos_repo = new PosRepo();
                        $pos_repo->updatePricePos($pos_balance, $pos->id, "DELETE_MONEY_COMES_BACK_" . $id);
                    }
                    $hkd_repo = new HoKinhDoanhRepo();
                    $hkd = $hkd_repo->getById($moneyComesBack->hkd_id);
                    if ($hkd) {
                        $hkd_balance = $hkd->price_pos - ($moneyComesBack->total_price - ($moneyComesBack->fee * $moneyComesBack->total_price) / 100);
                        $hkd_repo->updateBalance($hkd_balance, $hkd->id, "DELETE_MONEY_COMES_BACK_" . $id);
                    }
                    $agent_old = Agent::where('id', $moneyComesBack->agent_id)->first();
                    if ($agent_old) {
                        $agent_balance = $agent_old->balance - $moneyComesBack->payment_agent;
                        $agent_repo = new AgentRepo();
                        $agent_repo->updateBalance($agent_old->id, $agent_balance, "UPDATE_MONEY_COMES_BACK_" . $id);
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
        $pos_id = isset($params['pos_id']) ? $params['pos_id'] : 0;
        $time_process = isset($params['time_process']) ? $params['time_process'] : null;
        $tran = MoneyComesBack::where('lo_number', $lo_number);
        $tran->where('agent_id', 0);
        $tran->where('pos_id', $pos_id);
        if ($time_process) {
            $tran->where('time_process', $time_process);
        }
        return $tran->first();
    }

    public function getByTimeProcess($time_process, $with_trashed = false)
    {
        $tran = MoneyComesBack::where('agent_id', 0);
        try {
            $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $time_process, 'UTC')->startOfDay();
            $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $time_process, 'UTC')->endOfDay();
            $tran->whereBetween('updated_at', [$date_from, $date_to]);
        } catch (\Exception $e) {
            // Handle invalid date format
        }
        return $tran->get()->toArray();
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


    public function getTotal($params, $is_counting = false, $is_agent = false)
    {
        $keyword = $params['keyword'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $hkd_id = $params['hkd_id'] ?? 0;
        $agent_id = $params['agent_id'] ?? 0;

        $query = MoneyComesBack::select();

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($status == Constants::USER_STATUS_ACTIVE) {
            if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
                try {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                    if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                    }
                    $query->whereBetween('time_end', [$date_from, $date_to]);
                } catch (\Exception $e) {
                    // Handle invalid date format
                }
            }
        } else {
            if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
                try {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                    if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                    }
                    $query->whereBetween('created_at', [$date_from, $date_to]);
                } catch (\Exception $e) {
                    // Handle invalid date format
                }
            }
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($lo_number > 0) {
            $query->where('lo_number', $lo_number);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }


        $query->where('total_price', '>', 0);
        // $query->whereNull('agent_id');

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        // Tính tổng của từng trường cần thiết
        $total = [
            'total_price' => (int)$query->sum('total_price'),
            'total_payment' => (int)$query->sum('payment'),
            'total_payment_agent' => (int)$query->sum('payment_agent'),
        ];

        return $total;
    }

    public function getTotalAgent($params, $is_counting = false, $is_agent = false)
    {
        $keyword = $params['keyword'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $agent_id = $params['agent_id'] ?? 0;

        $query = MoneyComesBack::select();

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {

                // if (Carbon::parse($date_from)->format('H:i:s') == '00:00:00') {
                //     $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->endOfDay();
                // }
                if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                }
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

        $query->whereNotNull('agent_id');

        if ($agent_id > 0) {
            $query->where('agent_id', $agent_id);
        } else {
            $query->where('agent_id', '!=', 0);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        // Tính tổng của từng trường cần thiết
        $total = [
            'total_price' => (int)$query->sum('total_price'),
            'total_payment' => (int)$query->sum('payment'),
            'total_payment_agent' => (int)$query->sum('payment_agent'),
            'total_profit' => $query->sum('payment') - $query->sum('payment_agent'),
            'total_cash' => 0
        ];
        return $total;
    }

    public function ReportDashboardAgent($params)
    {
        $date_from = $params['date_from'] ?? Carbon::now()->startOfDay();
        $date_to = $params['date_to'] ?? Carbon::now()->endOfDay();

        $date_from = Carbon::parse($date_from)->startOfDay();
        $date_to = Carbon::parse($date_to)->endOfDay();

        $query = DB::table('money_comes_back')
            ->select(
                DB::raw('SUM(total_price) as total_price'),
                DB::raw('SUM(payment) as payment'),
                DB::raw('SUM(total_price * (fee_agent - fee) / 100) as profit')
            )
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->whereNotNull('agent_id')
            ->where('agent_id', '!=', 0)
            ->whereBetween('time_end', [$date_from, $date_to]);

        if (auth()->user()->account_type !== Constants::ACCOUNT_TYPE_SYSTEM) {
            $query->where('created_by', auth()->user()->id);
        }

        $totals = $query->first();

        // Convert the results to integer
        $total_san_luong = isset($totals->total_price) ? (int)$totals->total_price : 0;
        $total_tien_nhan = isset($totals->payment) ? (int)$totals->payment : 0;
        $total_profit = isset($totals->profit) ? (int)$totals->profit : 0;

        return [
            'san_luong' => $total_san_luong,
            'tien_nhan' => $total_tien_nhan,
            'profit' => $total_profit
        ];
    }

    public function chartDashboardAgent($params)
    {
        $date_from = Carbon::parse($params['date_from'])->startOfDay();
        $date_to = Carbon::parse($params['date_to'])->endOfDay();

        $date_from = Carbon::parse($date_from)->startOfDay();
        $date_to = Carbon::parse($date_to)->endOfDay();

        // Tạo một đối tượng Collection mới chứa các ngày trong khoảng thời gian đã chỉ định
        $date_range = collect();
        $current_date = $date_from->copy();
        while ($current_date->lessThanOrEqualTo($date_to)) {
            $date_range->push($current_date->toDateString());
            $current_date->addDay();
        }

        // Truy vấn dữ liệu từ cơ sở dữ liệu
        $query = MoneyComesBack::select(['total_price', 'fee_agent', 'fee', 'created_at'])
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->whereNotNull('agent_id')
            ->where('agent_id', '!=', 0)
            ->whereBetween('time_end', [$date_from, $date_to])
            ->get()
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->created_at)->toDateString();
            });

        // Merge các ngày trong khoảng thời gian đã chỉ định với các ngày trong kết quả truy vấn
        $date_range = $date_range->merge($query->keys());

        // Đảm bảo không có ngày trùng lặp và sắp xếp lại danh sách các ngày
        $date_range = $date_range->unique()->sort();

        // Điền vào danh sách ngày không có dữ liệu và tính tổng hợp
        $total = ['total_price_rut' => 0, 'total_profit' => 0];
        $result = $date_range->map(function ($date) use ($query, &$total) {
            $total_price_rut = $query->has($date) ? $query[$date]->sum('total_price') : 0;
            $total_profit = $query->has($date)
                ? $query[$date]->sum(function ($transaction) {
                    return $transaction->total_price * ($transaction->fee_agent - $transaction->fee) / 100;
                })
                : 0;
            $total['total_price_rut'] += $total_price_rut; // Cộng tổng sản lượng vào biến tổng hợp
            $total['total_profit'] += $total_profit; // Cộng tổng lợi nhuận vào biến tổng hợp
            // Định dạng lại ngày theo định dạng d/m/Y
            $formatted_date = Carbon::parse($date)->format('d/m/Y');
            return [
                'date' => $formatted_date,
                'total_price_rut' => $total_price_rut,
                'total_profit' => round($total_profit, 2)
            ];
        });

        // Trả về mảng gồm data và total
        return ['data' => $result, 'total' => $total];
    }

    public function ketToanLo($id, $time_process, $time_end)
    {
        if (empty($time_end)) {
            $status = Constants::USER_STATUS_LOCKED;
        } else {
            $status = Constants::USER_STATUS_ACTIVE;
        }

        $update = [
            'time_process' => $time_process,
            'time_end' => $time_end,
            'status' => $status
        ];

        return MoneyComesBack::where('id', $id)->update($update);
    }
    public function getTopAgency($params)
    {
        $date_from = Carbon::parse($params['date_from'] ?? Carbon::now()->startOfDay())->startOfDay();
        $date_to = Carbon::parse($params['date_to'] ?? Carbon::now()->endOfDay())->endOfDay();

        $transferRepo = new TransferRepo();

        // Query to get all relevant transactions
        $transactions = MoneyComesBack::select(['agent_id', 'total_price', 'payment', 'payment_agent', 'fee_agent', 'fee', 'created_at'])
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->whereNotNull('agent_id')
            ->where('agent_id', '!=', 0)
            ->whereBetween('time_end', [$date_from, $date_to])
            ->get()
            ->groupBy('agent_id');

        // Get all agents involved in the transactions
        $agentIds = $transactions->keys();
        $agents = Agent::whereIn('id', $agentIds)->get()->keyBy('id');

        $result = $transactions->map(function ($group) use ($agents, $transferRepo, $date_from, $date_to) {
            $total_price_rut = $group->sum('total_price');
            $total_payment = $group->sum('payment_agent');
            $total_profit = $group->sum(function ($transaction) {
                return $transaction->total_price * ($transaction->fee_agent - $transaction->fee) / 100;
            });

            // Get total transfer amount
            $total_transfer_data_to = $transferRepo->getTotalAgent([
                'agent_id' => $group->first()->agent_id,
                'agent_date_from' => $date_from,
                'agent_date_to' => $date_to,
                'type' => 'TO'
            ]);
            $total_transfer_data_from = $transferRepo->getTotalAgent([
                'agent_id' => $group->first()->agent_id,
                'agent_date_from' => $date_from,
                'agent_date_to' => $date_to,
                'type' => 'FROM'
            ]);

            $row_total_transfer = (int)($total_transfer_data_to['total_transfer'] - $total_transfer_data_from['total_transfer']);
            $total_cash = $total_payment - $row_total_transfer;

            $agent = $agents[$group->first()->agent_id];

            return [
                'agent_id' => $group->first()->agent_id,
                'name' => $agent->name ?? 'N/A',
                'total_price_rut' => $total_price_rut,
                'total_payment' => $total_payment,
                'total_profit' => (int)$total_profit,
                'total_cash' => $total_cash,
                'total_transfer' => (int)$row_total_transfer
            ];
        })->sortByDesc('total_price_rut')->values();

        return $result;
    }

    public function getListingAllAgent($params, $is_counting = false, $is_agent = false)
    {
        $keyword = $params['keyword'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $agent_id = $params['agent_id'] ?? 0;

        $query = MoneyComesBack::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'bank_code']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            },
            'user' => function ($sql) {
                $sql->select(['id', 'status', 'username', 'email', 'fullname']);
            }
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                // if (Carbon::parse($date_from)->format('H:i:s') == '00:00:00') {
                //     $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->endOfDay();
                // }
                if (Carbon::parse($date_to)->format('H:i:s') == '00:00:00') {
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                }
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

        $query->whereNotNull('agent_id');
        if ($agent_id > 0) {
            $query->where('agent_id', $agent_id);
        } else {
            $query->where('agent_id', '!=', 0);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        $query->orderBy('id', 'DESC');

        return $query->get()->toArray();
    }

    public function updateSync($params, $id)
    {
        $fillable = [
            'total_price',
            'payment',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }
        $old_money = MoneyComesBack::where('id', $id)->first();

        $res = MoneyComesBack::where('id', $id)->update($update);
        // Lưu log qua event
        event(new ActionLogEvent([
            'actor_id' => auth()->user()->id ?? 0,
            'username' => auth()->user()->username ?? 0,
            'action' => 'UPDATE_BANLANCE_MONEY_COMES_BACK',
            'description' => 'SYNC_BALANCE_MONEY lô tiền về ' . $old_money->lo_number . ' từ ' . $old_money->total_price . ' thành ' . $update['total_price'],
            'data_new' => $update['total_price'],
            'data_old' => $old_money->total_price,
            'model' => 'MoneyComesBack',
            'table' => 'money_comes_back',
            'record_id' => $id,
            'ip_address' => request()->ip()
        ]));
        return $res ? true : false;
    }
    public function getTotalPriceByHkd($hkd_id, $params = [])
    {
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $is_all = $params['is_all'] ?? false;
        $is_ket_toan = $params['is_ket_toan'] ?? false;

        // Lấy tất cả các bản ghi theo hkd_id và status
        $query = MoneyComesBack::where('hkd_id', $hkd_id)
            ->where('status', '!=', Constants::USER_STATUS_DELETED);

        // Áp dụng điều kiện ngày tháng nếu có

        if ($is_all) {

            if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
                try {
                    // $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                    // $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                    $query->whereBetween('time_end', [$date_from, $date_to]);
                } catch (\Exception $e) {
                    // Handle invalid date format
                }
            }
            if ($is_ket_toan) {
                $query->whereNotNull('time_end');
            }
        } else {
            if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
                try {
                    // $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                    // $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                    $query->whereBetween('created_at', [$date_from, $date_to]);
                } catch (\Exception $e) {
                    // Handle invalid date format
                }
            }
        }

        // Lấy kết quả của truy vấn
        $records = $query->get();

        // Khởi tạo biến tổng số tiền
        $totalBalance = 0;

        // Lặp qua từng bản ghi để tính tổng tiền
        foreach ($records as $record) {
            // Tính toán tổng tiền theo công thức
            $hkdBalance = $record->total_price - ($record->fee * $record->total_price) / 100;
            $totalBalance += $hkdBalance;
        }

        return $totalBalance;
    }


    public function getListingAllHkd($params, $is_counting = false, $is_agent = false)
    {
        $keyword = $params['keyword'] ?? null;
        $lo_number = $params['lo_number'] ?? 0;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $hkd_id = $params['hkd_id'] ?? 0;
        $agent_id = $params['agent_id'] ?? 0;

        $query = MoneyComesBack::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'bank_code']);
            },
            'agency' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            },
            'hkd' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            }
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                // $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                // $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
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

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($agent_id > 0) {
            $query->where('agent_id', $agent_id);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }
        $query->where('total_price', '>', 0);
        $query->orderBy('lo_number', 'ASC');
        $results = $query->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d'); // Group by date
        });

        return $results->toArray();
    }
    public function getTotalHkd($params, $is_counting = false, $is_agent = false)
    {
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $hkd_id = $params['hkd_id'] ?? 0;

        $query = MoneyComesBack::select();

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                // $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                // $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_end', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        // Tính tổng của từng trường cần thiết
        $total = [
            'total_price' => (int)$query->sum('total_price'),
            'total_payment' => (int)$query->sum('payment'),
            'total_payment_agent' => (int)$query->sum('payment_agent'),
            'total_cash' => 0
        ];
        return $total;
    }


    /**
     * Hàm danh sách lô tiền về chưa kết toán
     *
     * @param $params
     */
    public function getByTimeEnd($time_process)
    {
        $tran = MoneyComesBack::where('time_process', $time_process);

        $tran->whereNull('time_end');
        $tran->where('status', Constants::USER_STATUS_LOCKED);

        return $tran->get()->toArray();
    }

    public function updateNote($note, $id)
    {
        $update = [
            'note' => $note
        ];

        return MoneyComesBack::where('id', $id)->update($update);
    }

    public function getProfitsMoney($startDate, $endDate)
    {
        return MoneyComesBack::selectRaw('DATE(time_end) as date, SUM(total_price * (fee_agent - fee) / 100) as profit_money')
            ->whereBetween('time_end', [$startDate, $endDate])
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->where('agent_id', '!=', 0)
            ->groupBy('date')
            ->get();
    }
}
