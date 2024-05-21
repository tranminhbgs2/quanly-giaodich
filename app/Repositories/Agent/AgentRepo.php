<?php

namespace App\Repositories\Agent;

use App\Helpers\Constants;
use App\Models\Agent;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class AgentRepo extends BaseRepo
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
        $manager_id = $params['manager_id'] ?? 0;

        $query = Agent::select()->with([
            'managerBy' => function ($sql) {
                $sql->select(['id', 'fullname', 'status']);
            },
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('surrogate', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('address', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('phone', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && $date_from <= $date_to && !empty($date_from) && !empty($date_to)){
            $query->whereBetween('created_at', [$date_from, $date_to]);
        }

        if ($manager_id > 0) {
            $query->where('manager_id', $manager_id);
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
            'name',
            'surrogate',
            'address',
            'phone',
            'manager_id',
            'status',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['name']) && !empty($insert['manager_id'])) {
            return Agent::create($insert) ? true : false;
        }

        return false;
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
            'name',
            'surrogate',
            'address',
            'phone',
            'manager_id',
            'status',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return Agent::where('id', $id)->update($update);
    }

    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $agent = Agent::select()->where('id', $id)->with([
            'managerBy' => function ($sql) {
                $sql->select(['id', 'fullname', 'status']);
            },
        ])->first();

        if ($agent) {
            return [
                'code' => 200,
                'error' => 'Thông tin chi tiết',
                'data' => $agent
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
        $agent = Agent::where('id', $id)->withTrashed()->first();

        if ($agent) {
            if ($agent->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Đại lý đã bị xóa',
                    'data' => null
                ];
            } else {
                $agent->status = Constants::USER_STATUS_DELETED;
                $agent->deleted_at = Carbon::now();

                if ($agent->save()) {
                    return [
                        'code' => 200,
                        'error' => 'Xóa đại lý thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'error' => 'Xóa đại lý không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin đại lý',
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
        $tran = Agent::where('id', $id);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    public function changeStatus($status, $id)
    {
        $update = ['status' => $status];
        return Agent::where('id', $id)->update($update);
    }
}
