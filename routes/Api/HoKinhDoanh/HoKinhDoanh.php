<?php

/**
 * {{url}}/api/v1/ho-kinh-doanh
 * {{url}}/api/v1/ho-kinh-doanh/no-auth
 */
Route::group(['prefix' => 'ho-kinh-doanh'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'HoKinhDoanhController@getListing');
        Route::get('/detail/{id}', 'HoKinhDoanhController@getDetail');
        Route::post('/store', 'HoKinhDoanhController@store');
        Route::post('/update', 'HoKinhDoanhController@update');
        Route::get('/delete/{id}', 'HoKinhDoanhController@delete');
        Route::post('/change-status', 'HoKinhDoanhController@changeStatus');
    });

});
