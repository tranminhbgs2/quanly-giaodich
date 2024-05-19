<?php

namespace App\Repositories\Va;

use App\Models\Va;
use App\Repositories\BaseRepo;

class VaRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
        //
    }

    /**
     * Hàm lấy ds thẻ ảo
     *
     * @param array $params
     * @param false $is_counting
     * @return mixed
     */
    public function listing($params = [], $is_counting=false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $sscid = isset($params['sscid']) ? $params['sscid'] : null;
        $bank_id = isset($params['bank_id']) ? $params['bank_id'] : -1;
        $status = isset($params['status']) ? $params['status'] : -1;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $page_index = ($page_index > 0 && $page_size < 1000001) ? $page_index : 1;
        $page_size = ($page_size > 0 && $page_size < 1001) ? $page_size : 10;
        //
        $query = Va::select([
            'id',
            'school_id',
            'student_id',
            'customer_id',
            'sscid',
            'bank_id',
            'card_number',
            'account_number',
            'owner',
            'bank_name',
            'bank_code',
            'branch',
            'balance',
            'created_by',
            'status',
        ]);

        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('card_number', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('account_number', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('owner', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('bank_name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('bank_code', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('branch', 'LIKE', "%" . $keyword . "%");
            });
        });

        if ($bank_id > 0) {
            $query->where('bank_id', $bank_id);
        }

        if ($status >= 0) {
            $query->where('status', $status);
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
            'parent' => function($sql){
                $sql->select(['id', 'email', 'phone']);
            }
        ]);

        $query->orderBy('updated_at', 'DESC');

        return $query->get();
    }

    /**
     * Hàm lấy thông tin chi tiết 1 thẻ ảo theo id
     *
     * @param $params
     * @return false
     */
    public function detail($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;

        if ($id) {
            $query = Va::where('id', $id);
            $query->with([
                'bank' => function($sql){
                    $sql->select(['id', 'name', 'code']);
                },
                'student' => function($sql){
                    $sql->select(['id', 'sscid', 'fullname', 'user_id', 'school_id', 'class_id'])->with([
                        'parent' => function($sql){
                            $sql->select(['id', 'fullname', 'email', 'phone']);
                        },
                        'school' => function($sql){
                            $sql->select(['id', 'name']);
                        },
                        'class' => function($sql){
                            $sql->select(['id', 'name']);
                        }
                    ]);
                },
                'parent' => function($sql){
                    $sql->select(['id', 'fullname', 'email', 'phone']);
                },
            ]);

            $data = $query->first();

            if ($data) {
                return [
                    'code' => 200,
                    'error' => 'Thông tin thẻ ảo',
                    'data' => $data
                ];
            } else {
                return [
                    'code' => 404,
                    'error' => 'Không tìm thấy thông tin thẻ ảo',
                    'data' => null
                ];
            }
        }

        return [
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng, thử lại sau.',
            'data' => null
        ];

    }

    public function store()
    {

    }

    public function changeStatus()
    {

    }

    public function cancel()
    {

    }

}
