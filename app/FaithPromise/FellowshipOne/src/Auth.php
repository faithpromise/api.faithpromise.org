<?php

namespace FaithPromise\FellowshipOne;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use OAuth;

class Auth implements AuthInterface {

    const F1_REQUEST_TOKEN_PATH = '/Tokens/RequestToken';
    const F1_ACCESS_TOKEN_PATH = '/Tokens/AccessToken';

    const REQUEST_TOKEN_KEY = 'f1_request_token';
    const REQUEST_SECRET_KEY = 'f1_request_secret';
    const ACCESS_TOKEN_KEY = 'f1_request_token';
    const ACCESS_SECRET_KEY = 'f1_request_secret';
    const USER_KEY = 'f1_user';
    const USER_LOCATION_KEY = 'f1_user_location';

    protected $headers = [];
    protected $content_type = 'json';

    public function __construct($key, $secret, $uri) {

        $this->settings = [
            'key'               => $key,
            'secret'            => $secret,
            'uri'               => $uri,
            'uri_request_token' => $uri . self::F1_REQUEST_TOKEN_PATH,
            'uri_access_token'  => $uri . self::F1_ACCESS_TOKEN_PATH
        ];

    }

    public function obtainRequestToken() {

        $request_token = $this
            ->getOauthClient()
            ->getRequestToken($this->settings['uri_request_token']);

        $this->storeRequestToken($request_token);

        return $request_token['oauth_token'];

    }

    public function obtainAccessToken($oauthToken) {

        $request_token = $this->getRequestToken($oauthToken);
        $oauth_token_secret = isset($request_token['oauth_token_secret']) ? $request_token['oauth_token_secret']  : null;

        try {

            $client = $this->getOauthClient($oauthToken, $oauth_token_secret);
            $access_token = $client->getAccessToken($this->settings['uri_access_token']);

            return [
                'oauth_token'        => $access_token['oauth_token'],
                'oauth_token_secret' => $access_token['oauth_token_secret'],
                'user_id'            => $this->getCurrentUserIdFromHeader()
            ];

        } catch (\OAuthException $e) {

            $previous = isset($client) ? $client->getLastResponse() : '';

            throw new Exception($e->getMessage(), $e->getCode(), $previous, ['url' => $this->settings['uri_access_token']], $e);
        }

    }

    public function getUserId() {
        $user = $this->getUser();

        return $user['@id'];
    }

    public function getCurrentUserIdFromHeader() {

        $location_header = $this->getContentLocationHeader();
        preg_match('/[0-9]+$/', $location_header, $matches);

        return $matches[0];
    }

    private function getUser() {

        if (!Session::has(self::USER_KEY)) {
            $user = $this->obtainUser();
            $this->storeUser($user['person']);
        }

        return Session::get(self::USER_KEY);
    }

    private function obtainUser() {
        return $this->fetch($this->getUserLocation());
    }

    private function getOauthClient($token = null, $secret = null) {

        if (!isset($this->client)) {
            $this->client = new \OAuth($this->settings['key'], $this->settings['secret'], OAUTH_SIG_METHOD_HMACSHA1);
        }

        if ($token !== null && $secret !== null) {
            $this->client->setToken($token, $secret);
        }

        return $this->client;
    }

    private function fetch($uri, $data = null, $method = OAUTH_HTTP_METHOD_GET, $retryCount = 0) {

        $uri = $this->cleanUri($uri);

        $headers = ['Content-Type' => 'application/' . $this->getContentType()];

        if (preg_match('[array|object]', gettype($data))) {
            $data = json_encode($data);
        }

        $client = $this->getOauthClient($this->getAccessToken(), $this->getAccessSecret());
        $client->disableSSLChecks();

        try {

            $client->fetch($uri, $data, $method, $headers);

            return json_decode($client->getLastResponse(), true);

        } catch (\OAuthException $e) {

            // TODO: Retry like F1api-php5? They look for 400 though, which means don't try again without modifications

            $extra = [
                'data'       => $data,
                'url'        => $uri,
                'method'     => $method,
                'headers'    => $this->getLastResponseHeaders(),
                'retryCount' => $retryCount,
            ];

            throw new Exception($e->getMessage(), $e->getCode(), $client->getLastResponse(), $extra, $e);
        }

    }

    private function oauth_get($uri, $data) {

        $uri = $uri . '?' . http_build_query($data);

        return $this->fetch($uri, null, OAUTH_HTTP_METHOD_GET);

    }

    private function cleanUri($uri) {

        // Make sure content type is appended to URI
        $content_type = $this->getContentType();
        $uri = preg_replace('/(?:\.' . $content_type . ')+$/', '', $uri) . '.' . $content_type;

        return $uri;
    }

    private function getContentType() {
        return $this->content_type;
    }

    /**
     * @return array
     */
    private function getLastResponseHeaders() {

        $header_str = $this->getOauthClient()->getLastResponseHeaders();
        $headers = [];
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header_str));

        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function ($m) {
                    return strtoupper($m[0]);
                }, strtolower(trim($match[1])));
                if (isset($headers[$match[1]])) {
                    $headers[$match[1]] = [$headers[$match[1]], $match[2]];
                } else {
                    $headers[$match[1]] = trim($match[2]);
                }
            }
        }

        return $headers;
    }

    private function getContentLocationHeader() {

        $headers = $this->getLastResponseHeaders();

        return isset($headers['Content-Location']) ? $headers['Content-Location'] : null;
    }

    private function getRequestToken($oauth_token) {

        $key = $oauth_token;
        return Cache::get($key);

    }

    private function storeRequestToken($value) {

        $key = $value['oauth_token'];
        Cache::put($key, $value, 5);

        return $this;
    }

    private function getAccessToken() {
        return Session::get(self::ACCESS_TOKEN_KEY);
    }

    private function storeAccessToken($value) {
        Session::set(self::ACCESS_TOKEN_KEY, $value);

        return $this;
    }

    private function getAccessSecret() {
        return Session::get(self::ACCESS_SECRET_KEY);
    }

    private function storeAccessSecret($value) {
        Session::set(self::ACCESS_SECRET_KEY, $value);

        return $this;
    }

    private function storeUser($value) {
        Session::set(self::USER_KEY, $value);

        return $this;
    }

    private function getUserLocation() {
        return Session::get(self::USER_LOCATION_KEY);
    }

    private function storeUserLocation($value) {
        Session::set(self::USER_LOCATION_KEY, $value);

        return $this;
    }

}

//
//    {#183
//        +"oauth_token": "22b2f714-1888-49b1-b708-e37a59a7046c"
//    +"oauth_token_secret": "4eecf0c6-be93-47d8-b8bf-6cfa1bba6567"
//}