<?php

/**
 * http://ssc.dcv.vn/api/v1/customers
 * http://ssc.dcv.vn/api/v1/me/ssc-cards
 *
 * http://ssc.dcv.vn/api/v1/customers/detail
 */
Route::group(['prefix' => 'customers'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'CustomerController@getListing');
        Route::get('/detail/{id}', 'CustomerController@getDetail');
        Route::post('/store', 'CustomerController@store');
        Route::post('/update/{id}', 'CustomerController@update');
        Route::post('/delete/{id}', 'CustomerController@delete');

    });

});

/**
 * {{url}}/api/v1/me/
 */
Route::group(['prefix' => 'me'], function (){
    Route::group(['middleware' => ['auth.jwt']], function () {
        Route::post('/update-avatar', 'CustomerController@updateAvatar');       // Cập nhật thông tin avatar
        Route::post('/change-password', 'CustomerController@changePassword');   // Thay đổi mk

    });

});
