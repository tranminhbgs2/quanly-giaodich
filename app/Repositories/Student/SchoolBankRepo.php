<?php

namespace App\Repositories\Student;

use App\Helpers\Constants;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\School;
use App\Models\SchoolBank;
use App\Repositories\BaseRepo;

class SchoolBankRepo extends BaseRepo
{
    protected $ssc_service;

    public function __construct()
    {
        parent::__construct();
        //
    }

    /**
     * Hàm lấy ds tài khoản giao dịch của đối tác
     *
     * @param $params
     * @return array|null
     */
    public function listing($params = [], $is_counting = false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $status = isset($params['status']) ? $params['status'] : -1;
        $partner_id = isset($params['partner_id']) ? $params['partner_id'] : -1;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        //
        $scope = isset($params['scope']) ? $params['scope'] : Constants::SCOPE_APP;
        //
        $page_index = ($page_index > 0) ? $page_index : 1;
        $page_size = ($page_size > 0) ? $page_size : 10;

        if ($scope == Constants::SCOPE_APP) {
            $query = SchoolBank::select('*');
        } else {
            $query = SchoolBank::select([
                'id',
                'school_id',
                'bank_id',
                'account_number',
                'owner',
                'branch',
                'bank_name',
                'bank_code',
                'card_number',
                'status',
            ]);
        }

        $query->when(! is_null($keyword), function ($sql) use ($keyword){
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($subsql) use ($keyword){
                $subsql->where('account_number', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('owner', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('branch', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('bank_code', 'LIKE', '%' . $keyword . '%');
            });
        });

        // Lọc theo trạng thái
        if ($status >= 0) { $query->where('status', $status); }

        if ($partner_id >= 0) { $query->where('school_id', $partner_id); }

        if ($is_counting) { return $query->count(); }

        // Xử lý phân trang
        if ($page_index && $page_size) {
            $query->take($page_size)->skip(($page_index - 1)*$page_size);
        }

        $query->with([
            'partner' => function($sql){ $sql->select(['id', 'name', 'code', 'headmaster', 'logo']); },
            'bank' => function($sql){ $sql->select(['id', 'name', 'code', 'prifix', 'logo']); },
        ]);

        return $query->get();
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
        $fill_params['school_id'] = isset($params['partner_id']) ? $params['partner_id'] : null;
        $fill_params['bank_id'] = isset($params['bank_id']) ? $params['bank_id'] : null;
        $fill_params['account_number'] = isset($params['account_number']) ? $params['account_number'] : null;
        $fill_params['owner'] = isset($params['owner']) ? $params['owner'] : null;
        $fill_params['branch'] = isset($params['branch']) ? $params['branch'] : null;
        $fill_params['status'] = 1;

        if ($fill_params['school_id'] && $fill_params['bank_id'] && $fill_params['account_number'] && $fill_params['owner']) {
            $bank = Bank::find($fill_params['bank_id']);
            if ($bank) {
                $fill_params['bank_name'] = $bank->name;
                $fill_params['bank_code'] = $bank->code;
                SchoolBank::create($fill_params);
                //
                return true;
            }
        }

        return false;
    }

    /**
     * Hàm cập nhật thông tin giao dịch của đối tác
     *
     * @param $params
     * @param $id
     * @return bool
     */
    public function update($params, $id)
    {
        $update = [];

        (isset($params['bank_id']) && $params['bank_id']) ? $update['bank_id'] = $params['bank_id'] : null;
        (isset($params['account_number']) && $params['account_number']) ? $update['account_number'] = $params['account_number'] : null;
        (isset($params['owner']) && $params['owner']) ? $update['owner'] = $params['owner'] : null;
        (isset($params['branch']) && $params['branch']) ? $update['branch'] = $params['branch'] : null;
        (isset($params['card_number']) && $params['card_number']) ? $update['card_number'] = $params['card_number'] : null;
        (isset($params['status']) && $params['status']) ? $update['status'] = $params['status'] : null;

        $partner_bank = SchoolBank::where('id', $id)->update($update);
        if ($partner_bank) {
            return true;
        }

        return false;
    }

    /**
     * Hàm xóa tài khoản giao dịch của đối tác
     *
     * @param $params
     * @return array
     */
    public function delete($params)
    {
        $id = isset($params['id']) ? $params['id'] : null;
        $partner = SchoolBank::where('id', $id)->delete();

        if ($partner) {
            return true;
        }

        return false;
    }

}
