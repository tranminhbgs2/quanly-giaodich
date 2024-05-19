<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Upload\UploadImageRequest;
use App\Models\Customer;
use App\Repositories\Upload\UploadRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    protected $uploadRepository;

    public function __construct(UploadRepo $uploadRepository)
    {
        $this->uploadRepository = $uploadRepository;
    }

    /**
     * API upload ảnh theo scope (model) và lưu đường dẫn vào field nào
     *
     * @param UploadImageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(UploadImageRequest $request)
    {
        $params['scope'] = request('scope', 'VERIFY_USER');
        $params['field_name'] = request('field_name', null);

        switch ($params['scope']) {
            case 'VERIFY_USER':
                $model = Customer::where('uid', Auth::user()->admin_id)->first();
        }

        $path = $this->uploadRepository->processUpload($params, $request, $model);

        return response()->json([
            'code' => 200,
            'error' => 'Đường dẫn ảnh vừa tải lên',
            'data' => [
                'path' => $path
            ]
        ]);

    }
}
