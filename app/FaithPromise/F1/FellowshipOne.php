<?php
/**
 * Created by PhpStorm.
 * User: broberts
 * Date: 10/23/15
 * Time: 11:25 AM
 */

namespace App\FaithPromise\F1;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth;
use GuzzleHttp\Exception\ClientException;

class FellowshipOne {

    public function __construct($key, $secret, $uri) {

        $this->settings = [
            'key'    => $key,
            'secret' => $secret,
            'uri'    => $uri
        ];

    }

    public function login($username, $password) {



    }

    public function loginViaRedirect($username, $password) {



    }

}