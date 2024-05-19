<?php

namespace App\Repositories\Pos;

use App\Helpers\Constants;
use App\Models\Pos;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class PosRepo extends BaseRepo
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
        $hkd_id = $params['hkd_id'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = Pos::with([
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name']);
            },
            'activeAgents',
        ]);

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('name', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if ($date_from && $date_to) {
            $query->whereBetween('created_at', [$date_from, $date_to]);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($status >= 0) {
            $query->where('status', $status);
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
            'bank_code',
            'method',
            'hkd_id',
            'fee',
            'total_fee',
            'fee_cashback',
            'price_pos',
            'created_by',
            'status',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['name']) && !empty($insert['hkd_id'])) {
            return Pos::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'name',
            'bank_code',
            'method',
            'hkd_id',
            'fee',
            'total_fee',
            'fee_cashback',
            'price_pos',
            'created_by',
            'status',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return Pos::where('id', $id)->update($update);
    }

    public function getDetail($params)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $pos = Pos::select()->where('id', $id)->with([
            'hokinhdoanh' => function ($sql) {
                $sql->select(['id', 'name']);
            },
        ])->first();

        if ($pos) {
            return [
                'code' => 200,
                'message' => 'Thông tin chi tiết',
                'data' => $pos
            ];
        } else {
            return [
                'code' => 404,
                'message' => 'Không tìm thấy thông tin chi tiết',
                'data' => null
            ];
        }
    }

    /**
     * Xóa điểm POS
     *
     * @param array $params
     * @return array
     */
    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $pos = Pos::where('id', $id)->withTrashed()->first();

        if ($pos) {
            if ($pos->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'message' => 'Điểm POS đã bị xóa',
                    'data' => null
                ];
            } else {
                $pos->status = Constants::USER_STATUS_DELETED;
                $pos->deleted_at = Carbon::now();

                if ($pos->save()) {
                    return [
                        'code' => 200,
                        'message' => 'Xóa điểm POS thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'message' => 'Xóa điểm POS không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'message' => 'Không tìm thấy thông tin điểm POS',
                'data' => null
            ];
        }
    }

    /**
     * Lấy danh sách POS theo hkd_id
     *
     * @param int $hkd_id
     * @return array
     */
    public function getPosByHkd($hkd_id)
    {
        return Pos::where('hkd_id', $hkd_id)->get()->toArray();
    }

    /**
     * Gán POS cho đại lý
     *
     * @param array $params
     * @return array
     */
    public function assignPosToAgent($params)
    {
        $pos_id = isset($params['pos_id']) ? $params['pos_id'] : 0;
        $agent_id = isset($params['agent_id']) ? $params['agent_id'] : 0;
        $fee = isset($params['fee']) ? $params['fee'] : 0;

        $pos = Pos::find($pos_id);

        if ($pos) {
            $pos->addAgentWithDeactivation($agent_id, $fee);

            return [
                'code' => 200,
                'message' => 'Gán POS cho đại lý thành công',
                'data' => null
            ];
        } else {
            return [
                'code' => 404,
                'message' => 'Không tìm thấy thông tin POS',
                'data' => null
            ];
        }
    }
}
