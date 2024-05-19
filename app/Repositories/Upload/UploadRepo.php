<?php

namespace App\Repositories\Upload;

use App\Helpers\Constants;
use App\Models\Customer;
use App\Repositories\BaseRepo;

class UploadRepo extends BaseRepo
{
    private $customer_model;

    public function __construct(Customer $customer)
    {
        parent::__construct();

        $this->customer_model = $customer;
    }

    /**
     * Hàm xử lý upload ảnh nói chung
     *
     * @param $params
     * @param $request
     * @param null $model
     * @return false|string|string[]|null
     */
    public function processUpload($params, $request, $model=null)
    {
        $param_fill['scope'] = isset($params['scope']) ? $params['scope'] : null;
        $param_fill['field_name'] = isset($params['field_name']) ? $params['field_name'] : null;
        if ($request->hasFile('image_file')) {
            $image_file = $request->file('image_file');
            switch ($params['scope']) {
                case 'VERIFY_USER':
                    $filename =  $param_fill['field_name'] . '_' . $model->uid . '.' . $image_file->getClientOriginalExtension();
                    $db_path_save = $this->_processUpload($image_file, $filename, $param_fill['scope']);
                    if ($db_path_save) {
                        $model->{$param_fill['field_name']} = $db_path_save;
                        $model->save();
                        //
                        return $model->{$param_fill['field_name']};
                    }
                case 'DCV_ASSET':
                    // Tải ảnh tài sản thiết bị cho DCV
                    $filename =  $param_fill['field_name'] . '_' . $model->id . '.' . $image_file->getClientOriginalExtension();
                    $db_path_save = $this->_processUpload($image_file, $filename, $param_fill['scope']);
                    if ($db_path_save) {
                        $model->{$param_fill['field_name']} = $db_path_save;
                        $model->save();
                        //
                        return $model->{$param_fill['field_name']};
                    }
                    break;
                case 'DCV_CHECK_IN':
                    $filename =  $param_fill['field_name'] . '_' . time() . '.' . $image_file->getClientOriginalExtension();
                    $db_path_save = $this->_processUpload($image_file, $filename, $param_fill['scope']);
                    return $db_path_save;
                    break;
                case 'UPLOAD_SLIDE':
                    $filename =  time() . '.' . $image_file->getClientOriginalExtension();
                    $db_path_save = $this->_processUpload($image_file, $filename, $param_fill['scope']);
                    return $db_path_save;
                    break;
                default:
            }
        }

        return null;

    }

    /**
     * Hàm xử lý upload avatar
     *
     * @param $params
     * @param $request
     * @return false|string|string[]|null
     */
    public function processUploadAvatar($params, $request)
    {
        $param_fill['scope'] = isset($params['scope']) ? $params['scope'] : null;
        $param_fill['field_name'] = isset($params['field_name']) ? $params['field_name'] : null;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename =  $param_fill['field_name'] . '.' . $avatar->getClientOriginalExtension();
            $db_path_save = $this->_processUpload($avatar, $filename, $param_fill['scope']);
            return $db_path_save;
        }

        return null;

    }

    public function processUploadLogo($params, $request)
    {
        $param_fill['scope'] = isset($params['scope']) ? $params['scope'] : null;
        $param_fill['field_name'] = isset($params['field_name']) ? $params['field_name'] : null;
        if ($request->hasFile('logo')) {
            $avatar = $request->file('logo');
            $filename =  $param_fill['field_name'] . '.' . $avatar->getClientOriginalExtension();
            $db_path_save = $this->_processUpload($avatar, $filename, $param_fill['scope']);
            return $db_path_save;
        }

        return null;

    }

    /**
     * Hàm xử lý upload chung avatar, logo, icon ảnh app
     *
     * @param $params
     * @param $request
     * @return false|string|string[]|null
     */
    public function processUploadByType($params, $request)
    {
        $param_fill['scope'] = isset($params['scope']) ? $params['scope'] : null;
        $param_fill['field_name'] = isset($params['field_name']) ? $params['field_name'] : null;
        //
        $db_path_save = null;
        $file_upload = null;
        switch ($param_fill['scope']) {
            case 'UPLOAD_AVATAR':
                if ($request->hasFile('avatar')) { $file_upload = $request->file('avatar'); }
                break;
            case 'UPLOAD_LOGO':
                if ($request->hasFile('logo')) { $file_upload = $request->file('logo'); }
                break;
            case 'UPLOAD_ICON':
                if ($request->hasFile('icon')) { $file_upload = $request->file('icon'); }
                break;
            case 'UPLOAD_BANNER':
                if ($request->hasFile('image')) { $file_upload = $request->file('image'); }
                break;
        }

        if ($file_upload) {
            $filename =  $param_fill['field_name'] . '.' . $file_upload->getClientOriginalExtension();
            $db_path_save = $this->_processUpload($file_upload, $filename, $param_fill['scope']);
        }

        return $db_path_save;
    }

    /**
     * Hàm xử lý upload ảnh vào storage
     *
     * @param $file
     * @param $filename
     * @return false|string|string[]
     */
    private function _processUpload($file, $filename, $scope='VERIFY_USER')
    {
        if (is_file($file) && $filename) {
            $base_path = null;
            switch ($scope){
                case 'VERIFY_USER': $base_path = Constants::UPLOAD_VERIFY_USER_PATH; break;
                case 'UPLOAD_AVATAR': $base_path = Constants::UPLOAD_AVATAR; break;
                case 'UPLOAD_LOGO': $base_path = Constants::UPLOAD_LOGO; break;
                case 'UPLOAD_ICON': $base_path = Constants::UPLOAD_ICON; break;
                case 'FEEDBACK_ATTACK_IMAGE': $base_path = Constants::UPLOAD_FEEDBACK_ATTACK_IMAGE; break;
                case 'DCV_ASSET': $base_path = Constants::UPLOAD_DCV_ASSET; break;
                case 'DCV_CHECK_IN': $base_path = Constants::UPLOAD_DCV_CHECK_IN; break;
                case 'UPLOAD_SLIDE': $base_path = Constants::UPLOAD_SLIDE; break;
                case 'UPLOAD_BANNER': $base_path = Constants::UPLOAD_BANNER; break;
            }

            if ($base_path) {
                // Tạo thư mục lưu lại nếu chưa tồn tại
                $dir_save_path = $base_path . '/' . date('Y/m');
                if (!file_exists($dir_save_path)) {
                    mkdir($dir_save_path, 0775, true);
                }

                try {
                    $db_path = $file->storeAs($dir_save_path, $filename);
                    return str_replace('public/', '', $db_path);
                } catch (\Exception $e) {
                    return false;
                }
            }

            return false;

        } else {
            return false;
        }
    }
}
