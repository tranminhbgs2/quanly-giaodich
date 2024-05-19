<?php

Route::group([], function (){
    /**
     * Cập nhật token của device
     * http://ssc.dcv.vn/api/v1/update-device-token
     */
    Route::post('update-device-token', 'DeviceController@updateDeviceToken');
});
