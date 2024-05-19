<?php

namespace App\Repositories\Log;

use App\Models\LogAuth;
use App\Repositories\BaseRepo;

class LogRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
        //
    }

    /**
     * Hàm lấy ds lịch sử phiên làm việc
     *
     * @param array $params
     * @param false $is_counting
     * @return mixed
     */
    public function sessionListing($params = [], $is_counting=false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $action_type = isset($params['action_type']) ? $params['action_type'] : 'ALL';
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $query = LogAuth::select([
            'id',
            'account_type',
            'session_id',
            'user_id',
            'action_type',
            'logged_in_at',
            'account_input',
            'logged_out_at',
            'user_agent',
            'duration',
            'ip_address',
            'result',
        ]);
        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('action_type', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('account_input', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('ip_address', 'LIKE', "%" . $keyword . "%");
            });
        });

        if ($action_type != 'ALL') {
            $query->where('action_type', strtoupper($action_type));
        }

        if ($is_counting) {
            return $query->count();
        } else {
            $offset = ($page_index - 1) * $page_size;
            if ($page_size > 0 && $offset >= 0) {
                $query->take($page_size)->skip($offset);
            }
        }

        $query->with([
            'user' => function($sql) {
                $sql->select(['id', 'username', 'fullname']);
            }
        ]);

        $query->orderBy('id', 'DESC');

        return $query->get();
    }

    public function actionListing($params = [], $is_counting=false)
    {

    }

}
