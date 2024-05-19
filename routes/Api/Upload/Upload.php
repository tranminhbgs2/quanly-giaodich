<?php

/**
 * http://ssc.dcv.vn/api/v1/uploads/upload-image
 */
Route::group(['prefix' => 'uploads'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::post('/upload-image', 'UploadController@uploadImage');
    });

});
