<?php

/**
 * {{url}}/api/v1/transaction
 * {{url}}/api/v1/transaction/no-auth
 */
Route::group(['prefix' => 'transaction'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'TransactionController@getListing');
        // Route::get('/cashback', 'TransactionController@getListingCashBack');
        Route::get('/detail/{id}', 'TransactionController@getDetail');
        Route::post('/store', 'TransactionController@store');
        Route::post('/update', 'TransactionController@update');
        Route::get('/delete/{id}', 'TransactionController@delete');
        Route::post('/change-status', 'TransactionController@changeStatus');
        Route::get('/report-dashboard', 'TransactionController@ReportDashboard');
        Route::post('/payment-fee', 'TransactionController@PaymentFee');
        Route::get('/cashback', 'MoneyComesBackController@getListingCashBack');
        Route::post('/chart-dashboard', 'TransactionController@ChartDashboard');
        Route::get('/restore-fee/{id}', 'TransactionController@RestoreFee');
        Route::get('/get-all-by-hkd', 'TransactionController@GetAllHkd');
        Route::get('/get-top-staff', 'TransactionController@GetTopStaff');
    });

});
