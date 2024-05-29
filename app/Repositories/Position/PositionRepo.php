<?php

namespace App\Repositories\Position;

use App\Helpers\Constants;
use App\Models\Position;
use App\Repositories\BaseRepo;
use Carbon\Carbon;

class PositionRepo extends BaseRepo
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
        $query = Position::select()
        ->with(['groupRule' => function ($sql) {
            $sql->select(['id', 'name', 'code']);
        }]);

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
            'groupRule' => function ($sql) {
                $sql->select(['id', 'name', 'code']);
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
    public function getById($id)
    {
        //check $id is not null
        if (empty($id)) {
            return null;
        }
        return Position::find($id)->groupRule();
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
            'function_id'
        ];

        $insert = [];

        foreach ($fillable as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                $insert[$field] = $params[$field];
            }
        }

        if (!empty($insert['code']) && !empty($insert['name'])) {
            return Position::create($insert) ? true : false;
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

        return Position::where('id', $id)->update($update);
    }

    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $category = Position::find($id);

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
        $tran = Position::select()
        ->with(['groupRule' => function ($sql) {
            $sql->select(['id', 'name', 'code']);
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

        return Position::where('id', $id)->update($update);
    }

    public function getAll()
    {
        return Position::select('id', 'name', 'code', 'function_id', 'status', 'is_default')
        ->with(['groupRule' => function ($sql) {
            $sql->select(['id', 'name', 'code', 'is_default']);
        }])
        ->where('status', Constants::USER_STATUS_ACTIVE)
        ->orderBy('id', 'DESC')->get()->toArray();
    }

    public function getAllByFunc($func_id)
    {
        return Position::select('id', 'name', 'code', 'function_id', 'status', 'is_default')
        ->with(['groupRule' => function ($sql) {
            $sql->select(['id', 'name', 'code', 'is_default']);
        }])
        ->where('status', Constants::USER_STATUS_ACTIVE)
        ->where('function_id', $func_id)
        ->orderBy('id', 'DESC')->get()->toArray();
    }
    public function attachPositions($PositionId, array $positionIds)
    {
        $Position = Position::find($PositionId);
        return $Position->positions()->attach($positionIds);
    }

    public function detachAllPositions($PositionId)
    {
        $Position = Position::find($PositionId);
        return $Position->positions()->detach();
    }
}
