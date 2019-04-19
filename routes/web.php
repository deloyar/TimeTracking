<?php

Route::group(['prefix' => 'api/v1'], function() {

    Route::resource('project', 'ProjectController', [
        'except' => ['edit', 'create']
    ]);

    Route::resource('task', 'TaskController', [
        'except' => ['edit', 'create']
    ]);

    /*Route::resource('report', 'ReportController', [
        'except' => ['edit', 'create']
    ]);
    */

    Route::post('report', [
        'uses' => 'ReportController@create'
    ]);

    Route::post('user', [
        'uses' => 'AuthController@store'
    ]);

    Route::post('user/signin', [
        'uses' => 'AuthController@signin'
    ]);
});

