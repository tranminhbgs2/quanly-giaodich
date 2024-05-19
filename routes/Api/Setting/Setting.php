<?php

Route::group(['prefix' => 'settings'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){

    });

    //
    Route::get('version', 'SettingController@version');

});
