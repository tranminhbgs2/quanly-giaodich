<?php

namespace App\Repositories\HoKinhDoanh;

use App\Events\ActionLogEvent;
use App\Models\HoKinhDoanh;
use App\Helpers\Constants;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class HoKinhDoanhRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $created_by = $params['created_by'] ?? 0;

        $query = HoKinhDoanh::select();

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('name', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('surrogate', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('phone', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('address', 'LIKE', "%" . $keyword . "%");
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

        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        //     $query->where('created_by', $created_by);
        // }

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

        $query->orderBy('balance', 'DESC');

        return $query->get()->toArray();
    }
    public function getTotal($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $created_by = $params['created_by'] ?? 0;

        $query = HoKinhDoanh::select();

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('name', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('surrogate', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('phone', 'LIKE', "%" . $keyword . "%")
                        ->orWhere('address', 'LIKE', "%" . $keyword . "%");
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

        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        //     $query->where('created_by', $created_by);
        // }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        $total = [
            'balance' => (int)$query->sum('balance'),
            'amount_old' => (int)$query->sum('amount_old'),
        ];
        return $total;
    }

    public function store($params)
    {
        $fillable = [
            'name',
            'surrogate',
            'phone',
            'address',
            'status',
            'balance',
            'amount_old'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }
        if (!empty($insert['name'])) {
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
            'status',
            'balance',
            'amount_old'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
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

    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getDetail($params, $with_trashed = false)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $tran = HoKinhDoanh::where('id', $id);

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
    public function getById($id, $with_trashed = false)
    {
        $tran = HoKinhDoanh::where('id', $id);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    public function changeStatus($status, $id)
    {
        $update = ['status' => $status];

        return HoKinhDoanh::where('id', $id)->update($update);
    }

    public function getAll()
    {
        return HoKinhDoanh::select('id', 'name', 'balance')->where('status', Constants::USER_STATUS_ACTIVE)->orderBy('id', 'DESC')->get()->toArray();
    }

    public function updateBalance($price_pos, $id, $action = "")
    {
        $pos = HoKinhDoanh::where('id', $id)->first();
        if (isset($price_pos)) {
            // Lưu log qua event
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id ?? 0,
                'username' => auth()->user()->username ?? 0,
                'action' => 'UPDATE_BANLANCE_HKD',
                'description' => $action . ' Cập nhật số tiền cho HKD ' . $pos->name . ' từ ' . $pos->balance . ' thành ' . $price_pos,
                'data_new' => $price_pos,
                'data_old' => $pos->balance,
                'model' => 'HoKinhDoanh',
                'table' => 'HoKinhDoanh',
                'record_id' => $pos->id,
                'ip_address' => request()->ip()
            ]));
            $params = ['balance' => $price_pos];
            return $pos->update($params);
        }

    }
}
