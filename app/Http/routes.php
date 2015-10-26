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

    Route:post('/auth/request-token', function() {

        $key = env('F1_KEY');
        $secret = env('F1_SECRET');
        $uri = env('F1_API_URI');

        $f1 = new \App\FaithPromise\F1\FellowshipOne($key, $secret, $uri);

        $test = $f1->obtainRequestToken();

        return response()->json(['oauth_token' => $test->oauth_token]);

    });

    Route::post('authenticate', 'AuthenticateController@authenticate');

});

Route::group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function() {

    Route::get('/', 'AuthenticateController@index');

});
