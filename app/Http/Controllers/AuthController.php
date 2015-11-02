<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use FaithPromise\FellowshipOne\AuthFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
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
        if (!$request->has('oauth_token')) {
            return $this->handleRequestToken($request);
        }

        // Second step for access token
        return $this->handleAccessToken($request);

    }

    public function register(Request $request) {

        // Validate email
        if (!preg_match('/@faithpromise\.org$')) {
            throw new \Exception('Faith Promise email address required.');
        }

        $data = [];
        $data['email'] = $request->input('email');
        $data['verification_token'] = 'fp-' . str_random(16);
        $data['verification_url'] = url('/verify-email?t=' . $data['verification_token']);

        Cache::put($data['verification_token'], $data['email'], 5);

        Mail::send('emails.register', ['data' => $data], function ($message) use ($data) {
            $message->from('dev@faithpromise.org');
            $message->to($data['email']);
        });

        return true;
    }

    public function verifyEmail(Request $request) {

//        $payload = JWTAuth::parseToken()->getPayload();
//        dd($payload->get(''));

        if ($email = Cache::get($request->input('t'))) {

            $payload = JWTAuth::parseToken()->getPayload();



            // Create the user
            $user = new User;
            $user->email = $email;
            $user->password = 'login_via_f1_' . str_random(35);
            $user->fellowship_one_user_id = $payload->get('fellowship_one_user_id');
            $user->save();


            $new_token = JWTAuth::fromUser($user, [
                'oauth_token'        => $auth['oauth_token'],
                'oauth_token_secret' => $auth['oauth_token_secret']
            ]);

        }

        return response()->json(['message' => 'Validation email expired.'], 404);

    }

    private function handleRequestToken(Request $request) {

        return response()->json([
            'oauth_token'    => AuthFacade::obtainRequestToken(),
            'oauth_callback' => $request->server('HTTP_REFERER')
        ]);

    }

    private function handleAccessToken(Request $request) {

        $auth = AuthFacade::obtainAccessToken($request->input('oauth_token'));
        $user = User::whereFellowshipOneUserId($auth['user_id'])->first();

        if (!$user) {
            $user = new User();
            $user->id = 0;
        }

        try {

            $token = JWTAuth::fromUser($user, [
                'oauth_token'            => $auth['oauth_token'],
                'oauth_token_secret'     => $auth['oauth_token_secret'],
                'fellowship_one_user_id' => $auth['user_id']
            ]);

            if (!$token) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token', 'user'));

    }

}
