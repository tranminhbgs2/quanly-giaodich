<?php

namespace App\Repositories\Bank;

use App\Helpers\Constants;
use App\Models\Bank;
use App\Repositories\BaseRepo;

class BankRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
        //
    }

    /**
     * Hàm lấy ds bank
     *
     * @param array $params
     * @param false $is_counting
     * @return mixed
     */
    public function listing($params = [], $is_counting=false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $query = Bank::select([
            'id',
            'code',
            'prifix',
            'name',
            'logo',
        ]);

        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('code', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('name', 'LIKE', "%" . $keyword . "%");
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

        $query->orderBy('code', 'ASC');

        return $query->get();
    }

    public function getAll()
    {
        return Bank::select('id', 'name', 'code')->where('is_active', Constants::USER_STATUS_ACTIVE)->orderBy('id', 'ASC')->get()->toArray();
    }
}
