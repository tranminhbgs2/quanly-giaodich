<?php

namespace App\Repositories\WithdrawPos;

use App\Events\ActionLogEvent;
use App\Helpers\Constants;
use App\Models\BankAccounts;
use App\Models\Pos;
use App\Models\WithdrawPos;
use App\Repositories\BankAccount\BankAccountRepo;
use App\Repositories\BaseRepo;
use App\Repositories\HoKinhDoanh\HoKinhDoanhRepo;
use App\Repositories\Pos\PosRepo;
use Carbon\Carbon;

class WithdrawPosRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getListing($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $hkd_id = $params['hkd_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = WithdrawPos::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name', 'surrogate']);
            },
            'accountBank' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_code', 'account_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('price_withdraw', 'LIKE', "%" . $keyword . "%");
            });
        }

        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        //     $query->where('created_by', $created_by);
        // }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_withdraw', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
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


    public function getTotal($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $hkd_id = $params['hkd_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = WithdrawPos::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name', 'surrogate']);
            },
            'accountBank' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_code', 'account_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('price_withdraw', 'LIKE', "%" . $keyword . "%");
            });
        }

        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        //     $query->where('created_by', $created_by);
        // }

        if ($date_from && $date_to) {
            $query->whereBetween('time_withdraw', [$date_from, $date_to]);
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }


        // Tính tổng của từng trường cần thiết
        $total = [
            'price_withdraw' => (int)$query->sum('price_withdraw'),
        ];

        return $total;
    }

    public function store($params)
    {
        $fillable = [
            'pos_id',
            'hkd_id',
            'time_withdraw',
            'account_bank_id',
            'price_withdraw',
            'status',
            'created_by',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['hkd_id']) && !empty($insert['price_withdraw'])) {
            $res = WithdrawPos::create($insert);
            if ($res) {
                $hkd_repo = new HoKinhDoanhRepo();
                $hkd = $hkd_repo->getById($insert['hkd_id']);
                $bank_acc = BankAccounts::where('id', $insert['account_bank_id'])->first();
                if ($hkd && $bank_acc) {
                    $price_bank = $bank_acc->balance + $insert['price_withdraw'];
                    $bank_acc = new BankAccountRepo();
                    $bank_acc->updateBalance($insert['account_bank_id'], $price_bank, "WITHDRAWPOS_CREATE_" . $res->id);

                    $balance = $hkd->balance - $insert['price_withdraw'];
                    $hkd_repo->updateBalance($balance, $hkd->id, "WITHDRAWPOS_CREATE_" . $res->id);
                }
            }

            return $res ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'pos_id',
            'hkd_id',
            'time_withdraw',
            'account_bank_id',
            'price_withdraw',
            'status',
            'created_by',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }
        $pos_withdraw = WithdrawPos::where('id', $id)->first();
        $price_withdraw_old = $pos_withdraw->price_withdraw;
        $bank_acc_id = $pos_withdraw->account_bank_id;
        $res = $pos_withdraw->update($update);
        if ($res) {
            if ($price_withdraw_old != $update['price_withdraw']) {
                $hkd_repo = new HoKinhDoanhRepo();
                $hkd = $hkd_repo->getById($update['hkd_id']);
                if ($hkd) {
                    // update số tiền máy pos
                    $price_pos = $hkd->balance - ($params['price_withdraw'] - $price_withdraw_old);
                    $hkd_repo->updateBalance($price_pos, $hkd->id, "WITHDRAWPOS_UPDATE_" . $id);
                }
            }
            if ($bank_acc_id != $update['account_bank_id']) {
                $bank_acc_old = BankAccounts::where('id', $bank_acc_id)->first();
                $bank_acc_new = BankAccounts::where('id', $update['account_bank_id'])->first();
                if ($bank_acc_old && $bank_acc_new) {
                    //update số tiền TKHT cũ
                    $price_bank_old = $bank_acc_old->balance - $price_withdraw_old;
                    $bank_acc = new BankAccountRepo();
                    $bank_acc->updateBalance($bank_acc_id, $price_bank_old, "WITHDRAWPOS_UPDATE" . $id);

                    //update số tiền TKHT mới
                    $price_bank = $bank_acc_new->balance + $update['price_withdraw'];
                    $bank_acc->updateBalance($update['account_bank_id'], $price_bank, "WITHDRAWPOS_UPDATE_" . $id);
                }
            } else {
                $bank_acc = BankAccounts::where('id', $update['account_bank_id'])->first();
                if ($bank_acc) {
                    //update số tiền TKHT
                    $price_bank = $bank_acc->balance + $update['price_withdraw'] - $price_withdraw_old;
                    $bank_acc = new BankAccountRepo();
                    $bank_acc->updateBalance($update['account_bank_id'], $price_bank, "WITHDRAWPOS_UPDATE_" . $id);
                }
            }
        }
        return $res;
    }

    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $withdrawPos = WithdrawPos::select()->where('id', $id)->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name', 'surrogate']);
            },
            'accountBank' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_code', 'account_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            },
        ])->first();

        if ($withdrawPos) {
            return [
                'code' => 200,
                'error' => 'Thông tin chi tiết',
                'data' => $withdrawPos
            ];
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin chi tiết ',
                'data' => null
            ];
        }
    }

    public function getById($id)
    {
        $withdrawPos = WithdrawPos::select()->where('id', $id)->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
            },
            'accountBank' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email']);
            },
        ])->first();

        return $withdrawPos;
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $withdrawPos = WithdrawPos::where('id', $id)->first();
        $price_withdraw_old = $withdrawPos->price_withdraw;

        if ($withdrawPos) {
            if ($withdrawPos->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Giao dịch đã bị xóa',
                    'data' => null
                ];
            } else {
                $withdrawPos->status = Constants::USER_STATUS_DELETED;
                $withdrawPos->deleted_at = Carbon::now();

                if ($withdrawPos->save()) {
                    $hkd_repo = new HoKinhDoanhRepo();
                    $hkd = $hkd_repo->getById($withdrawPos->hkd_id);
                    $bank_acc = BankAccounts::where('id', $withdrawPos->account_bank_id)->first();
                    if ($hkd && $bank_acc) {
                        $price_bank = $bank_acc->balance - $price_withdraw_old;
                        $bank_acc = new BankAccountRepo();
                        $bank_acc->updateBalance($withdrawPos->account_bank_id, $price_bank, "WITHDRAWPOS_DELETE_" . $id);

                        $price_pos = $hkd->balance + $price_withdraw_old;
                        $hkd_repo->updateBalance($price_pos, $hkd->id, "WITHDRAWPOS_DELETE_" . $id);
                    }
                    return [
                        'code' => 200,
                        'error' => 'Xóa rút tiền pos thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'error' => 'Xóa rút tiền pos không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin rút tiền',
                'data' => null
            ];
        }
    }

    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return WithdrawPos::where('id', $id)->update($update);
    }


    public function getAll($hkd_id = null)
    {
        $query = WithdrawPos::select()->with([
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name', 'surrogate']);
            },
            'accountBank' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_code', 'account_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            },
        ]);;

        if ($hkd_id) {
            $query->where('hkd_id', $hkd_id);
        }

        return $query->orderBy('id', 'DESC')->get()->toArray();
    }

    public function getTotalByHkd($hkd_id, $params = [])
    {
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $query = WithdrawPos::select()->where('hkd_id', $hkd_id)->where('status', Constants::USER_STATUS_ACTIVE);

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                // $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                // $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_withdraw', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        $total = (int)$query->sum('price_withdraw');

        return $total;
    }

    public function getListByHkd($params = [])
    {
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;

        // Khởi tạo câu truy vấn
        $query = WithdrawPos::where('hkd_id', $params['hkd_id'])
            ->where('status', Constants::USER_STATUS_ACTIVE);

        // Áp dụng điều kiện ngày tháng nếu có
        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                // $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                // $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_withdraw', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Xử lý khi định dạng ngày tháng không hợp lệ
                return [
                    'error' => 'Invalid date format',
                ];
            }
        }

        // Sắp xếp kết quả theo thời gian rút tiền giảm dần
        $query->orderBy('time_withdraw', 'ASC');

        // Lấy kết quả từ câu truy vấn
        $results = $query->get();

        // Nhóm kết quả theo ngày sử dụng Carbon
        $groupedResults = $results->groupBy(function ($item) {
            return Carbon::parse($item->time_withdraw)->format('Y-m-d');
        });

        return $groupedResults->toArray();
    }
}
