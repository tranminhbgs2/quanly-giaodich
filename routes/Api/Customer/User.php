<?php

/**
 * http://ssc.dcv.vn/api/v1/users
 * http://ssc.dcv.vn/api/v1/users/delete/1
 */
Route::group(['prefix' => 'users'], function () {
    Route::group(['middleware' => ['auth.jwt']], function () {
        Route::get('/', 'UserController@getListing');
        Route::get('/user-info', 'UserController@getInfo');
        Route::get('/detail/{id}', 'UserController@getDetail');
        Route::post('/store', 'UserController@store');
        Route::post('/update', 'UserController@update');
        Route::get('/delete/{id}', 'UserController@delete');
        Route::post('/change-status', 'UserController@changeStatus');
    });
});
