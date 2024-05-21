<?php

/**
 * {{url}}/api/v1/agent
 * {{url}}/api/v1/agent/no-auth
 */
Route::group(['prefix' => 'agent'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/', 'AgentController@getListing');
        Route::get('/detail/{id}', 'AgentController@getDetail');
        Route::post('/store', 'AgentController@store');
        Route::post('/update', 'AgentController@update');
        Route::get('/delete/{id}', 'AgentController@delete');
        Route::post('/change-status', 'AgentController@changeStatus');
    });

});
