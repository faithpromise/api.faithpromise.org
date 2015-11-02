<?php

use Illuminate\Support\Facades\Route;

/**
 * Authentication
 */
Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function() {

    Route::any('/auth/fellowshipone', ['as' => 'authEndpoint', 'uses' => 'AuthController@fellowshipone']);
    Route::post('auth/register', 'AuthController@register');
    Route::get('auth/verify-email', 'AuthController@verifyEmail');

});

/**
 * API
 */
Route::group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function() {

    Route::get('/', 'AuthenticateController@index');

});
