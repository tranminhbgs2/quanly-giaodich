<?php

namespace App\Repositories\HoKinhDoanh;

use App\Models\HoKinhDoanh;
use App\Helpers\Constants;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class HoKinhDoanhRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $created_by = $params['created_by'] ?? 0;

        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = HoKinhDoanh::select();

        if ($date_from && $date_to) {
            $query->whereBetween('created_at', [$date_from, $date_to]);
        }

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
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

    public function store($params)
    {
        $fillable = [
            'name',
            'surrogate',
            'phone',
            'address',
            'status'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['name']) && !empty($insert['phone'])) {
            return HoKinhDoanh::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'name',
            'surrogate',
            'phone',
            'address',
            'status'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return HoKinhDoanh::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $hoKinhDoanh = HoKinhDoanh::find($id);

        if ($hoKinhDoanh) {
            $hoKinhDoanh->status = Constants::USER_STATUS_DELETED;
            $hoKinhDoanh->deleted_at = Carbon::now();

            if ($hoKinhDoanh->save()) {
                return [
                    'code' => 200,
                    'error' => 'Xóa hộ kinh doanh thành công',
                    'data' => null
                ];
            } else {
                return [
                    'code' => 400,
                    'error' => 'Xóa hộ kinh doanh không thành công',
                    'data' => null
                ];
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy hộ kinh doanh',
                'data' => null
            ];
        }
    }
}
