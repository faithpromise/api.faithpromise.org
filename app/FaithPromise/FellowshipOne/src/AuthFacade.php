<?php

namespace FaithPromise\FellowshipOne;

use Illuminate\Support\Facades\Facade;

class AuthFacade extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'FaithPromise\FellowshipOne\AuthInterface';
    }
}
