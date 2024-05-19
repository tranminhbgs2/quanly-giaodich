<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StudentListingRequest;
use App\Repositories\Student\StudentRepo;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $student_repo;

    public function __construct(StudentRepo $studentRepo)
    {
        $this->student_repo = $studentRepo;
    }

    /**
     * API tìm kiếm HS theo SSCID
     * URL: {{url}}/api/v1/students/bills/find-bill-detail
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBySscid()
    {
        $params['sscid'] = request('sscid', null);

        $data = $this->student_repo->searchBySscid($params);

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Thông tin học sinh',
                'data' => $data
            ]);
        }

        return response()->json([
            'code' => 404,
            'error' => 'Không tìm thấy thông tin học sinh',
            'data' => null
        ]);
    }

    /**
     * API tìm kiếm HS theo thông tin cá nhân: họ và tên, ngày sinh, mã trường học
     * URL: {{url}}/api/v1/students/search-by-info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchByInfo()
    {
        $params['student_name'] = request('student_name', null);
        $params['year_of_birth'] = request('year_of_birth', null);
        $params['school_code'] = request('school_code', null);

        $data = $this->student_repo->searchByInfo($params);

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Thông tin học sinh',
                'data' => $data
            ]);
        }

        return response()->json([
            'code' => 404,
            'error' => 'Không tìm thấy thông tin học sinh',
            'data' => null
        ]);
    }

    /**
     * API lấy ds học sinh
     * URL: {{url}}/api/v1/students
     *
     * @param StudentListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listing(StudentListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->student_repo->listing($params);
        $total = $this->student_repo->listing($params, true);

        if ($data) {
            return response()->json([
                'code' => 200,
                'error' => 'Danh sách học sinh',
                'data' => $data,
                'meta' => [
                    'page_index' => intval($params['page_index']),
                    'page_size' => intval($params['page_size']),
                    'records' => $total,
                    'pages' => ceil($total / $params['page_size'])
                ]
            ]);
        }

        return response()->json([
            'code' => 404,
            'error' => 'Không tìm thấy thông tin học sinh',
            'data' => null
        ]);
    }
}
