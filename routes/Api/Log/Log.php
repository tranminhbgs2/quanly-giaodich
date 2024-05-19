<?php

/**
 * {{url}}/api/v1/logs/sessions
 * {{url}}/api/v1/logs/actions
 */
Route::group(['prefix' => 'logs'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/sessions', 'LogController@sessionListing');
        Route::get('/actions', 'LogController@actionListing');
    });
});
