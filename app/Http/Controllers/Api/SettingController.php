<?php

namespace App\Http\Controllers\Api;

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
        $data = [
            0 => [
                'id' => 1,
                'name' => 'Đáo hạn'
            ],
            1 => [
                'id' => 2,
                'name' => 'Rút tiền mặt'
            ],
            2 => [
                'id' => 3,
                'name' => 'Online'
            ],
        ];
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
                'name' => 'Máy Pos'
            ],
            1 => [
                'id' => 2,
                'name' => 'Thanh toán QR Code'
            ],
            2 => [
                'id' => 3,
                'name' => 'Cổng thanh toán'
            ],
        ];
        return response()->json([
            'code' => 200,
            'error' => 'Danh sách phương thức thanh toán',
            'data' => $data
        ]);
    }

}
