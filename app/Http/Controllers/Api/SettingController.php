<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constants;
use App\Repositories\Setting\VersionRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    protected $versionRepository;

    public function __construct(VersionRepo $versionRepository)
    {
        $this->versionRepository = $versionRepository;
    }

    /**
     * API lấy thông tin version hiện tại của nền tảng muốn lấy
     * URL: {{url}}/api/v1/settings/version?platform=android
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function version(Request $request)
    {
        $platform = $request->input('platform', null);

        if (in_array($platform, ['android', 'ios'])) {
            $result = $this->versionRepository->version($platform);
            return response()->json([
                'code' => ($result) ? 200 : 400,
                'error' => ($result) ? 'Thông tin phiên bản hiện tại' : 'Đã có lỗi xảy ra, Bạn vui lòng thử lại sau',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'code' => 422,
                'error' => 'Truyền thiếu hoặc sai tham số platform (android/ios)',
                'data' => null
            ]);
        }
    }

    /**
     * API lấy danh sách hình thức thanh toán
     * URL: {{url}}/api/v1/dropdown/hinh-thuc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHinhThuc()
    {
        //Kế toán này chỉ xem dc GD POSS
        // if (auth()->user()->id == 2372) {
        //     $data = [
        //         0 => [
        //             'id' => 1,
        //             'name' => 'Đáo hạn',
        //             'code' => 'DAO_HAN'
        //         ],
        //         1 => [
        //             'id' => 2,
        //             'name' => 'Rút tiền mặt',
        //             'code' => 'RUT_TIEN_MAT'
        //         ],
        //     ];
        // } elseif (auth()->user()->id == 2373 || auth()->user()->id == 2370) {
        //     //Kế toán này chỉ xem dc GD Online

        //     $data = [
        //         2 => [
        //             'id' => 3,
        //             'name' => 'Online',
        //             'code' => 'ONLINE'
        //         ],
        //         3 => [
        //             'id' => 4,
        //             'name' => 'QR Code',
        //             'code' => 'QR_CODE'
        //         ],
        //     ];
        // } else {
            $data = [
                0 => [
                    'id' => 1,
                    'name' => 'Đáo hạn',
                    'code' => 'DAO_HAN'
                ],
                1 => [
                    'id' => 2,
                    'name' => 'Rút tiền mặt',
                    'code' => 'RUT_TIEN_MAT'
                ],
                2 => [
                    'id' => 3,
                    'name' => 'Online',
                    'code' => 'ONLINE'
                ],
                3 => [
                    'id' => 4,
                    'name' => 'QR Code',
                    'code' => 'QR_CODE'
                ],
            ];
        // }
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách hình thức thanh toán',
            'data' => $data
        ]);
    }

    /**
     * API lấy danh sách phương thức thanh toán
     * URL: {{url}}/api/v1/dropdown/phuong-thuc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhuongThuc()
    {

        $data = [
            0 => [
                'id' => 1,
                'name' => 'Máy Pos',
                'code' => 'POS'
            ],
            1 => [
                'id' => 2,
                'name' => 'Thanh toán QR Code',
                'code' => 'QR_CODE'
            ],
            2 => [
                'id' => 3,
                'name' => 'Cổng thanh toán',
                'code' => 'GATEWAY'
            ],
        ];
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách phương thức thanh toán',
            'data' => $data
        ]);
    }
    public function getTypeTransfer()
    {
        $data = [
            0 => [
                'name' => 'Tài khoản nguồn',
                'code' => 'MASTER'
            ],
            1 => [
                'name' => 'Tài khoản nhân viên',
                'code' => Constants::ACCOUNT_TYPE_STAFF
            ],
            2 => [
                'name' => 'Tài khoản đại lý',
                'code' => 'AGENCY'
            ],
        ];
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách loại chuyển khoản',
            'data' => $data
        ]);
    }


    /**
     * API lấy danh sách hình thức thanh toán
     * URL: {{url}}/api/v1/dropdown/hinh-thuc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTypeCard()
    {
        $data = [
            0 => [
                'id' => 1,
                'name' => 'Visa',
                'code' => 'VISA'
            ],
            1 => [
                'id' => 2,
                'name' => 'Master',
                'code' => 'MASTER'
            ],
            2 => [
                'id' => 3,
                'name' => 'JCB',
                'code' => 'JCB'
            ],
            3 => [
                'id' => 4,
                'name' => 'Napas',
                'code' => 'NAPAS'
            ],
            4 => [
                'id' => 5,
                'name' => 'AMEX',
                'code' => 'AMEX'
            ],
        ];
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách loại thẻ',
            'data' => $data
        ]);
    }

    /**
     * API lấy danh sách phương thức thanh toán
     * URL: {{url}}/api/v1/dropdown/phuong-thuc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupAccount()
    {
        $data = [
            0 => [
                'id' => 1,
                'name' => 'ADMIN',
                'code' => Constants::ACCOUNT_TYPE_SYSTEM
            ],
            1 => [
                'id' => 2,
                'name' => 'Kế toán',
                'code' => Constants::ACCOUNT_TYPE_ACCOUNTANT
            ],
            2 => [
                'id' => 3,
                'name' => 'Nhân viên',
                'code' => Constants::ACCOUNT_TYPE_STAFF
            ],
        ];
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách nhóm quyền user',
            'data' => $data
        ]);
    }
}
