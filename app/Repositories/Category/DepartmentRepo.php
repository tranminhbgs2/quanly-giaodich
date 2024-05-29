<?php

namespace App\Repositories\Category;

use App\Helpers\Constants;
use App\Models\Department;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class DepartmentRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hàm lấy ds phòng ban
     *
     * @param $params
     * @param false $is_counting
     * @return mixed
     */
    public function listing($params, $is_counting = false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $query = Department::select('id', 'name', 'code', 'description', 'url', 'status', 'is_default');

        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('code', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('code', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('description', 'LIKE', "%" . $keyword . "%");
            });
        });

        if ($is_counting) {
            return $query->count();
        } else {
            $offset = ($page_index - 1) * $page_size;
            if ($page_size > 0 && $offset >= 0) {
                $query->take($page_size)->skip($offset);
            }
        }

        $query->with([
            'actionsFunc' => function ($sql) {
                $sql->select(['id', 'function_id', 'name', 'code']);
            }
        ]);
        $query->orderBy('id', 'DESC');

        return $query->get();
    }

    /**
     * Hàm lấy thông tin phòng ban theo id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id, $with_trashed = false)
    {
        //check $id is not null
        if (empty($id)) {
            return null;
        }
        $tran = Department::select()
        ->with(['actionsFunc' => function ($sql) {
            $sql->select(['id', 'function_id', 'name', 'code']);
        }])
        ->where('id', $id);
        if($with_trashed){
            $tran->withTrashed();
        }

        $data = $tran->first();
        return $data;
    }

    public function store($params)
    {
        $fillable = [
            'name',
            'code',
            'description',
            'url',
            'status',
            'is_default',
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['code']) && !empty($insert['name'])) {
            return Department::create($insert) ? true : false;
        }

        return false;
    }

    public function update($params, $id)
    {
        $fillable = [
            'name',
            'code',
            'description',
            'url',
            'status',
            'is_default',
        ];

        $update = [];

        foreach ($fillable as $field) {
            if (isset($params[$field])) {
                $update[$field] = $params[$field];
            }
        }

        return Department::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $category = Department::find($id);

        if ($category) {
            $category->status = Constants::USER_STATUS_DELETED;
            $category->deleted_at = Carbon::now();

            if ($category->save()) {
                return [
                    'code' => 200,
                    'error' => 'Xóa nhóm quyền thành công',
                    'data' => null
                ];
            } else {
                return [
                    'code' => 400,
                    'error' => 'Xóa nhóm quyền không thành công',
                    'data' => null
                ];
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy nhóm quyền',
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
        $tran = Department::select()
        ->with(['actionsFunc' => function ($sql) {
            $sql->select(['id', 'function_id', 'name', 'code']);
        }])
        ->where('id', $id);

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
    public function changeStatus($status, $id)
    {

        $update = ['status' => $status];

        return Department::where('id', $id)->update($update);
    }

    public function getAll()
    {
        return Department::select('id', 'name', 'code', 'is_default', 'status')
        ->with(['actionsFunc' => function ($sql) {
            $sql->select(['id', 'function_id', 'name', 'code', 'is_default']);
        }])
        ->where('status', Constants::USER_STATUS_ACTIVE)
        ->orderBy('id', 'DESC')->get()->toArray();
    }
    public function attachPositions($departmentId, array $positionIds)
    {
        $department = Department::find($departmentId);
        return $department->positions()->attach($positionIds);
    }

    public function detachAllPositions($departmentId)
    {
        $department = Department::find($departmentId);
        return $department->positions()->detach();
    }
}
