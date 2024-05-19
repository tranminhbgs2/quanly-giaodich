<?php

/**
 * API login
 * http://ssc.dcv.vn/api/v1/announcements
 */


Route::group(['prefix' => 'announcements'], function (){

    Route::group(['middleware' => ['filter.signed']], function (){
        Route::get('/', 'AnnouncementController@getListing');
        Route::get('/detail/{id}', 'AnnouncementController@getDetail');

    });


    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/unread-counter', 'AnnouncementController@unreadCounter');
        Route::post('/store', 'AnnouncementController@store');
        Route::post('/delete/{id}', 'AnnouncementController@delete');
        Route::post('/batch-delete/{ids}', 'AnnouncementController@batchDelete');


    });




});

