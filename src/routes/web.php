<?php 


$namespace = "Doorons\DoUI\Http\Controllers";

Route::group(['namespace' => $namespace, 'prefix' => 'dashboard'], function () {
    Route::post('/resources/{type}', 'ResourceController@parse')->name('resources.action');
});