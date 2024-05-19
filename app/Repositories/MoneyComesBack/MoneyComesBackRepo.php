<?php

namespace App\Repositories\MoneyComesBack;

use App\Models\MoneyComesBack;
use App\Helpers\Constants;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class MoneyComesBackRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $agency_id = $params['agency_id'] ?? 0;
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

        if ($date_from && $date_to) {
            $query->whereBetween('time_end', [$date_from, $date_to]);
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($agency_id > 0) {
            $query->where('agency_id', $agency_id);
        }

        if ($status >= 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
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
            'agency_id',
            'pos_id',
            'lo_number',
            'time_end',
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['agency_id']) && !empty($insert['pos_id']) && !empty($insert['total_price'])) {
            return MoneyComesBack::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'agency_id',
            'pos_id',
            'lo_number',
            'time_end',
            'created_by',
            'fee',
            'total_price',
            'payment',
            'balance',
            'status'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return MoneyComesBack::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $moneyComesBack = MoneyComesBack::where('id', $id)->withTrashed()->first();

        if ($moneyComesBack) {
            if ($moneyComesBack->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'message' => 'Lô tiền về đã bị xóa',
                    'data' => null
                ];
            } else {
                $moneyComesBack->status = Constants::USER_STATUS_DELETED;
                $moneyComesBack->deleted_at = Carbon::now();

                if ($moneyComesBack->save()) {
                    return [
                        'code' => 200,
                        'message' => 'Xóa lô tiền về thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'message' => 'Xóa lô tiền về không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'message' => 'Không tìm thấy giao dịch',
                'data' => null
            ];
        }
    }
}
