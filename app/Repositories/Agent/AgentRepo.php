<?php

namespace App\Repositories\Agent;

use App\Events\ActionLogEvent;
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
            'bankAccounts' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code', 'agent_id']);
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
            $res = Agent::create($insert);
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
            'bankAccounts' => function ($sql) {
                $sql->select(['id', 'account_number', 'account_name', 'bank_code', 'agent_id']);
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
        $agent = Agent::where('id', $id)->first();

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

    public function getAll()
    {
        return Agent::select('id', 'name')->where('status', Constants::USER_STATUS_ACTIVE)->orderBy('id', 'DESC')->get()->toArray();
    }

    public function updateBalance($id, $balance, $action = "")
    {
        $bank = Agent::where('id', $id)->first();
        // Lưu log qua event
        event(new ActionLogEvent([
            'actor_id' => auth()->user()->id ?? 0,
            'username' => auth()->user()->username ?? 0,
            'action' => 'UPDATE_BANLANCE_AGENT',
            'description' => $action. ' Cập nhật số tiền cho Đại lý ' . $bank->name . ' từ ' . $bank->balance . ' thành ' . $balance,
            'data_new' => $balance,
            'data_old' => $bank->balance,
            'model' => 'Agent',
            'table' => 'agency',
            'record_id' => $id,
            'ip_address' => request()->ip()
        ]));

        $update = ['balance' => $balance];
        return $bank->update($update);
    }
}
