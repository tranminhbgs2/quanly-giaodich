<?php

namespace App\Repositories\CashFlow;

use App\Events\ActionLogEvent;
use App\Helpers\Constants;
use App\Models\CashFlow;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class CashFlowRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getListing($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $type = $params['type'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $manager_id = $params['created_by'] ?? 0;

        $query = CashFlow::select()->with([
            'managerBy' => function ($sql) {
                $sql->select(['id', 'fullname', 'status']);
            },
            'bankAccounts' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code', 'balance']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('note', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('price', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)){
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('created_at', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($manager_id > 0) {
            $query->where('created_by', $manager_id);
        }

        if (!empty($type)) {
            $query->where('type', $type);
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
            'type',
            'acc_bank_id',
            'note',
            'price',
            'time_payment',
            'status',
            'created_by',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['type'])) {
            $res = CashFlow::create($insert);
            return $res->id;
        }

        return 0;
    }

    /**
     * Hàm cập nhật thông tin
     *
     * @param $params
     * @param $id
     */
    public function update($params, $id)
    {
        $fillable = [
            'type',
            'acc_bank_id',
            'note',
            'price',
            'time_payment',
            'status',
            'created_by',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return CashFlow::where('id', $id)->update($update);
    }

    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $cash = CashFlow::select()->where('id', $id)->with([
            'managerBy' => function ($sql) {
                $sql->select(['id', 'fullname', 'status']);
            },
            'bankAccounts' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code', 'balance']);
            },
        ])->first();

        if ($cash) {
            return [
                'code' => 200,
                'error' => 'Thông tin chi tiết',
                'data' => $cash
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
        $cash = CashFlow::where('id', $id)->first();

        if ($cash) {

                $cash->status = Constants::USER_STATUS_DELETED;
                $cash->deleted_at = Carbon::now();

                if ($cash->save()) {
                    return true;
                } else {
                    return false;
                }
        } else {
            return false;
        }
    }

    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getById($id, $with_trashed = false)
    {
        $cash = CashFlow::where('id', $id);

        if ($with_trashed) {
            $cash->withTrashed();
        }

        return $cash->first();
    }

    public function changeStatus($status, $id)
    {
        $update = ['status' => $status];
        return CashFlow::where('id', $id)->update($update);
    }

    public function getAll()
    {
        return CashFlow::select('id', 'type')->where('status', Constants::USER_STATUS_ACTIVE)->orderBy('id', 'DESC')->get()->toArray();
    }
}
