<?php

/**
 * {{url}}/api/v1/action
 * {{url}}/api/v1/transaction/no-auth
 */
Route::group(['prefix' => 'action'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'PositionController@getListing');
        Route::get('/detail/{id}', 'PositionController@getDetail');
        Route::post('/store', 'PositionController@store');
        Route::post('/update', 'PositionController@update');
        Route::get('/delete/{id}', 'PositionController@delete');
        Route::post('/change-status', 'PositionController@changeStatus');
    });

});
