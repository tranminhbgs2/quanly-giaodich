<?php

Route::group([
    'namespace' => 'Dev',
    'prefix' => 'v1/test',
    'middleware' => []
], function () {
    Route::post('push-firebase-notification', 'PushController@pushFirebaseNotification');
    Route::post('test-reformat', 'PushController@testReformat');
});
