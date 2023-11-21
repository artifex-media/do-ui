<?php 


$namespace = "Doorons\DoUI\Http\Controllers";

Route::group(['namespace' => $namespace, 'prefix' => 'dashboard','middleware' => 'auth'], function () {
    Route::get('/resources/{type}', 'ResourceController@parse');
});