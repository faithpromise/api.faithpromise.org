<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//require base_path('vendor/fellowshipone/f1api-php/src/fellowshipone/api.php');

Route::group(['prefix' => 'v1'], function() {

    Route::post('authenticate', 'AuthenticateController@authenticate');

});

Route::group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function() {

    Route::get('/', 'AuthenticateController@index');

});
