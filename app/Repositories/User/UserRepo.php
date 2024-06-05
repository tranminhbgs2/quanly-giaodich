<?php
namespace App\Repositories\User;

use App\Helpers\Constants;
use App\Models\User;
use App\Repositories\BaseRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserRepo extends BaseRepo
{
    public function getListing($params, $is_counting = false)
    {
        $status = $params['status'] ?? -1;
        $page_index = $params['page_index'] ?? 1;
        $page_size = $params['page_size'] ?? 10;
        $date_from = $params['date_from'] ?? null;
        $date_to = $params['date_to'] ?? null;

        $query = User::select()->with('agency');

        if ($date_from && $date_to) {
            $query->whereBetween('created_at', [$date_from, $date_to]);
        }

        if ($status >= 0) {
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
            'agent_id',
            'bank_code',
            'account_number',
            'account_name',
            'balance',
            'status'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['account_number']) && !empty($insert['bank_code']) && !empty($insert['account_name'])){
            return User::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'agent_id',
            'bank_code',
            'account_number',
            'account_name',
            'balance',
            'status'
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return User::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $bankAccount = User::find($id);

        if ($bankAccount) {
            $bankAccount->status = Constants::USER_STATUS_DELETED;
            $bankAccount->deleted_at = Carbon::now();

            if ($bankAccount->save()) {
                return [
                    'code' => 200,
                    'error' => 'Xóa user thành công',
                    'data' => null
                ];
            } else {
                return [
                    'code' => 400,
                    'error' => 'Xóa user không thành công',
                    'data' => null
                ];
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy tài khoản ngân hàng',
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
        $tran = User::where('id', $id)->with(['userPermissions' => function ($query) {
            $query->select('positions.id as action_id', 'positions.name as action_name', 'positions.code as action_code'); // Chọn các trường cụ thể từ bảng positions
        }]);

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
        $tran = User::where('id', $id)->with(['userPermissions' => function ($query) {
            $query->select('positions.id as action_id', 'positions.name as action_name', 'positions.code as action_code'); // Chọn các trường cụ thể từ bảng positions
        }]);

        if ($with_trashed) {
            $tran->withTrashed();
        }

        return $tran->first();
    }

    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return User::where('id', $id)->update($update);
    }

    public function getRoles($user_id = null)
    {
        $group_id = auth()->user()->group_id;
        // $query = Group::where('id', $group_id)->where('is_active', 1);
        // $query->with([
        //     'roles' => function($sql){
        //         $sql->select('*');
        //     }
        // ]);

        // $group = $query->get();

        // if (isset($group[0])) {
        //     $roles =  collect($group[0]->roles)->map(function ($item){
        //         return $item->code;
        //     })->all();
        // } else {
        //     $roles = [];
        // }
        // return $roles;
    }

    public function getPermission($user_id = null)
    {
        // $user_id = auth()->id;
        // echo 'a';
        $query = User::where('id', Auth::id());
        $query->with([
            'permissions' => function($sql){
                $sql->select('*');
            }
        ]);
        $result = $query->get();
        if (isset($result[0])) {
            $permissions =  collect($result[0]->permissions)->map(function ($item){
                return $item->code;
            })->all();
        } else {
            $permissions = [];
        }

        // print_r($permissions);
        return $permissions;
    }

    public function getPermissionUser($user_id = null)
    {
        // $user_id = auth()->id;
        // echo 'a';
        $query = User::where('id', $user_id);
        $query->with([
            'permissions' => function($sql){
                $sql->select('*');
            }
        ]);
        $result = $query->get();
        if (isset($result[0])) {
            $data['permissions'] =  collect($result[0]->permissions)->map(function ($item){
                return $item->permission_id;
            })->all();
            $roles = collect($result[0]->permissions)->map(function ($item){
                return $item->role_id;
            })->all();
            $roles = collect($roles)->unique();
            $data['roles'] = $roles->values()->all();
        } else {
            $data = [
                'permissions' => [],
                'roles' => []
            ];
        }
        // print_r($permissions);
        return $data;
    }
    public function getAllStaff()
    {
        return User::select('id', 'fullname', 'status')->where('status', Constants::USER_STATUS_ACTIVE)->where('account_type', 'STAFF')->orderBy('id', 'DESC')->get()->toArray();
    }
}
