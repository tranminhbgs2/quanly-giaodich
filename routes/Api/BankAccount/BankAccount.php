<?php

/**
 * {{url}}/api/v1/bank-account
 *
 * {{url}}/api/v1/bank-account/no-auth
 */
Route::group(['prefix' => 'bank-account'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'BankAccountController@getListing');
        Route::get('/detail/{id}', 'BankAccountController@getDetail');
        Route::post('/store', 'BankAccountController@store');
        Route::post('/update', 'BankAccountController@update');
        Route::get('/delete/{id}', 'BankAccountController@delete');
        Route::post('/change-status', 'BankAccountController@changeStatus');
    });

});
