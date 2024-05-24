<?php

/**
 * {{url}}/api/v1/transfer
 * {{url}}/api/v1/transfer/no-auth
 */
Route::group(['prefix' => 'transfer'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'TransferController@getListing');
        Route::get('/detail/{id}', 'TransferController@getDetail');
        Route::post('/store', 'TransferController@store');
        Route::post('/update', 'TransferController@update');
        Route::get('/delete/{id}', 'TransferController@delete');
        Route::post('/change-status', 'TransferController@changeStatus');
    });

});
