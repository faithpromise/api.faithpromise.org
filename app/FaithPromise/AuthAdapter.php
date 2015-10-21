<?php

namespace App\FaithPromise;

use Exception;
use Illuminate\Auth\AuthManager;
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
}
