<?php

namespace App\Repositories\Student;

use App\Helpers\Constants;
use App\Models\Customer;
use App\Models\School;
use App\Repositories\BaseRepo;
use App\Services\SSC\SscService;
use Illuminate\Support\Facades\Log;

class SchoolRepo extends BaseRepo
{
    protected $ssc_service;

    public function __construct(SscService $sscService)
    {
        parent::__construct();
        //
        $this->ssc_service = $sscService;

    }

    /**
     * API lấy ds trường học
     * URL: {{url}}/api/v1/schools
     *
     * @param $params
     * @return array|null
     */
    public function listing($params = [], $is_counting = false)
    {
        //$res = $this->ssc_service->getSchools();

        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        $status = isset($params['status']) ? $params['status'] : -1;
        //
        $scope = isset($params['scope']) ? $params['scope'] : Constants::SCOPE_APP;
        //
        $page_index = ($page_index > 0) ? $page_index : 1;
        $page_size = ($page_size > 0) ? $page_size : 10;

        if ($scope == Constants::SCOPE_APP) {
            $query = School::selectRaw('name AS school_name, code AS school_code, province_id AS province, address');
        } else {
            $query = School::select([
                'id',
                'name',
                'headmaster',
                'code',
                'logo',
                'address',
                'phone',
                'fax',
                'email',
                'status',
            ]);
        }

        $query->when(! is_null($keyword), function ($sql) use ($keyword){
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($subsql) use ($keyword){
                $subsql->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('headmaster', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('address', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $keyword . '%');
            });
        });

        // Lọc theo trạng thái
        if ($status >= 0) {
            $query->where('status', $status);
        }

        if ($is_counting) {
            return $query->count();
        }

        // Xử lý phân trang
        if ($page_index && $page_size) {
            $query->take($page_size)->skip(($page_index - 1)*$page_size);
        }

        return $query->get()->toArray();
    }

    /**
     * Hàm lấy chi tiết thông tin đối tác
     *
     * @param $params
     * @return Customer|\Illuminate\Database\Eloquent\Model|null
     */
    public function detail($params, $with_trashed=false)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $scope = isset($params['scope']) ? $params['scope'] : null;

        if ($id) {
            $partner = School::select([
                'id',
                'province_id',
                'district_id',
                'name',
                'headmaster',
                'code',
                'logo',
                'address',
                'phone',
                'fax',
                'email'
            ])->where('id', $id);

            $partner->with([
                'province' => function($sql){
                    $sql->select(['id', 'name']);
                },
                'district' => function($sql){
                    $sql->select(['id', 'name']);
                },
                'apps' => function($sql){
                    $sql->select(['id', 'name']);
                },
            ]);

            if ($with_trashed) {
                $partner->withTrashed();
            }

            $data = $partner->first();

            if ($data) {
                return [
                    'code' => 200,
                    'message' => 'Thông tin chi tiết đối tác',
                    'data' => $data
                ];
            } else {
                return [
                    'code' => 404,
                    'message' => 'Không tìm thấy thông tin chi tiết đối tác',
                    'data' => null
                ];
            }
        }

        return [
            'code' => 422,
            'message' => 'Mã đối tác không hợp lệ hoặc truyền thiếu',
            'data' => null
        ];

    }

    /**
     * Hàm tạo thông tin Khách hàng, Nhân viên
     *
     * @param $params
     * @return bool
     */
    public function store($params)
    {
        $code = isset($params['code']) ? $params['code'] : null;
        $name = isset($params['name']) ? $params['name'] : null;
        $headmaster = isset($params['headmaster']) ? $params['headmaster'] : null;
        $phone = isset($params['phone']) ? $params['phone'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $address = isset($params['address']) ? $params['address'] : null;
        $logo = isset($params['logo']) ? $params['logo'] : null;
        $app_list = isset($params['app_list']) ? $params['app_list'] : null;

        if ($code && $name && $headmaster && $phone && $address && $app_list) {
            $partner = new School();
            $partner->fill([
                'province_id' => null,
                'district_id' => null,
                'name' => $name,
                'headmaster' => $headmaster,
                'code' => $code,
                'logo' => $logo,
                'address' => $address,
                'phone' => $phone,
                'fax' => null,
                'email' => $email
            ]);

            if ($partner->save()) {
                // Cập nhật gán sscid cho KH nếu có
                if (is_array($app_list) && count($app_list) > 0) {
                    $partner->apps()->sync($app_list, false);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Hàm cập nhật thông tin KH theo id
     *
     * @param $params
     * @param $id
     * @return bool
     */
    public function update($params, $id)
    {
        $update = [];

        (isset($params['code']) && $params['code']) ? $update['code'] = $params['code'] : null;
        (isset($params['name']) && $params['name']) ? $update['name'] = $params['name'] : null;
        (isset($params['headmaster']) && $params['headmaster']) ? $update['headmaster'] = $params['headmaster'] : null;

        (isset($params['phone']) && $params['phone']) ? $update['phone'] = $params['phone'] : null;
        (isset($params['email']) && $params['email']) ? $update['email'] = $params['email'] : null;
        (isset($params['address']) && $params['address']) ? $update['address'] = $params['address'] : null;
        (isset($params['logo']) && $params['logo']) ? $update['logo'] = $params['logo'] : null;

        $app_list = isset($params['app_list']) ? $params['app_list'] : null;

        $partner = School::where('id', $id)->update($update);
        if ($partner) {
            // Cập nhật gán sscid cho KH nếu có
            $partner = School::find($id);
            if ($partner && is_array($app_list) && count($app_list) > 0) {
                $partner->apps()->sync($app_list);
            }

            return true;
        }

        return false;
    }

    /**
     * Hàm khóa thông tin đối tác, khóa trạng thái, ko xóa vật lý
     *
     * @param $params
     * @return array
     */
    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $partner = School::where('id', $id)->update(['status' => Constants::SCHOOL_STATUS_LOCKED]);

        if ($partner) {
            return [
                'code' => 200,
                'message' => 'Khóa thông tin đối tác thành công',
                'data' => null
            ];
        } else {
            return [
                'code' => 400,
                'message' => 'Khóa thông tin đối tác không thành công',
                'data' => null
            ];
        }
    }

}
