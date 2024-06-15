<?php

Route::group(['prefix' => 'settings'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){

    });

    //
    Route::get('version', 'SettingController@version');

});

Route::group(['prefix' => 'dropdown'], function (){
    Route::group(['middleware' => ['auth.jwt']], function (){
        Route::get('/agent', 'AgentController@getAll');
        Route::get('/bank', 'BankController@getAll');
        Route::get('/bank-account', 'BankAccountController@getAll');
        Route::get('/category', 'CategoryController@getAll');
        Route::get('/ho-kinh-doanh', 'HoKinhDoanhController@getAll');
        Route::get('/pos', 'PosController@getAll');
        Route::get('/hinh-thuc', 'SettingController@getHinhThuc');
        Route::get('/phuong-thuc', 'SettingController@getPhuongThuc');
        Route::get('/function', 'DepartmentController@getAll');
        Route::get('/action', 'PositionController@getAll');
        Route::get('/action-by-func/{func_id}', 'PositionController@getAllByFunc');
        Route::get('/type-transfer', 'SettingController@getTypeTransfer');
        Route::get('/staff', 'UserController@getAllStaff');
        Route::get('/type-card', 'SettingController@getTypeCard');
        Route::get('/group-account', 'SettingController@getGroupAccount');
    });

});
