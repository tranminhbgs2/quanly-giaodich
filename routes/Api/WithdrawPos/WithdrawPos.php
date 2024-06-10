<?php

/**
 * {{url}}/api/v1/withdraw-pos
 *
 * {{url}}/api/v1/withdraw-pos/no-auth
 */
Route::group(['prefix' => 'withdraw-pos'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'WithdrawPosController@getListing');
        Route::get('/detail/{id}', 'WithdrawPosController@getDetail');
        Route::post('/store', 'WithdrawPosController@store');
        Route::post('/update', 'WithdrawPosController@update');
        Route::get('/delete/{id}', 'WithdrawPosController@delete');
        Route::post('/change-status', 'WithdrawPosController@changeStatus');
        Route::get('/get-all', 'WithdrawPosController@getAll');
    });

});
