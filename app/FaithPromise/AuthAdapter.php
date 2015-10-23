<?php

namespace App\FaithPromise;

use Exception;
use F1\API;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Providers\Auth\AuthInterface;

class AuthAdapter implements AuthInterface {
    /**
     * @var \Illuminate\Auth\AuthManager
     */
    protected $auth;

    /**
     * @param \Illuminate\Auth\AuthManager $auth
     */
    public function __construct(AuthManager $auth) {
        $this->auth = $auth;
    }

    /**
     * Check a user's credentials
     *
     * @param  array $credentials
     * @return bool
     */
    public function byCredentials(array $credentials = []) {


        try {
            $this->fellowshipOneAuth($credentials['email'], $credentials['password']);
            return $this->byId();
        } catch(\F1\Exception $e) {
            return false;
        } catch(Exception $e) {
            return false;
        }

        dd('byCredentials');

        return $this->auth->once($credentials);


    }

    /**
     * Authenticate a user via the id
     *
     * @param  mixed $id
     * @return bool
     */
    public function byId($id) {
        try {
            return $this->auth->onceUsingId($id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the currently authenticated user
     *
     * @return mixed
     */
    public function user() {
        return $this->auth->user();
    }

    private function fellowshipOneAuth($username, $password = null) {

        $f1 = App::make('faithpromise.fellowshipone.api');

//        $f1 = new API([
//            'key'     => env('F1_KEY'),
//            'secret'  => env('F1_SECRET'),
//            'baseUrl' => env('F1_API_URI')
//        ]);

        if (App::environment('local')) {
            $f1->debug = true;
        }

        $f1->login2ndParty(
            $username,
            $password,
            API::TOKEN_CACHE_CUSTOM,
            [
                'setAccessToken' => function($username, $token = null) {

                },
                'getAccessToken' => function($username) {

                }
            ]
        );
    }
}
