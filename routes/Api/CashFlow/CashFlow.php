<?php

/**
 * {{url}}/api/v1/agent
 * {{url}}/api/v1/agent/no-auth
 */
Route::group(['prefix' => 'cash-flow'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'CashFlowController@getListing');
        Route::get('/detail/{id}', 'CashFlowController@getDetail');
        Route::post('/store', 'CashFlowController@store');
        Route::post('/update', 'CashFlowController@update');
        Route::get('/delete/{id}', 'CashFlowController@delete');
        Route::post('/change-status', 'CashFlowController@changeStatus');
    });

});
