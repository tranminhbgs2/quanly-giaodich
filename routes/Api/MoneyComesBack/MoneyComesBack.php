<?php

/**
 * {{url}}/api/v1/lo-tien-ve
 * {{url}}/api/v1/transaction/no-auth
 */
Route::group(['prefix' => 'lo-tien-ve'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'MoneyComesBackController@getListing');
        Route::get('/detail/{id}', 'MoneyComesBackController@getDetail');
        Route::post('/store', 'MoneyComesBackController@store');
        Route::post('/update', 'MoneyComesBackController@update');
        Route::get('/delete/{id}', 'MoneyComesBackController@delete');
        Route::post('/change-status', 'MoneyComesBackController@changeStatus');
    });

});
