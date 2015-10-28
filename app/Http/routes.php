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


Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function() {

    Route::post('/auth/request-token', 'FellowshipOneAuthController@requestToken'); // TODO: switch back to POST
    Route::any('/auth/access-token', 'FellowshipOneAuthController@accessToken');

    Route::post('authenticate', 'AuthenticateController@authenticate');

    Route::get('test', function() {

        $uri = 'https://foo.com/this/that';
        $uri = preg_replace('/(?:\.json)+$/', '', $uri) . '.json';

        dd($uri);

        return 'test page here';
    });
});

Route::group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function() {

    Route::get('/', 'AuthenticateController@index');

});
