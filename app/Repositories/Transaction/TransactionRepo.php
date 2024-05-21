<?php

namespace App\Repositories\Transaction;

use App\Helpers\Constants;
use App\Models\Transaction;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class TransactionRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hàm lấy ds gi, có tìm kiếm và phân trang
     *
     * @param $params
     * @param false $is_counting
     *
     * @return mixed
     */
    public function getListing($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $category_id = $params['category_id'] ?? 0;
        $lo_number = $params['lo_number'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = Transaction::select()->with([
            'category' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
            },
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
        ]);

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to) {
            $query->whereBetween('time_payment', [$date_from, $date_to]);
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($category_id > 0) {
            $query->where('category_id', $category_id);
        }

        if ($lo_number > 0) {
            $query->where('lo_number', $lo_number);
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
     * Hàm lấy tổng số giao dịch
     *
     * @param $params
     * @return array
     */
    public function getTotal($params)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $pos_id = $params['pos_id'] ?? 0;
        $category_id = $params['category_id'] ?? 0;
        $lo_number = $params['lo_number'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = Transaction::select();

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to) {
            $query->whereBetween('time_payment', [$date_from, $date_to]);
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($category_id > 0) {
            $query->where('category_id', $category_id);
        }

        if ($lo_number > 0) {
            $query->where('lo_number', $lo_number);
        }

        if ($status >= 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }

        // Tính tổng của từng trường cần thiết
        $total = [
            'price_nop' => $query->sum('price_nop'),
            'price_rut' => $query->sum('price_rut'),
            'price_fee' => $query->sum('price_fee'),
            'price_transfer' => $query->sum('price_transfer'),
            'profit' => $query->sum('profit'),
            'price_repair' => $query->sum('price_repair')
        ];

        return $total;
    }

    /**
     * Hàm tạo thông tin Khách hàng, Nhân viên
     *
     * @param $params
     * @return bool
     */
    public function store($params)
    {
        $fillable = [
            'category_id',
            'customer_id',
            'customer_name',
            'bank_card',
            'method',
            'pos_id',
            'lo_number',
            'price_nop',
            'price_rut',
            'fee',
            'price_fee',
            'price_transfer',
            'profit',
            'price_repair',
            'time_payment',
            'status',
            'created_by',
            'original_fee'
        ];

        $insert = [];

        // Lặp qua các trường fillable và kiểm tra xem chúng có trong $params không
        foreach ($fillable as $field) {
            // Kiểm tra xem trường tồn tại trong $params và không rỗng
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        // Tạo mới đối tượng và lưu thông tin nếu có đủ dữ liệu
        if (!empty($insert['category_id']) && !empty($insert['pos_id']) && !empty($insert['customer_name'])) {
            return Transaction::create($insert) ? true : false;
        }

        return false;
    }

    /**
     * Hàm cập nhật thông tin giao dịch theo id
     *
     * @param $params
     * @param $id
     * @return bool
     */
    public function update($params)
    {
        $fillable = [
            'category_id',
            'customer_id',
            'customer_name',
            'bank_card',
            'method',
            'pos_id',
            'lo_number',
            'price_nop',
            'price_rut',
            'fee',
            'price_fee',
            'price_transfer',
            'profit',
            'price_repair',
            'time_payment',
            'status',
            'created_by',
            'original_fee'
        ];

        $update = [];

        // Lặp qua các trường fillable và kiểm tra xem chúng có trong $params không
        foreach ($fillable as $field) {
            // Kiểm tra xem trường tồn tại trong $params và không rỗng
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        // Tìm đối tượng theo ID và cập nhật thông tin nếu tìm thấy
        return Transaction::where('id', $params['id'])->update($update);
    }


    /**
     * Hàm lấy chi tiết thông tin GD
     *
     * @param $params
     */
    public function getDetail($params, $with_trashed = false)
    {
        $id = isset($params['id']) ? $params['id'] : 0;
        $tran = Transaction::select()->where('id', $id);
        $tran->with([
            'category' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
            },
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
        ]);

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
        $tran = Transaction::select()->where('id', $id);
        $tran->with([
            'category' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
            },
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
        ]);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    /**
     * Hàm khóa thông tin khách hàng vĩnh viễn, khóa trạng thái, ko xóa vật lý
     *
     * @param $params
     * @return array
     */
    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $tran = Transaction::where('id', $id)
            ->withTrashed()
            ->first();

        if ($tran) {
            if ($tran->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Giao dịch đã bị xóa',
                    'data' => null
                ];
            } else {
                $tran->status = Constants::USER_STATUS_DELETED;
                $tran->deleted_at = Carbon::now();

                if ($tran->save()) {
                    return [
                        'code' => 200,
                        'error' => 'Xóa giao dịch thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'error' => 'Xóa giao dịch không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin giao dịch',
                'data' => null
            ];
        }
    }
    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return Transaction::where('id', $id)->update($update);
    }
}
