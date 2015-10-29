<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use FaithPromise\F1\FellowshipOne;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class FellowshipOneAuthController extends BaseController {

    public function requestToken(Request $request) {

        $key = env('F1_KEY');
        $secret = env('F1_SECRET');
        $uri = env('F1_API_URI');

        $f1 = new \App\FaithPromise\F1\FellowshipOne($key, $secret, $uri);

        $oauth_token = $f1->obtainRequestToken();

        return response()->json([
            'oauth_token'    => $oauth_token,
            'oauth_callback' => url('/v1/auth/access-token') // TODO: Use named route
        ]);

    }

    public function accessToken(Request $request) {

        $key = env('F1_KEY');
        $secret = env('F1_SECRET');
        $uri = env('F1_API_URI');
        $oauth_token = $request->input('oauth_token');

        $f1 = new \App\FaithPromise\F1\FellowshipOne($key, $secret, $uri);

        $response = $f1->obtainAccessToken($oauth_token);

        $user = $f1->obtainCurrentUser();

        var_dump($response);
        dd($user);


    }

}
