<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ChangePasswordRequest;
use App\Http\Requests\Customer\DeleteRequest;
use App\Http\Requests\Customer\GetDetailRequest;
use App\Http\Requests\Customer\GetListingRequest;
use App\Http\Requests\Customer\CusStoreRequest;
use App\Http\Requests\Customer\CusUpdateAvatarRequest;
use App\Http\Requests\Customer\CusUpdateRequest;
use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Repositories\Customer\CustomerRepo;
use App\Repositories\Upload\UploadRepo;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected $customer_repo;
    protected $upload_repo;

    public function __construct(CustomerRepo $customerRepo, UploadRepo $uploadRepo)
    {
        $this->customer_repo = $customerRepo;
        $this->upload_repo = $uploadRepo;
    }

    /**
     * API lấy ds khách hàng
     * URL: {{url}}/api/v1/customers
     *
     * @param GetListingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListing(GetListingRequest $request)
    {
        $params['keyword'] = request('keyword', null);
        $params['status'] = request('status', -1);
        $params['page_index'] = request('page_index', 1);
        $params['page_size'] = request('page_size', 10);

        $data = $this->customer_repo->getListing($params, false);
        $total = $this->customer_repo->getListing($params, true);

        if ($data) {
            $data = collect($data)->map(function ($customer){
                if ($customer['last_login']) {
                    $customer['last_login'] = date('d/m/Y H:i', strtotime(str_replace('/', '-', $customer['last_login'])));
                }
                return $customer;
            })->all();
        }

        return response()->json([
            'code' => 200,
            'error' => 'Danh sách khách hàng',
            'data' => $data,
            'meta' => [
                'page_index' => intval($params['page_index']),
                'page_size' => intval($params['page_size']),
                'records' => $total,
                'pages' => ceil($total / $params['page_size'])
            ]
        ]);
    }

    /**
     * API lấy thông tin chi tiết khách hàng
     * URL: {{url}}/api/v1/customers/detail/8
     *
     * @param GetDetailRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(GetDetailRequest $request, $sscid)
    {
        if ($sscid) {
            $params['id'] = request('id', null);
            $data = $this->customer_repo->getDetail($params);

        } else {
            $data = [
                'code' => 422,
                'error' => 'Truyền thiếu SSC-ID',
                'data' => null
            ];
        }

        return response()->json($data);
    }

    /**
     * API thêm mới KH từ CMS
     * URL: {{url}}/api/v1/customers/store
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params['fullname'] = request('fullname', null);
        $params['phone'] = request('phone', null);
        $params['email'] = request('email', null);
        if ($request->hasFile('avatar')) {
            $params['avatar'] = $request->file('avatar');
        } else {
            $params['avatar'] = null;
        }
        $params['username'] = request('username', null);
        $params['password'] = request('password', null);
        $params['password_confirmation'] = request('password_confirmation', null);

        // Xử lý upload
        if ($params['avatar']) {
            $path = $this->upload_repo->processUploadAvatar([
                'scope' => 'UPLOAD_AVATAR',
                'field_name' => strtolower($params['username']),
            ], $request);

            $params['avatar'] = $path;
        }

        $resutl = $this->customer_repo->store($params);

        if ($resutl) {
            return response()->json([
                'code' => 200,
                'error' => 'Thêm mới nhân viên thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Thêm mới nhân viên không thành công',
            'data' => null
        ]);
    }

    /**
     * API cập nhật thông tin KH theo id
     * URL: {{url}}/api/v1/customers/update/id
     *
     * @param UpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $id)
    {
        if ($id && is_numeric($id)) {
            $params['id'] = request('id', null);
            if ($id == $params['id']) {
                $params['fullname'] = request('fullname', null);
                $phone = request('phone', null);
                $params['email'] = request('email', null);
                $avatar = ($request->hasFile('avatar')) ? $request->file('avatar') : null;
                $birthday = request('birthday', null);
                $params['identifier'] = request('identifier', null);
                $issue_date = request('issue_date', null);
                $params['issue_place'] = request('issue_place', null);
                $params['address'] = request('address', null);
                $params['status'] = request('status', null);

                //
                $params['phone'] = ($phone) ? formatMobile($phone) : null;                      // phone
                $params['birthday'] = ($birthday) ? reformatDate($birthday) : null;             // birthday
                $params['issue_date'] = ($issue_date) ? reformatDate($issue_date) : null;       // issue_date

                // Xử lý upload
                if ($avatar) {
                    $path = $this->upload_repo->processUploadAvatar([
                        'scope' => 'UPLOAD_AVATAR',
                        'field_name' => strtolower($params['phone']),
                    ], $request);

                    $params['avatar'] = $path;
                } else {
                    $params['avatar'] = null;
                }

                $resutl = $this->customer_repo->update($params, $id);

                if ($resutl) {
                    return response()->json([
                        'code' => 200,
                        'error' => 'Cập nhật thông tin nhân viên thành công',
                        'data' => null
                    ]);
                }

                return response()->json([
                    'code' => 400,
                    'error' => 'Cập nhật thông tin nhân viên không thành công',
                    'data' => null
                ]);
            } else {
                return response()->json([
                    'code' => 422,
                    'error' => 'Mã nhân viên không hợp lệ',
                    'data' => null
                ]);
            }
        }

        return response()->json([
            'code' => 422,
            'error' => 'Truyền thiếu id của Nhân viên',
            'data' => null
        ]);

    }

    /**
     * API xóa thông tin khách hàng, xóa trạng thái, ko xóa vật lý
     * URL: {{url}}/api/v1/customers/delete/1202112817000308
     *
     * @param CusDeleteRequest $request
     * @param $sscid
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteRequest $request, $id)
    {
        if ($id) {
            $params['id'] = request('id', null);
            if ($id == $params['sscid']) {
                $data = $this->customer_repo->delete($params);
            } else {
                return response()->json([
                    'code' => 422,
                    'error' => 'ID không hợp lệ',
                    'data' => null
                ]);
            }
        } else {
            $data = [
                'code' => 422,
                'error' => 'Truyền thiếu ID',
                'data' => null
            ];
        }

        return response()->json($data);
    }

    /**
     * API Cập nhật avatar cho KH
     * URL: {{url}}/api/v1/me/update-avatar
     *
     * @param CusUpdateAvatarRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(CusUpdateAvatarRequest $request)
    {
        $params['avatar'] = ($request->hasFile('avatar')) ? $request->file('avatar') : null;

        // Xử lý upload
        if ($params['avatar']) {
            $path = $this->upload_repo->processUploadAvatar([
                'scope' => 'UPLOAD_AVATAR',
                'field_name' => strtolower(Auth::user()->username),
            ], $request);

            if ($path) {
                $params['avatar'] = $path;
                $result = $this->customer_repo->updateAvatar($params, Auth::user());
                if ($result) {
                    return response()->json([
                        'code' => 200,
                        'error' => 'Cập nhật thông avatar thành công',
                        'data' => [
                            'avatar' => asset('storage/' . $path)
                        ]
                    ]);
                } else {
                    return response()->json([
                        'code' => 400,
                        'error' => 'Cập nhật thông avatar không thành công',
                        'data' => null
                    ]);
                }
            }
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
            'data' => null
        ]);
    }

    /**
     * API thay đổi mật khẩu
     * URL: {{url}}/api/v1/me/change-password
     *
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $params['password'] = request('password', null);

        $result = $this->customer_repo->changePassword($params, Auth::user());

        if ($result) {
            return response()->json([
                'code' => 200,
                'error' => 'Thay đổi mật khẩu thành công',
                'data' => null
            ]);
        }

        return response()->json([
            'code' => 400,
            'error' => 'Đã có lỗi xảy ra. Bạn vui lòng thử lại sau',
            'data' => null
        ]);
    }
}
