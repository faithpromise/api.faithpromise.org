<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use FaithPromise\FellowshipOne\AuthFacade;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller {

    /**
     * Called by client (Satellizer) to get an OAuth request token
     * from FellowshipOne, then a second time to get access token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function fellowshipone(Request $request) {

        // First step for request token
        if (! $request->has('oauth_token')) {
            return $this->handleRequestToken($request);
        }

        // Second step for access token
        return $this->handleAccessToken($request);

    }

    private function handleRequestToken(Request $request) {

        return response()->json([
            'oauth_token'    => AuthFacade::obtainRequestToken(),
            'oauth_callback' => $request->server('HTTP_REFERER')
        ]);

    }

    private function handleAccessToken(Request $request) {

        $oauth_token = $request->input('oauth_token');

        $auth = AuthFacade::obtainAccessToken($oauth_token);

        $fellowship_one_user_id = $auth->getUserId();

        $user = User::whereFellowshipOneUserId($fellowship_one_user_id)->first();

        if ($user) {

            try {
                if (!$token = JWTAuth::fromUser($user)) {
                    return response()->json(['error' => 'invalid_credentials'], 401);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }

            return response()->json(compact('token'));

        }

        // No user found
        // TODO: Return msg to client for email verification step
        throw new \Exception('No user found yo');

    }

}
