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

    /**
     * /api/v1/students/bills/search-by-sscid
     * /api/v1/students/bills/find-bill-detail
     * /api/v1/students/bills/pay-bill
     */
    Route::group(['prefix' => 'bills'], function (){

        Route::get('/search-by-sscid', 'BillController@searchBySscid');
        Route::get('/find-bill-detail', 'BillController@findBillDetail');
        Route::post('/pay-bill', 'BillController@payBill');

        Route::post('/create-bill', 'BillController@createBill');

        Route::group(['middleware' => ['auth.jwt']], function (){


        });
    });

});
