<?php

namespace App\Repositories\Customer;

use App\Helpers\Constants;
use App\Models\Customer;
use App\Models\Fee;
use App\Models\Student;
use App\Models\User;
use App\Repositories\BaseRepo;
use App\Services\Email\MailerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerRepo extends BaseRepo
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hàm lấy ds KH, có tìm kiếm và phân trang
     *
     * @param $params
     * @param false $is_counting
     *
     * @return mixed
     */
    public function getListing($params, $is_counting = false)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $status = isset($params['status']) ? $params['status'] : -1;
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        $account_type = isset($params['account_type']) ? $params['account_type'] : Constants::ACCOUNT_TYPE_STAFF;
        $query = User::select([
                'id', 'username',
                'fullname', 'email', 'phone',
                'display_name', 'status', 'last_login', 'department_id as group_id', 'balance',
            ]);

            $query->with([
                'department' => function($sql){
                    $sql->select(['id', 'name', 'code']);
                },
            ]);

        $query->when(!empty($keyword), function ($sql) use ($keyword) {
            $keyword = translateKeyWord($keyword);
            return $sql->where(function ($sub_sql) use ($keyword) {
                $sub_sql->where('username', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('fullname', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('display_name', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('email', 'LIKE', "%" . $keyword . "%")
                    ->orWhere('phone', 'LIKE', "%" . $keyword . "%");
            });
        });

        $query->whereIn('account_type', [Constants::ACCOUNT_TYPE_STAFF, Constants::ACCOUNT_TYPE_ACCOUNTANT]);

        if ($status > 0) {
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

        /*$query->with([
            'department' => function($sql){
                $sql->select(['id', 'name', 'code']);
            }
        ]);*/

        $query->orderBy('id', 'DESC');

        return $query->get()->toArray();
    }

    /**
     * Hàm tạo thông tin Khách hàng, Nhân viên
     *
     * @param $params
     * @return number
     */
    public function store($params)
    {
        $fullname = isset($params['fullname']) ? $params['fullname'] : null;
        $phone = isset($params['phone']) ? $params['phone'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $birthday = isset($params['birthday']) ? $params['birthday'] : null;
        $department_id = isset($params['department_id']) ? $params['department_id'] : null;
        $username = isset($params['username']) ? $params['username'] : null;
        $password = isset($params['password']) ? $params['password'] : null;
        $status = isset($params['status']) ? $params['status'] : Constants::USER_STATUS_ACTIVE;
        $account_type = isset($params['account_type']) ? $params['account_type'] : Constants::ACCOUNT_TYPE_STAFF;

        if ($fullname && $username && $password) {
            $user = new User();
            $user->fill([
                'account_type' => $account_type,
                'username' => $username,
                'fullname' => $fullname,
                'birthday' => $birthday,
                'address' => null,
                'email' => $email,
                'phone' => formatMobile($phone),
                'display_name' => $fullname,
                'password' => Hash::make($password),
                'notes' => null,
                'department_id' => $department_id,
                'status' => $status
            ]);

            if ($user->save()) {
                return $user->id;
            }

        }

        return 0;
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

        (isset($params['fullname']) && $params['fullname']) ? $update['fullname'] = $params['fullname'] : null;
        (isset($params['fullname']) && $params['fullname']) ? $update['display_name'] = $params['fullname'] : null;

        (isset($params['phone']) && $params['phone']) ? $update['phone'] = $params['phone'] : null;
        (isset($params['email']) && $params['email']) ? $update['email'] = $params['email'] : null;
        (isset($params['avatar']) && $params['avatar']) ? $update['avatar'] = $params['avatar'] : null;
        (isset($params['birthday']) && $params['birthday']) ? $update['birthday'] = $params['birthday'] : null;

        (isset($params['department_id']) && $params['department_id']) ? $update['department_id'] = $params['department_id'] : null;
        (isset($params['identifier']) && $params['identifier']) ? $update['identifier'] = $params['identifier'] : null;
        (isset($params['issue_date']) && $params['issue_date']) ? $update['issue_date'] = $params['issue_date'] : null;
        (isset($params['issue_place']) && $params['issue_place']) ? $update['issue_place'] = $params['issue_place'] : null;
        (isset($params['address']) && $params['address']) ? $update['address'] = $params['address'] : null;

        (isset($params['status']) && $params['status']) ? $update['status'] = $params['status'] : null;
        (isset($params['account_type']) && $params['account_type']) ? $update['account_type'] = $params['account_type'] : null;

        $user = User::where('id', $id)->update($update);
        if ($user) {
            return true;
        }

        return false;
    }

    /**
     * Hàm lấy chi tiết thông tin KH
     *
     * @param $params
     */
    public function getDetail($params, $with_trashed=false)
    {
            $message = 'Nhân viên';

            $id = isset($params['id']) ? $params['id'] : null;
            $user = User::select([
                'id',
                'account_type',
                'username',
                'sscid',
                'fullname',
                'birthday',
                'gender',
                'avatar',
                'address',
                'email',
                'phone',
                'display_name',
                'last_login',
                'department_id',
                'status',
                'balance',
            ])
                ->where('id', $id);
            $user->with([
                'department' => function($sql){
                    $sql->select(['id', 'name', 'code']);
                },
                'position' => function($sql){
                    $sql->select(['id', 'name', 'code']);
                },
            ]);

        if ($with_trashed) {
            $user->withTrashed();
        }

        $data = $user->first();

        if ($data) {
            unset($data->email_verified_at);
            unset($data->deleted_at);
            unset($data->data_mode);

            return [
                'code' => 200,
                'error' => 'Thông tin chi tiết ' . $message,
                'data' => $data
            ];
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin chi tiết ' . $message,
                'data' => null
            ];
        }
    }

    /**
     * Hàm khóa thông tin khách hàng vĩnh viễn, khóa trạng thái, ko xóa vật lý
     *
     * @param $params
     * @return array
     */
    public function delete($params)
    {
            $message = 'Nhân viên';

            $id = isset($params['id']) ? $params['id'] : null;
            $user = User::where('id', $id)
                ->first();

        if ($user) {
            if ($user->status == Constants::USER_STATUS_DELETED) {
                return [
                    'code' => 200,
                    'error' => 'Thông tin ' . $message . ' đang bị khóa vĩnh viễn',
                    'data' => null
                ];
            } else {
                $user->status = Constants::USER_STATUS_DELETED;
                $user->deleted_at = Carbon::now();

                if ($user->save()) {
                    return [
                        'code' => 200,
                        'error' => 'Khóa thông tin ' . $message . ' vĩnh viễn thành công',
                        'data' => null
                    ];
                } else {
                    return [
                        'code' => 400,
                        'error' => 'Xóa thông tin ' . $message . ' không thành công',
                        'data' => null
                    ];
                }
            }
        } else {
            return [
                'code' => 404,
                'error' => 'Không tìm thấy thông tin ' . $message,
                'data' => null
            ];
        }
    }

    /**
     * Hàm reset lại mật khẩu
     *
     * @param $params
     * @return bool
     */
    public function resetPassword($params)
    {
        $receiver_by = isset($params['email']) ? $params['email'] : null;

        if ($receiver_by) {
            $new_password = strval(rand(10000000, 99999999));

            $body = getEmailBody(Constants::EMAIL_TYPE_RESET_PASSWORD, [
                'email' => $receiver_by,
                'content' => $new_password
            ]);

            $mailer = new MailerService();
            $res = $mailer->sendSingle($receiver_by, 'Thiết lập lại mật khẩu - ' . $new_password, $body);

            if ($res == 1) {
                User::where([
                    'email' => $receiver_by
                ])->update([
                    'password' => Hash::make($new_password)
                ]);

                return true;
            }
        }

        return false;

    }

    /**
     * Hàm cập nhật avatar cho KH
     *
     * @param $params
     * @param $user_obj
     * @return bool
     */
    public function updateAvatar($params, $user_obj)
    {
        $avatar = isset($params['avatar']) ? $params['avatar'] : null;

        if (is_object($user_obj) && $user_obj && $avatar) {
            $update = User::where('id', $user_obj->id)->update(['avatar' => $avatar]);
            if ($update) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hàm thay đổi mật khẩu
     *
     * @param $params
     * @param $user_obj
     * @return false
     */
    public function changePassword($params, $user_obj)
    {
        $password = isset($params['password']) ? $params['password'] : null;

        if (is_object($user_obj) && $user_obj && $password) {
            $user_obj->password = Hash::make($password);

            if ($user_obj->save()) {
                return true;
            }
        }

        return false;
    }


    /**
     * Hàm tạo mật khẩu để lấy mã OTP
     *
     * @param $password
     * @param $customer
     * @return bool
     */
    public function storeOtpPassword($password, $customer)
    {
        if ($password && is_object($customer)) {
            $customer->otp_password = Hash::make($password);
            $customer->otp_created_at = Carbon::now();

            if ($customer->save()) {
                return true;
            }
        }

        return false;

    }

    /**
     * Hàm cập nhật mk lấy mã OTP
     *
     * @param $password
     * @param $customer
     * @return bool
     */
    public function updateOtpPassword($password, $customer)
    {
        if ($password && is_object($customer)) {
            $customer->otp_password = Hash::make($password);
            $customer->otp_created_at = Carbon::now();

            if ($customer->save()) {
                return true;
            }
        }

        return false;

    }

    public function attachPositions($userId, array $positionIds)
    {
        $department = User::find($userId);
        return $department->userPermissions()->attach($positionIds);
    }

    public function detachAllPositions($userId)
    {
        $department = User::find($userId);
        return $department->userPermissions()->detach();
    }

    public function getByEmail($email)
    {
        return User::where('email', $email)->first();
    }
}
