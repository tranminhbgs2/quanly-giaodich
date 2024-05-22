<?php

namespace App\Repositories\Transfer;

use App\Helpers\Constants;
use App\Models\Transfer;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class TransferRepo extends BaseRepo
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
        $acc_bank_from_id = $params['acc_bank_from_id'] ?? 0;
        $acc_bank_to_id = $params['acc_bank_to_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = Transfer::select()->with([
            'bankFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'bankTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('acc_name_from', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('acc_name_to', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('acc_number_from', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('acc_number_to', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if ($date_from && $date_to && $date_from <= $date_to && !empty($date_from) && !empty($date_to)){
            try {
                $date_from = Carbon::createFromFormat('Y-m-d', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($acc_bank_from_id > 0) {
            $query->where('acc_bank_from_id', $acc_bank_from_id);
        }

        if ($acc_bank_to_id > 0) {
            $query->where('acc_bank_to_id', $acc_bank_to_id);
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

    public function store($params)
    {
        $fillable = [
            'acc_bank_from_id',
            'acc_number_from',
            'acc_name_from',
            'acc_bank_to_id',
            'acc_number_to',
            'acc_name_to',
            'bank_to',
            'bank_from',
            'type_to',
            'time_payment',
            'created_by',
            'price',
            'status',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['acc_bank_from_id']) && !empty($insert['acc_bank_to_id'])) {
            return Transfer::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'acc_bank_from_id',
            'acc_number_from',
            'acc_name_from',
            'acc_bank_to_id',
            'acc_number_to',
            'acc_name_to',
            'bank_to',
            'bank_from',
            'type_to',
            'time_payment',
            'created_by',
            'price',
            'status',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return Transfer::where('id', $id)->update($update);
    }

    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $transfer = Transfer::select()->where('id', $id)->with([
            'bankFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_name']);
            },
            'bankTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email']);
            },
        ])->first();

        if ($transfer) {
            return [
                'code' => 200,
                'error' => 'Thông tin chi tiết',
                'data' => $transfer
            ];
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin chi tiết',
                'data' => null
            ];
        }
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $transfer = Transfer::where('id', $id)->withTrashed()->first();

        if ($transfer) {
            if ($transfer->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Giao dịch đã bị xóa',
                    'data' => null
                ];
            } else {
                $transfer->status = Constants::USER_STATUS_DELETED;
                $transfer->deleted_at = Carbon::now();

                if ($transfer->save()) {
                    return [
                        'code' => 200,
                        'error' => 'Xóa chuyển khoản thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'error' => 'Xóa chuyển khoản không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin chuyển khoản',
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
        $tran = Transfer::where('id', $id)->with([
            'bankFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_name']);
            },
            'bankTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'bank_name']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email']);
            },
        ]);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return Transfer::where('id', $id)->update($update);
    }
}
