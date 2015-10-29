<?php

namespace App\Http\Controllers;

use FaithPromise\FellowshipOne\AuthFacade;
use Illuminate\Http\Request;
use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller {

    public function authenticate(Request $request) {

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));

    }

    /**
     * Called by client (Satellizer) to get an OAuth request token
     * from FellowshipOne
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestToken() {

        return response()->json([
            'oauth_token'    => AuthFacade::obtainRequestToken(),
            'oauth_callback' => url('/v1/auth/access-token') // TODO: Use named route
        ]);

    }

    /**
     * Successful FellowshipOne login will redirect back to our app
     * and call this method.
     *
     * @param Request $request
     * @throws \FaithPromise\FellowshipOne\Exception
     */
    public function accessToken(Request $request) {

        $key = env('F1_KEY');
        $secret = env('F1_SECRET');
        $uri = env('F1_API_URI');
        $oauth_token = $request->input('oauth_token');

        $f1 = new Auth($key, $secret, $uri);

        $response = $f1->obtainAccessToken($oauth_token);

        $user = $f1->obtainCurrentUser();

        var_dump($response);
        dd($user);


    }

}
