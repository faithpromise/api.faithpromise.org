<?php

namespace App\FaithPromise\F1;

class Exception extends \Exception {

    public $response;
    public $extra;

    public function __construct($message, $code = 0, $response = null, $extra = null, \OAuthException $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
        $this->extra = $extra;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getExtra() {
        return $this->extra;
    }

}