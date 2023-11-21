<?php 


$namespace = "Doorons\DoUI\Http\Controllers";

Route::group(['namespace' => $namespace, 'prefix' => 'dashboard','middleware' => 'web'], function () {
    Route::post('/resources/{type}', 'ResourceController@parse')->name('resources.action');
});