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
        $type_from = $params['type_from'] ?? null;
        $type_to = $params['type_to'] ?? null;
        $id_to = $params['id_to'] ?? 0;
        $id_from = $params['id_from'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = Transfer::select()->with([
            'bankTransferFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'bankTransferTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname', "status"]);
            },
            'fromAgent' => function ($sql) {
                $sql->select(['id', 'name']); // Adjust the columns as needed
            },
            'toAgent' => function ($sql) {
                $sql->select(['id', 'name']); // Adjust the columns as needed
            },
            'fromUser' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname', 'status']); // Adjust the columns as needed
            },
            'toUser' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname', 'status']); // Adjust the columns as needed
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

        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        //     $query->where('created_by', $created_by);
        // }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
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

        if ($type_from) {
            $query->where('type_from', $type_from);
        }
        if ($type_to) {
            $query->where('type_to', $type_to);
        }
        if ($id_to > 0) {
            $query->where('to_agent_id', $id_to);
        }
        if ($id_from > 0) {
            $query->where('from_agent_id', $id_from);
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

        $query->orderBy('time_payment', 'DESC');

        return $query->get()->toArray();
    }


    public function getTotal($params, $is_counting = false)
    {
        $keyword = $params['keyword'] ?? null;
        $status = $params['status'] ?? -1;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;
        $acc_bank_from_id = $params['acc_bank_from_id'] ?? 0;
        $acc_bank_to_id = $params['acc_bank_to_id'] ?? 0;
        $type_from = $params['type_from'] ?? null;
        $type_to = $params['type_to'] ?? null;
        $id_to = $params['id_to'] ?? 0;
        $id_from = $params['id_from'] ?? 0;
        $created_by = $params['created_by'] ?? 0;
        $account_type = $params['account_type'] ?? Constants::ACCOUNT_TYPE_STAFF;

        $query = Transfer::select()->with([
            'bankTransferFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'bankTransferTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', 'fullname', "status"]);
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

        // if ($account_type == Constants::ACCOUNT_TYPE_STAFF) {
        //     $query->where('created_by', $created_by);
        // }

        if ($date_from && $date_to && strtotime($date_from) <= strtotime($date_to) && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
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

        if ($type_from) {
            $query->where('type_from', $type_from);
        }
        if ($type_to) {
            $query->where('type_to', $type_to);
        }
        if ($id_to > 0) {
            $query->where('to_agent_id', $id_to);
        }
        if ($id_from > 0) {
            $query->where('from_agent_id', $id_from);
        }

        $total = [
            'price' => (int)$query->sum('price'),
        ];

        return $total;
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
            'type_from',
            'type_to',
            'time_payment',
            'created_by',
            'price',
            'status',
            'from_agent_id',
            'to_agent_id',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['acc_bank_from_id']) && !empty($insert['acc_bank_to_id'])) {
            return Transfer::create($insert);
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
            'from_agent_id',
            'to_agent_id',
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
        $transfer = Transfer::where('id', $id)->with([
            'bankTransferFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'bankTransferTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', "status"]);
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
        $transfer = Transfer::where('id', $id)->first();

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
            'bankTransferFrom' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'bankTransferTo' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code']);
            },
            'createdBy' => function ($sql) {
                $sql->select(['id', 'username', 'email', "status"]);
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


    /**
     * Hàm lấy tổng số giao dịch
     *
     * @param $params
     * @return array
     */
    public function getTotalAgent($params)
    {
        $status = $params['status'] ?? -1;
        $date_from = $params['agent_date_from'] ?? null;
        $date_to = $params['agent_date_to'] ?? null;
        $agent_id = $params['agent_id'] ?? 0;
        $type = $params['type'] ?? "TO";

        $query = Transfer::select();

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }
        if($type == "TO"){
            $query->where('type_to', "AGENCY");
        } else {
            $query->where('type_from', "AGENCY");
        }

        if ($agent_id > 0) {
            if($type == "TO"){
                $query->where('to_agent_id', $agent_id);
            } else {
                $query->where('from_agent_id', $agent_id);
            }
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }
        // Tính tổng của từng trường cần thiết
        $total = [
            'total_transfer' => $query->sum('price'),
        ];

        return $total;
    }

    /**
     * Hàm lấy tổng số giao dịch
     *
     * @param $params
     * @return array
     */
    public function getTotalMaster($params)
    {
        $status = $params['status'] ?? 1;
        $date_from = $params['date_from'] ?? Carbon::now()->startOfDay();
        $date_to = $params['date_to'] ?? Carbon::now()->endOfDay();

        $query = Transfer::select();

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }
        // tính tổng tiền nhận đã được chuyển cho nhân viên
        if (auth()->user()->account_type !== Constants::ACCOUNT_TYPE_SYSTEM) {
            $query->where('to_agent_id', auth()->user()->id);
            $query->where('type_to', "STAFF");
        } else {
            $query->where('type_from', "MASTER");
        }
        // Tính tổng của từng trường cần thiết
        $total = [
            'total_transfer' => (int)$query->sum('price'),
        ];

        return $total;
    }

    /**
     * Hàm lấy tổng số giao dịch
     *
     * @param $params
     * @return array
     */
    public function getListAgent($params)
    {
        $status = $params['status'] ?? -1;
        $date_from = $params['agent_date_from'] ?? null;
        $date_to = $params['agent_date_to'] ?? null;
        $agent_id = $params['agent_id'] ?? 0;

        $query = Transfer::select();

        if ($date_from && $date_to && !empty($date_from) && !empty($date_to)) {
            try {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->startOfDay();
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_to)->endOfDay();
                $query->whereBetween('time_payment', [$date_from, $date_to]);
            } catch (\Exception $e) {
                // Handle invalid date format
            }
        }
        $query->where('type_to', "AGENCY");

        if ($agent_id > 0) {
            $query->where('to_agent_id', $agent_id);
        }

        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', Constants::USER_STATUS_ACTIVE);
        }

        return $query->get()->toArray();
    }

    public function getBalanceTransferStaff($id, $type = "TO")
    {
        // $date_from = Carbon::now()->startOfDay();
        // $date_to = Carbon::now()->endOfDay();
        $query_transfer = Transfer::select('to_agent_id', 'price')
            ->where('status', Constants::USER_STATUS_ACTIVE);
        if ($type == 'TO') {
            $query_transfer->where('to_agent_id', $id)
                ->where('type_to', Constants::ACCOUNT_TYPE_STAFF);
        }elseif ($type == "FROM") {
            $query_transfer->where('from_agent_id', $id)
                ->where('type_from', Constants::ACCOUNT_TYPE_STAFF);
        }
        // $query_transfer->whereBetween('created_at', [$date_from, $date_to]);
        $query_transfer->get();
        return (int)$query_transfer->sum('price');
    }
}
