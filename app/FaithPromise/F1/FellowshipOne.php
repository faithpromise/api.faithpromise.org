<?php

namespace App\FaithPromise\F1;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Session;
use \OAuth;

class FellowshipOne implements FellowshipOneInterface {

    const F1_REQUEST_TOKEN_PATH = '/Tokens/RequestToken';

    public function __construct($key, $secret, $uri) {

        $this->settings = [
            'key'    => $key,
            'secret' => $secret,
            'uri'    => $uri
        ];

    }

    public function obtainRequestToken() {

        $url = $this->settings['uri'] . self::F1_REQUEST_TOKEN_PATH;
        $client = new \OAuth($this->settings['key'], $this->settings['secret'], OAUTH_SIG_METHOD_HMACSHA1);

        $response = (object)$client->getAccessToken($url);

        /* oauth_token & oauth_token_secret */
        Session::put('f1tokens', $response);

        return $response;

    }

    public function login($username, $password) {


    }

    public function loginViaRedirect($username, $password) {


    }
}
//
//    {#183
//        +"oauth_token": "22b2f714-1888-49b1-b708-e37a59a7046c"
//    +"oauth_token_secret": "4eecf0c6-be93-47d8-b8bf-6cfa1bba6567"
//}