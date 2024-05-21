<?php

/**
 * {{url}}/api/v1/pos
 * {{url}}/api/v1/pos/no-auth
 */
Route::group(['prefix' => 'pos'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'PosController@getListing');
        Route::get('/detail/{id}', 'PosController@getDetail');
        Route::post('/store', 'PosController@store');
        Route::post('/update', 'PosController@update');
        Route::get('/delete/{id}', 'PosController@delete');
        Route::post('/assign-pos', 'PosController@assignPosToAgent');
        Route::post('/change-status', 'PosController@changeStatus');
    });

});
