<?php

/**
 * http://ssc.dcv.vn/api/v1/students/search-by-sscid
 * http://ssc.dcv.vn/api/v1/students/search-by-info
 * http://ssc.dcv.vn/api/v1/students
 */
Route::group(['prefix' => 'students'], function (){

    Route::get('/search-by-sscid', 'StudentController@searchBySscid');
    Route::get('/search-by-info', 'StudentController@searchByInfo');

    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'StudentController@listing');

    });


});
