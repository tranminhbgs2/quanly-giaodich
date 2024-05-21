<?php

/**
 * {{url}}/api/v1/category
 * {{url}}/api/v1/transaction/no-auth
 */
Route::group(['prefix' => 'category'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'CategoryController@getListing');
        Route::get('/detail/{id}', 'CategoryController@getDetail');
        Route::post('/store', 'CategoryController@store');
        Route::post('/update', 'CategoryController@update');
        Route::get('/delete/{id}', 'CategoryController@delete');
        Route::post('/change-status', 'CategoryController@changeStatus');
    });

});
