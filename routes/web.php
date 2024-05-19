<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/**
 * http://ssc.dcv.vn/webview/dieu-khoan-dich-vu
 * http://ssc.dcv.vn/webview/chinh-sach-bao-mat
 */
Route::group([
    'namespace' => 'Webview',
    'prefix' => 'webview',
    'middleware' => []
], function () {
    Route::get('dieu-khoan-dich-vu', 'WebviewController@termsOfService')->name('terms_of_service');
    Route::get('chinh-sach-bao-mat', 'WebviewController@privacyPolicy')->name('privacy_policy');

});

Route::group([
    'namespace' => 'Common',
    'middleware' => ['cors']
], function () {
    // http://ssc.dcv.vn/deep-link-app-android
    // Route::get('deep-link-app-android', 'CommonController@android')->name('deep_link_android');

});
