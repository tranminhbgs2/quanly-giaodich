<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api'], function () {

    Route::group(['prefix' => 'v1'], function (){
        //includeRouteFiles(__DIR__ . '/Api/Customer/');
        //includeRouteFiles(__DIR__ . '/Api/Device/');
        //includeRouteFiles(__DIR__ . '/Api/Notification/');
        //includeRouteFiles(__DIR__ . '/Api/Payment/');
        //includeRouteFiles(__DIR__ . '/Api/Setting/');
        //includeRouteFiles(__DIR__ . '/Api/Slide/');
        //includeRouteFiles(__DIR__ . '/Api/Statistic/');
        //includeRouteFiles(__DIR__ . '/Api/Transaction/');

        Route::group(['prefix' => 'auth'], function (){
            includeRouteFiles(__DIR__ . '/Api/Auth/');
        });

        includeRouteFiles(__DIR__ . '/Api/Setting/');
        includeRouteFiles(__DIR__ . '/Api/Announcement/');
        includeRouteFiles(__DIR__ . '/Api/Bank/');
        includeRouteFiles(__DIR__ . '/Api/Common/');
        includeRouteFiles(__DIR__ . '/Api/Customer/');
        includeRouteFiles(__DIR__ . '/Api/Log/');
        includeRouteFiles(__DIR__ . '/Api/Student/');
        includeRouteFiles(__DIR__ . '/Api/Upload/');
        includeRouteFiles(__DIR__ . '/Api/Transaction/');
        includeRouteFiles(__DIR__ . '/Api/Category/');
        includeRouteFiles(__DIR__ . '/Api/Pos/');
        includeRouteFiles(__DIR__ . '/Api/HoKinhDoanh/');
        includeRouteFiles(__DIR__ . '/Api/Agent/');
        includeRouteFiles(__DIR__ . '/Api/BankAccount/');
        includeRouteFiles(__DIR__ . '/Api/MoneyComesBack/');
        includeRouteFiles(__DIR__ . '/Api/WithdrawPos/');
        includeRouteFiles(__DIR__ . '/Api/Transfer/');
        includeRouteFiles(__DIR__ . '/Api/Department/');
        includeRouteFiles(__DIR__ . '/Api/Position/');
        includeRouteFiles(__DIR__ . '/Api/CashFlow/');
    });
});

includeRouteFiles(__DIR__ . '/Dev/');

