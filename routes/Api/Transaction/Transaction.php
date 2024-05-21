<?php

/**
 * {{url}}/api/v1/transaction
 * {{url}}/api/v1/transaction/no-auth
 */
Route::group(['prefix' => 'transaction'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'TransactionController@getListing');
        Route::get('/detail/{id}', 'TransactionController@getDetail');
        Route::post('/store', 'TransactionController@store');
        Route::post('/update', 'TransactionController@update');
        Route::get('/delete/{id}', 'TransactionController@delete');
        Route::post('/change-status', 'TransactionController@changeStatus');
    });

});
