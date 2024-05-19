<?php

namespace App\Repositories\Student;

use App\Models\Student;
use App\Repositories\BaseRepo;
use App\Services\FSC\FscService;
use App\Services\SSC\SscService;

class StudentRepo extends BaseRepo
{
    protected $ssc_service;
    protected $fscService;

    public function __construct(SscService $sscService, FscService $fscService)
    {
        parent::__construct();
        //
        $this->ssc_service = $sscService;
        $this->fscService = $fscService;

    }

    /**
     * API tìm kiếm HS theo SSCID
     * URL: {{url}}/api/v1/students/search-by-sscid
     *
     * @param $params
     * @return array|null
     */
    public function searchBySscid($params)
    {
        $sscid = isset($params['sscid']) ? $params['sscid'] : null;

        if ($sscid) {
            $this->ssc_service->setSscid($sscid);
            return $this->ssc_service->findStudentById();
        }

        return null;

    }

    /**
     * API tìm kiếm HS theo thông tin cá nhân: họ và tên, ngày sinh, mã trường học
     * URL: {{url}}/api/v1/students/search-by-info
     *
     * @param $params
     * @return array
     */
    public function searchByInfo($params)
    {
        return $this->ssc_service->findStudent($params);
        //return $this->fscService->findStudent($params);
    }

    /**
     * Hàm lấy ds học sinh
     *
     * @param array $params
     * @param false $is_counting
     * @return mixed
     */
    public function listing($params = [], $is_counting=false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $status = isset($params['status']) ? $params['status'] : -1;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $page_index = ($page_index > 0 && $page_size < 1000001) ? $page_index : 1;
        $page_size = ($page_size > 0 && $page_size < 1001) ? $page_size : 10;
        //
        $query = Student::select([
            'id',
            'user_id',
            'school_id',
            'class_id',
            'fullname',
            'sscid',
            'birthday',
            'gender',
            'avatar',
            'address',
            'email',
            'phone',
            'school_name',
            'class_name',
            'va_id',
            'status',
        ]);

        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('fullname', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('sscid', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('address', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('email', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('phone', 'LIKE', "%" . $keyword . "%");
            });
        });

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
                $sql->select(['id', 'email', 'phone', 'status']);
            },
            /*'school' => function($sql){
                $sql->select(['id', 'name', 'code', 'status']);
            },*/
            /*'class' => function($sql){
                $sql->select(['id', 'name', 'code']);
            },*/
            'va' => function($sql){
                $sql->select(['id', 'account_number', 'owner', 'bank_name', 'bank_code', 'branch']);
            },
        ]);

        $query->orderBy('id', 'DESC');

        return $query->get();
    }



}
