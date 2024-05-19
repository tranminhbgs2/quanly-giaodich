<?php

namespace App\Repositories\Categories;

use App\Models\Categories;
use App\Helpers\Constants;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class CategoriesRepository extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;

        $query = Categories::select();

        if ($date_from && $date_to) {
            $query->whereBetween('created_at', [$date_from, $date_to]);
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
            'code',
            'fee',
            'name',
            'note',
            'status'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['code']) && !empty($insert['name'])) {
            return Categories::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'code',
            'fee',
            'name',
            'note',
            'status'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return Categories::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $category = Categories::find($id);

        if ($category) {
            $category->status = Constants::USER_STATUS_DELETED;
            $category->deleted_at = Carbon::now();

            if ($category->save()) {
                return [
                    'code' => 200,
                    'message' => 'Xóa danh mục thành công',
                    'data' => null
                ];
            } else {
                return [
                    'code' => 400,
                    'message' => 'Xóa danh mục không thành công',
                    'data' => null
                ];
            }
        } else {
            return [
                'code' => 404,
                'message' => 'Không tìm thấy danh mục',
                'data' => null
            ];
        }
    }
}
