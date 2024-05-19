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

}
