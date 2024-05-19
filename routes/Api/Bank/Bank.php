<?php

/**
 * {{url}}/api/v1/banks
 * {{url}}/api/v1/banks/no-auth
 */
Route::group(['prefix' => 'banks'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'BankController@listing');
    });

});
