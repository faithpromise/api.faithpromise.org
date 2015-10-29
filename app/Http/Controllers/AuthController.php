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

        $oauth_token = $request->input('oauth_token');

        $auth = AuthFacade::obtainAccessToken($oauth_token);

        $user = $auth->obtainCurrentUser();

        var_dump($user);

    }

}
