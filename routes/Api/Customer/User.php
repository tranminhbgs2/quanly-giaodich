<?php

/**
 * http://ssc.dcv.vn/api/v1/users
 * http://ssc.dcv.vn/api/v1/users/delete/1
 */
Route::group(['prefix' => 'users'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        // Route::get('/', 'UserController@listing');
        // Route::get('/detail/{id}', 'UserController@detail');
        // Route::post('/store', 'UserController@store');
        // Route::post('/update/{id}', 'UserController@update');
        // Route::post('/delete/{id}', 'UserController@delete');
    });

});
