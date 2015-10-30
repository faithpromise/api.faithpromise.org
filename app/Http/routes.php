<?php

use Illuminate\Support\Facades\Route;

/**
 * Authentication
 */
Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function() {

    Route::any('/auth/request-token', ['as' => 'requestToken', 'uses' => 'AuthController@requestToken']);
    Route::any('/auth/access-token', ['as' => 'accessToken', 'uses' => 'AuthController@accessToken']);

//    Route::post('authenticate', 'AuthController@authenticate');

});

/**
 * API
 */
Route::group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function() {

    Route::get('/', 'AuthenticateController@index');

});
