<?php

/**
 * http://ssc.dcv.vn/api/v1/departments
 */
Route::group(['prefix' => 'departments'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'DepartmentController@listing');

    });

});
