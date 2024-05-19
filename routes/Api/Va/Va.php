<?php

/**
 * {{url}}/api/v1/vas
 * {{url}}/api/v1/vas/detail
 */
Route::group(['prefix' => 'vas'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'VaController@listing');
        Route::get('/detail', 'VaController@detail');
        Route::post('/store', 'VaController@store');
        Route::post('/change-status', 'VaController@changeStatus');
        Route::post('/cancel', 'VaController@cancel');
    });

});
