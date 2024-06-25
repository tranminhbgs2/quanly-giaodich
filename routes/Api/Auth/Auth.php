<?php

/**
 * API login
 * http://ssc.dcv.vn/api/v1/auth/login
 * http://ssc.dcv.vn/api/v1/auth/app-register
 * http://ssc.dcv.vn/api/v1/auth/customer-register
 * http://ssc.dcv.vn/api/v1/auth/customer-get-otp
 * http://ssc.dcv.vn/api/v1/auth/customer-forgot-password
 * http://ssc.dcv.vn/api/v1/auth/logout
 * http://ssc.dcv.vnm/api/v1/auth/refresh
 */
Route::post('login', 'AuthController@login');

Route::group(['middleware' => ['filter.signed']], function (){
    Route::post('app-register', 'AuthController@appRegister');
    Route::post('reset-password', 'AuthController@resetPassword');
    Route::get('check-web-order', 'AuthController@checkWebOrder');
    Route::get('sync-user', 'UserController@syncBalance');
    Route::get('sync-lo-tien-ve', 'MoneyComesBackController@syncMoneyComesBack');
    Route::get('sync-lo-ket-toan', 'MoneyComesBackController@syncLoKetToan');
    Route::get('sync-ho-kinh-doanh', 'HoKinhDoanhController@syncBalance');
    Route::get('sync-agency', 'AgentController@syncBalance');
});

Route::group(['middleware' => ['auth.jwt']], function (){
    Route::post('change-password', 'AuthController@changePassword');

    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');

});
