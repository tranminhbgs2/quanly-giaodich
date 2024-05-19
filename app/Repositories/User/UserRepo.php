<?php
namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepo;
use Illuminate\Support\Facades\Auth;

class UserRepo extends BaseRepo
{

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
}
