<?php

namespace App\Repositories\WithdrawPos;

use App\Helpers\Constants;
use App\Models\WithdrawPos;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class WithdrawPosRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getListing($params, $is_counting = false)
    {
        // $keyword = $params['keyword'] ?? null;
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

        // if (!empty($keyword)) {
        //     $keyword = translateKeyWord($keyword);
        //     $query->where(function ($sub_sql) use ($keyword) {
        //         $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%");
        //     });
        // }

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if ($date_from && $date_to && $date_from <= $date_to && !empty($date_from) && !empty($date_to)){
            try {
                $date_from = Carbon::createFromFormat('Y-m-d', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d', $date_to)->endOfDay();
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
        // $keyword = $params['keyword'] ?? null;
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

        // if (!empty($keyword)) {
        //     $keyword = translateKeyWord($keyword);
        //     $query->where(function ($sub_sql) use ($keyword) {
        //         $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%");
        //     });
        // }

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if ($date_from && $date_to) {
            $query->whereBetween('time_withdraw', [$date_from, $date_to]);
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($status >= 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }


        // Tính tổng của từng trường cần thiết
        $total = [
            'price_withdraw' => $query->sum('price_withdraw'),
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

        if (!empty($insert['pos_id']) && !empty($insert['price_withdraw'])) {
            return WithdrawPos::create($insert) ? true : false;
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

        return WithdrawPos::where('id', $id)->update($update);
    }

    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
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
        $withdrawPos = WithdrawPos::where('id', $id)->withTrashed()->first();

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
}
