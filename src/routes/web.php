<?php 


$namespace = "Doorons\DoUI\Http\Controllers";

Route::group(['namespace' => $namespace, 'prefix' => 'dashboard'], function () {
    Route::get('/resources/{type}', 'ResourceController@parse');
});