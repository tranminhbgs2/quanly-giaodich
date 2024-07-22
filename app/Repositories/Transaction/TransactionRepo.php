<?php

namespace App\Repositories\Transaction;

use App\Events\ActionLogEvent;
use App\Helpers\Constants;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
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
        $hkd_id = $params['hkd_id'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;
        $method = $params['method'] ?? null;
        $status_fee = $params['status_fee'] ?? 1; // 1: tất cả, 2: chưa thanh toán, 3: đã thanh toán. chưa thanh toán khi fee_paid < price_fee, đã thanh toán khi fee_paid = price_fee

        $query = Transaction::select()->with([
            'category' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
            },
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback', 'bank_code']);
            },
            'hkd' => function ($sql) {
                $sql->select(['id', 'name', 'balance']);
            },
        ]);

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF || $created_by > 0) {
            $query->where('created_by', $created_by);
        }

        if ($status_fee > 1) {
            $query->where('status_fee', $status_fee);
        }

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%")
                    ->orwhere('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
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

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }


        //Kế toán này chỉ xem dc GD POSS
        if (auth()->user()->id == 2372) {
            if (!empty($method)) {
                if ($method != 'ONLINE' && $method != 'QR_CODE') {
                    $query->where('method', $method);
                } else {
                    $query->whereNotIn('method', ['ONLINE', 'QR_CODE']);
                }
            } else {
                $query->whereNotIn('method', ['ONLINE', 'QR_CODE']);
            }
        } elseif (auth()->user()->id == 2373) {
            //Kế toán này chỉ xem dc GD Online
            if (!empty($method)) {
                if ($method == 'ONLINE' || $method == 'QR_CODE') {
                    $query->where('method', $method);
                } else {
                    $query->whereIn('method', ['ONLINE', 'QR_CODE']);
                }
            } else {
                $query->whereIn('method', ['ONLINE', 'QR_CODE']);
            }
        } else {
            if (!empty($method)) {
                $query->where('method', $method);
            }
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
     * Hàm lấy ds gi, có tìm kiếm và phân trang
     *
     * @param $params
     * @param false $is_counting
     *
     * @return mixed
     */
    public function getListingCashBack($params, $is_counting = false)
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

        $query = Transaction::select(["price_rut", "time_payment", "pos_id"])->with([
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

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
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

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        $query->orderBy('id', 'DESC');
        // Lấy kết quả và nhóm theo pos_id và ngày
        $transactions = $query->with('pos')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->pos_id . '_' . Carbon::parse($transaction->time_payment)->format('Y-m-d');
            })
            ->map(function ($group) {
                $pos = $group->first()->pos;
                $date = Carbon::parse($group->first()->time_payment)->format('d/m/Y');
                $total_price_rut = $group->sum('price_rut');
                $total_payment_cashback = intval($total_price_rut * $pos->fee_cashback / 100);

                return [
                    'pos_id' => $pos->id,
                    'date' => $date,
                    'total_price_rut' => $total_price_rut,
                    'total_payment_cashback' => $total_payment_cashback,
                    'pos' => [
                        'id' => $pos->id,
                        'name' => $pos->name,
                        'fee' => $pos->fee,
                        'total_fee' => $pos->total_fee,
                        'fee_cashback' => $pos->fee_cashback
                    ]
                ];
            })
            ->values();

        // $results = $query->get();
        // Tính toán tổng số lượng kết quả đã nhóm
        $total = $transactions->count();

        // Nếu đang đếm số lượng, trả về tổng số lượng
        if ($is_counting) {
            return $total;
        }

        // Xử lý phân trang trên kết quả đã nhóm
        $offset = ($page_index - 1) * $page_size;
        $pagedTransactions = $transactions->slice($offset, $page_size);

        return $pagedTransactions->values();
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
        $hkd_id = $params['hkd_id'] ?? 0;
        $category_id = $params['category_id'] ?? 0;
        $lo_number = $params['lo_number'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;
        $method = $params['method'] ?? null;
        $status_fee = $params['status_fee'] ?? 1; // 1: tất cả, 2: chưa thanh toán, 3: đã thanh toán. chưa thanh toán khi fee_paid < price_fee, đã thanh toán khi fee_paid = price_fee

        $query = Transaction::select();

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword);
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%")
                    ->orwhere('lo_number', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($category_id > 0) {
            $query->where('category_id', $category_id);
        }

        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($lo_number > 0) {
            $query->where('lo_number', $lo_number);
        }

        if ($account_type == Constants::ACCOUNT_TYPE_STAFF || $created_by > 0) {
            $query->where('created_by', $created_by);
        }

        if ($status_fee > 1) {
            $query->where('status_fee', $status_fee);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }

        //Kế toán này chỉ xem dc GD Online
        if (auth()->user()->id == 2372) {
            if (!empty($method)) {
                if ($method != 'ONLINE' && $method != 'QR_CODE') {
                    $query->where('method', $method);
                } else {
                    $query->whereNotIn('method', ['ONLINE', 'QR_CODE']);
                }
            } else {
                $query->whereNotIn('method', ['ONLINE', 'QR_CODE']);
            }
        } elseif (auth()->user()->id == 2373) {
            //Kế toán này chỉ xem dc GD Online
            if (!empty($method)) {
                if ($method == 'ONLINE' || $method == 'QR_CODE') {
                    $query->where('method', $method);
                } else {
                    $query->whereIn('method', ['ONLINE', 'QR_CODE']);
                }
            } else {
                $query->whereIn('method', ['ONLINE', 'QR_CODE']);
            }
        } else {
            if (!empty($method)) {
                $query->where('method', $method);
            }
        }

        // Fetch all transactions to perform conditional sum operations
        $transactions = $query->get();

        // Sum the fields based on status_fee condition
        $price_transfer = $transactions->where('status_fee', 3)->sum('price_transfer');
        $not_price_transfer = $transactions->where('status_fee', '!=', 3)->where('method', '!=', 'DAO_HAN')->sum('price_transfer');
        $price_nop = $transactions->sum('price_nop');

        // Tổng tiền đã chuyển khoản đi: tiền đã ck + tiền nộp(nộp vào pos)
        $total_price_transfer = $price_transfer + $price_nop;

        $total_fee_paid = $transactions->sum('fee_paid');
        $price_fee = $transactions->sum('total_fee');

        // Tính tổng của từng trường cần thiết
        $total = [
            'price_nop' => $price_nop,
            'price_rut' => $transactions->sum('price_rut'),
            'price_fee' => (int)$price_fee,
            'price_transfer' => $price_transfer,
            'not_price_transfer' => $not_price_transfer,
            'profit' => (int)$transactions->sum('profit'),
            'price_repair' => $transactions->sum('price_repair'),
            'total_fee_paid' => $price_fee - $total_fee_paid,
            'total_price_transfer' => $total_price_transfer,
        ];
        return $total;
    }

    /**
     * Hàm lấy tổng số giao dịch
     *
     * @param $params
     * @return array
     */
    public function getTotalCashBack($params)
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

        // Khởi tạo query
        $query = Transaction::query();

        // Áp dụng các điều kiện lọc
        if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
            $query->where('created_by', $created_by);
        }

        if (!empty($keyword)) {
            $keyword = translateKeyWord($keyword); // Giả định rằng hàm translateKeyWord đã được định nghĩa
            $query->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('customer_name', 'LIKE', "%" . $keyword . "%");
            });
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
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

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', Constants::USER_STATUS_DELETED);
        }

        $query->orderBy('id', 'DESC');

        // Lấy kết quả và nhóm theo pos_id và ngày
        $transactions = $query->with('pos')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->pos_id . '_' . Carbon::parse($transaction->time_payment)->format('Y-m-d');
            })
            ->map(function ($group) {
                $pos = $group->first()->pos;
                $date = Carbon::parse($group->first()->time_payment)->format('Y-m-d');
                $total_price_rut = $group->sum('price_rut');
                $total_payment_cashback = $total_price_rut * $pos->fee_cashback / 100;

                return [
                    'pos_id' => $pos->id,
                    'date' => $date,
                    'total_price_rut' => $total_price_rut,
                    'total_payment_cashback' => (int)$total_payment_cashback,
                    'pos' => [
                        'id' => $pos->id,
                        'name' => $pos->name,
                        'fee' => $pos->fee,
                        'total_fee' => $pos->total_fee,
                        'fee_cashback' => $pos->fee_cashback
                    ]
                ];
            })
            ->values();

        // Tính tổng total_payment_cashback
        $total_cashback_sum = $transactions->sum('total_payment_cashback');
        // Tính tổng của từng trường cần thiết
        $total = [
            'payment_cashback' => $total_cashback_sum
        ];

        return $total;
    }

    /**
     * Hàm tạo thông tin Khách hàng, Nhân viên
     *
     * @param $params
     * @return
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
            'original_fee',
            'fee_cashback',
            'note',
            'fee_paid',
            'hkd_id',
            'type_card',
            'bank_code',
            'transfer_by',
            'total_fee',
            'price_array',
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
        if (!empty($insert['customer_name'])) {
            return Transaction::create($insert);
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
            'original_fee',
            'fee_cashback',
            'note',
            'fee_paid',
            'hkd_id',
            'type_card',
            'bank_code',
            'transfer_by',
            'total_fee',
            'price_array'
        ];

        $update = [];

        // Lặp qua các trường fillable và kiểm tra xem chúng có trong $params không
        foreach ($fillable as $field) {
            // Kiểm tra xem trường tồn tại trong $params và không rỗng
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }
        $tran = Transaction::where('id', $params['id'])->first();
        // Lưu log qua event
        if (isset($update['price_nop']) && $update['price_nop'] != $tran->price_nop) {
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id ?? 0,
                'username' => auth()->user()->username ?? 0,
                'action' => 'UPDATED_TRANSACTION',
                'description' => 'UPDATED_TRANSACTION_' . $tran->id . ' Cập nhật price_nop Transaction ' . $tran->id . ' từ ' . $tran->price_nop . ' thành ' . $update['price_nop'],
                'data_new' => $update['price_nop'],
                'data_old' => $tran->price_nop,
                'model' => 'Transaction',
                'table' => 'transactions',
                'record_id' => $tran->id,
                'ip_address' => request()->ip()
            ]));
        }
        // Lưu log qua event
        if (isset($update['time_payment']) && $update['time_payment'] != $tran->time_payment) {
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id ?? 0,
                'username' => auth()->user()->username ?? 0,
                'action' => 'UPDATED_TRANSACTION',
                'description' => 'UPDATED_TRANSACTION_' . $tran->id . ' Cập nhật price_nop Transaction ' . $tran->id . ' từ ' . $tran->time_payment . ' thành ' . $update['time_payment'],
                'data_new' => $update['time_payment'],
                'data_old' => $tran->time_payment,
                'model' => 'Transaction',
                'table' => 'transactions',
                'record_id' => $tran->id,
                'ip_address' => request()->ip()
            ]));
        }

        if (isset($update['price_transfer']) && $update['price_transfer'] != $tran->price_transfer) {
            event(new ActionLogEvent([
                'actor_id' => auth()->user()->id ?? 0,
                'username' => auth()->user()->username ?? 0,
                'action' => 'UPDATED_TRANSACTION',
                'description' => 'UPDATED_TRANSACTION_' . $tran->id . ' Cập nhật price_transfer Transaction ' . $tran->id . ' từ ' . $tran->price_transfer . ' thành ' . $update['price_transfer'],
                'data_new' => $update['price_transfer'],
                'data_old' => $tran->price_transfer,
                'model' => 'Transaction',
                'table' => 'transactions',
                'record_id' => $tran->id,
                'ip_address' => request()->ip()
            ]));
        }
        // Tìm đối tượng theo ID và cập nhật thông tin nếu tìm thấy
        return $tran->update($update);
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

    public function ReportDashboard($params)
    {
        $date_from = $params['date_from'] ?? Carbon::now()->startOfDay();
        $date_to = $params['date_to'] ?? Carbon::now()->endOfDay();
        $date_from = Carbon::parse($date_from)->startOfDay();
        $date_to = Carbon::parse($date_to)->endOfDay();
        $query = Transaction::select()
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->where('created_at', '>=', $date_from)
            ->where('created_at', '<=', $date_to);
        if (auth()->user()->account_type !== Constants::ACCOUNT_TYPE_SYSTEM) {
            $query->where('created_by', auth()->user()->id);
        }
        $query->get();


        // Tính tổng của từng trường cần thiết
        $total = [
            'san_luong' => (int)$query->sum('price_rut'),
            'profit' => (int)$query->sum('profit'),
            'price_nop' => (int)$query->sum('price_nop'),
            'price_transfer' => (int)$query->where('status_fee', 3)->sum('price_transfer'),
        ];
        //Tính tiền gốc nhận được theo từng giao dịch sau đó tính tổng bằng = price_rut - price_rut * original_fee / 100
        $total['tien_nhan'] = 0;
        foreach ($query as $transaction) {
            $total['tien_nhan'] += (int)($transaction->price_rut - $transaction->price_rut * $transaction->original_fee / 100);
        }

        return $total;
    }
    public function topStaffTransaction($params)
    {
        // Đặt múi giờ mặc định cho toàn bộ ứng dụng (nếu cần)
        date_default_timezone_set('Asia/Ho_Chi_Minh'); // GMT+7
        // Set default date range if not provided
        $date_from = $params['date_from'] . ' 00:00:00' ?? Carbon::now()->startOfDay();
        $date_to = $params['date_to'] . ' 00:00:00' ?? Carbon::now()->endOfDay();
        
        // Chuyển đổi sang Carbon object với định dạng Y-m-d H:i:s
        $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();

        // Query to get transactions within the date range and group by 'created_by'
        $transactionsQuery = Transaction::select([
            'created_by', 'price_rut', 'price_nop', 'profit', 'price_transfer', 'original_fee',
            'status_fee', 'method', 'transfer_by'
        ])
            ->with([
                'createdBy' => function ($query) {
                    $query->select(['id', 'fullname', 'balance']);
                }
            ])
            ->where('status', Constants::USER_STATUS_ACTIVE);

        if (isset($params['created_by']) && $params['account_type'] === Constants::ACCOUNT_TYPE_STAFF) {
            $transactionsQuery->where('created_by', $params['created_by']);
        }

        $transactions = $transactionsQuery->whereBetween('created_at', [$date_from, $date_to])
            ->get()
            ->groupBy('created_by');

        // Initialize an array to store staff information
        $staffs = [];
        $user_staff = User::select('id', 'fullname', 'status', 'balance')->where('status', Constants::USER_STATUS_ACTIVE)->whereIn('account_type', [Constants::ACCOUNT_TYPE_STAFF, Constants::ACCOUNT_TYPE_ACCOUNTANT])->orderBy('id', 'DESC')->get()->toArray();

        foreach ($user_staff as $user) {
            $staffs[$user['id']] = [
                'id' => $user['id'],
                'name' => $user['fullname'],
                'total_price_rut' => 0,
                'total_profit' => 0,
                'total_price_transfer' => 0,
                'user_balance' => $user['balance'],
                'total_mester_transfer' => 0,
                'date_from' => $date_from,
                'date_to' => $date_to,
            ];
        }
        // Calculate total_price_transfer for each staff
        foreach ($transactions as $group) {
            foreach ($group as $transaction) {
                $createdBy = $transaction->createdBy;
                $transferBy = $transaction->transferBy;

                // Initialize the staff entry if not exists for createdBy
                if (!isset($staffs[$createdBy->id])) {
                    $staffs[$createdBy->id] = [
                        'id' => $createdBy->id,
                        'name' => $createdBy->fullname,
                        'total_price_rut' => 0,
                        'total_profit' => 0,
                        'total_price_transfer' => 0,
                        'user_balance' => $createdBy->balance,
                        'total_mester_transfer' => 0
                    ];
                }

                // Add transaction values to createdBy's totals
                $staffs[$createdBy->id]['total_price_rut'] += $transaction->price_rut;
                $staffs[$createdBy->id]['total_profit'] += $transaction->profit;

                // Check if transferBy exists and is different from createdBy
                if ($transferBy) {
                    // Initialize the staff entry if not exists for transferBy
                    if (!isset($staffs[$transferBy->id])) {
                        $staffs[$transferBy->id] = [
                            'id' => $transferBy->id,
                            'name' => $transferBy->fullname,
                            'total_price_rut' => 0,
                            'total_profit' => 0,
                            'total_price_transfer' => 0,
                            'user_balance' => $transferBy->balance,
                            'total_mester_transfer' => 0
                        ];
                    }

                    // Calculate total_price_transfer for transferBy
                    if ($transaction->status_fee == 3 && $transaction->method != 'DAO_HAN') {
                        $staffs[$transferBy->id]['total_price_transfer'] += $transaction->price_transfer;
                    } elseif ($transaction->method == 'DAO_HAN') {
                        $staffs[$transferBy->id]['total_price_transfer'] += $transaction->price_nop;
                    }
                } else {
                    // Calculate total_price_transfer for createdBy
                    if ($transaction->status_fee == 3 && $transaction->method != 'DAO_HAN') {
                        $staffs[$createdBy->id]['total_price_transfer'] += $transaction->price_transfer;
                    } elseif ($transaction->method == 'DAO_HAN') {
                        $staffs[$createdBy->id]['total_price_transfer'] += $transaction->price_nop;
                    }
                }
            }
        }
        // Calculate the total amount transferred to the staff from transferRepo
        foreach ($staffs as &$staff) {
            $query_transfer = Transfer::select('to_agent_id', 'price')
                ->where('status', Constants::USER_STATUS_ACTIVE)
                ->where('to_agent_id', $staff['id'])
                ->where('type_to', Constants::ACCOUNT_TYPE_STAFF)
                ->whereBetween('time_payment', [$date_from, $date_to])
                ->get();
            $staff['total_mester_transfer'] = $query_transfer->sum('price');

            // nhân viên ck lại cho tk nguồn
            $query_transfer_from = Transfer::select('from_agent_id', 'price')
                ->where('status', Constants::USER_STATUS_ACTIVE)
                ->where('from_agent_id', $staff['id'])
                ->where('type_from', Constants::ACCOUNT_TYPE_STAFF)
                ->whereBetween('time_payment', [$date_from, $date_to])
                ->get();
            $staffs[$staff['id']]['total_price_transfer'] += $query_transfer_from->sum('price');
        }
        // Sort by total price rutted and return all results
        $topStaff = collect($staffs)->sortByDesc('total_price_rut')->values()->all();

        return $topStaff;
    }

    public function changeFeePaid($fee_paid, $id, $transfer_by,  $type = "")
    {
        $tran = Transaction::where('id', $id)->where('status', Constants::USER_STATUS_ACTIVE)->first();
        if ($tran->fee_paid == $tran->total_fee) {
            $status_fee = 3;
            $fee_paid_new = $fee_paid;
        } else {
            $fee_paid_new = $tran->fee_paid + $fee_paid;
            if ($fee_paid_new == $tran->total_fee) {
                $status_fee = 3;
            } else {
                $status_fee = 2;
            }
        }
        //danh cho quy lại các action
        if ($type == "RESTORE") {
            $fee_paid_new = $tran->fee_paid + $fee_paid;
            $status_fee = 2;
        }
        $update = ['fee_paid' => $fee_paid_new, 'status_fee' => $status_fee, 'transfer_by' => $transfer_by];

        return $tran->update($update);
    }

    public function chartDashboard($params)
    {
        $date_from = Carbon::parse($params['date_from'])->startOfDay();
        $date_to = Carbon::parse($params['date_to'])->endOfDay();

        // Tạo một đối tượng Collection mới chứa các ngày trong khoảng thời gian đã chỉ định
        $date_range = collect();
        $current_date = $date_from->copy();
        while ($current_date->lessThanOrEqualTo($date_to)) {
            $date_range->push($current_date->toDateString());
            $current_date->addDay();
        }

        // Truy vấn dữ liệu từ cơ sở dữ liệu
        $query = Transaction::select(['price_rut', 'profit', 'created_at'])
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->get()
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->created_at)->toDateString();
            });

        // Merge các ngày trong khoảng thời gian đã chỉ định với các ngày trong kết quả truy vấn
        $date_range = $date_range->merge($query->keys());

        // Đảm bảo không có ngày trùng lặp và sắp xếp lại danh sách các ngày
        $date_range = $date_range->unique()->sort();

        // Điền vào danh sách ngày không có dữ liệu và tính tổng hợp
        $total = ['total_price_rut' => 0, 'total_profit' => 0];
        $result = $date_range->map(function ($date) use ($query, &$total) {
            $total_price_rut = $query->has($date) ? $query[$date]->sum('price_rut') : 0;
            $total_profit = $query->has($date) ? $query[$date]->sum('profit') : 0;
            $total['total_price_rut'] += $total_price_rut; // Cộng tổng sản lượng vào biến tổng hợp
            $total['total_profit'] += $total_profit; // Cộng tổng lợi nhuận vào biến tổng hợp
            // Định dạng lại ngày theo định dạng d/m/Y
            $formatted_date = Carbon::parse($date)->format('d/m/Y');
            return [
                'date' => $formatted_date,
                'total_price_rut' => $total_price_rut,
                'total_profit' => (int)$total_profit
            ];
        });

        // Trả về mảng gồm data và total
        return ['data' => $result, 'total' => $total];
    }

    public function getAllByHkd($param)
    {
        $hkd_id = $param['hkd_id'] ?? 0;
        $lo_number = $param['lo_number'] ?? 0;
        $pos_id = $param['pos_id'] ?? 0;
        $date_from = $param['date_from'] ?? null;
        $date_to = $param['date_to'] ?? null;

        $query = Transaction::select()->with([
            'category' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
            },
            'pos' => function ($sql) {
                $sql->select(['id', 'name', 'fee', 'total_fee', 'fee_cashback']);
            },
        ])
            ->where('hkd_id', $hkd_id)
            ->where('lo_number', $lo_number)
            ->where('status', Constants::USER_STATUS_ACTIVE);

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }
        return $query->get()->toArray();
    }

    public function getPriceNop($transfer_by)
    {
        $query = Transaction::select('price_nop')
            ->where('transfer_by', $transfer_by)
            ->where('method', 'DAO_HAN')
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->get();
        return $query->sum('price_nop');
    }

    public function getPriceTransfer($transfer_by)
    {
        $query = Transaction::select('price_transfer')
            ->where('transfer_by', $transfer_by)
            ->where('status_fee', 3)
            ->where('method', '!=', 'DAO_HAN')
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->get();
        return $query->sum('price_transfer');
    }

    public function getPriceLoNumber($params)
    {
        $lo_number = $params['lo_number'] ?? 0;
        $pos_id = $params['pos_id'] ?? 0;
        $hkd_id = $params['hkd_id'] ?? 0;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;

        $query = Transaction::select('price_rut', 'price_fee')
            ->where('lo_number', $lo_number)
            ->where('status', Constants::USER_STATUS_ACTIVE);

        if ($pos_id > 0) {
            $query->where('pos_id', $pos_id);
        }
        if ($hkd_id > 0) {
            $query->where('hkd_id', $hkd_id);
        }

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }
        $query->get();
        $total = [
            'price_rut' => (int)$query->sum('price_rut'),
            'price_fee' => (int)$query->sum('price_fee'),
        ];
        return $total;
    }

    public function getProfitTrans($startDate, $endDate, $method)
    {
        // Truy vấn dữ liệu từ bảng transactions
        return Transaction::selectRaw('DATE(time_payment) as date, SUM(price_fee - (original_fee * price_rut / 100)) as profit_trans')
            ->whereBetween('time_payment', [$startDate, $endDate])
            ->where('status', Constants::USER_STATUS_ACTIVE)
            ->whereIn('method', $method)
            ->groupBy('date')
            ->get();
    }
}
