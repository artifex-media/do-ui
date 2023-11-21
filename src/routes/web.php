<?php 


$namespace = "Doorons\DoUI\Http\Controllers";

Route::group(['namespace' => $namespace, 'prefix' => 'do_ui'], function () {
    Route::get('/resources/{type}', 'ResourceController@parse');
});