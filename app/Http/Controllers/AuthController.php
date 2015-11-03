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

        // Make sure username ends with '@faithpromise.org'
        $email = preg_replace('/(?:@faithpromise\.org)+$/', '', $request->input('username')) . '@faithpromise.org';

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('A valid Faith Promise email address is required to register.');
        }

        $data = [];
        $data['email'] = $email;
        $data['verification_token'] = 'fp-' . str_random(16);
        $data['verification_url'] = config('site.admin_url') . '/verify-email/' . $data['verification_token'];

        Cache::put($data['verification_token'], $data['email'], 5);

        // TODO: Remove return
//        return $data['verification_url'];

        Mail::send('emails.register', ['data' => $data], function ($message) use ($data) {
            $message
                ->from('dev@faithpromise.org')
                ->to($data['email'])
                ->subject('Faith Promise: Verify your email address');
        });

    }

    public function verifyEmail(Request $request) {

//        $payload = JWTAuth::parseToken()->getPayload();
//        dd($payload->get(''));

        $email = Cache::get($request->input('token'));

        if ($email) {

            $payload = JWTAuth::parseToken()->getPayload();

            $user = User::whereEmail($email)->first();

            // Create the user
            if (!$user) {
                $user = new User;
                $user->email = $email;
                $user->password = 'login_via_f1_' . str_random(35);
            }

            $user->fellowship_one_user_id = $payload->get('fellowship_one_user_id');
            $user->save();

            $token = JWTAuth::fromUser($user, [
                'oauth_token'        => $payload->get('oauth_token'),
                'oauth_token_secret' => $payload->get('oauth_token_secret')
            ]);

            // TODO: Limit what is returned back in user object
            return response()->json(compact('token', 'user'));

        }

        return response()->json(['message' => 'Validation email expired: ' . $email], 404); // TODO:

    }

    private function handleRequestToken(Request $request) {

        return response()->json([
            'oauth_token'    => AuthFacade::obtainRequestToken(),
            'oauth_callback' => $request->server('HTTP_REFERER')
        ]);

    }

    private function handleAccessToken(Request $request) {

        $result = [];
        $auth = AuthFacade::obtainAccessToken($request->input('oauth_token'));
        $user = User::whereFellowshipOneUserId($auth['user_id'])->first();
        $claims = [
            'oauth_token'            => $auth['oauth_token'],
            'oauth_token_secret'     => $auth['oauth_token_secret'],
            'fellowship_one_user_id' => $auth['user_id']
        ];

        if ($user) {
            $result['user'] = $user->toArray();
        } else {
            $user = new User();
            $user->id = 0;
        }

        try {

            $result['token'] = JWTAuth::fromUser($user, $claims);

            if (!$result['token']) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json($result);

    }

}
