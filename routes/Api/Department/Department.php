<?php

/**
 * {{url}}/api/v1/function
 * {{url}}/api/v1/transaction/no-auth
 */
Route::group(['prefix' => 'function'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'DepartmentController@getListing');
        Route::get('/detail/{id}', 'DepartmentController@getDetail');
        Route::post('/store', 'DepartmentController@store');
        Route::post('/update', 'DepartmentController@update');
        Route::get('/delete/{id}', 'DepartmentController@delete');
        Route::post('/change-status', 'DepartmentController@changeStatus');
        Route::get('/get-all', 'DepartmentController@getAll');
    });

});
