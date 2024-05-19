<?php

namespace App\Repositories\Category;

use App\Models\Department;
use App\Repositories\BaseRepo;

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
        $query = Department::select(['id', 'name', 'code']);

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
            'positions' => function($sql) {
                $sql->select(['id', 'department_id', 'name', 'code']);
            }
        ]);

        return $query->get();
    }

    /**
     * Hàm lấy thông tin phòng ban theo id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        //check $id is not null
        if (empty($id)) {
            return null;
        }
        return Department::find($id);
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
