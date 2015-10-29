<?php

namespace FaithPromise\FellowshipOne;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

    public function register() {

        $this->app->bindShared('FaithPromise\FellowshipOne\AuthInterface', function ($app) {

            return new Auth(
                $app['config']['fellowshipone']['key'],
                $app['config']['fellowshipone']['secret'],
                $app['config']['fellowshipone']['api_url']
            );

        });

    }

//    public function boot() {
//
//        $this->app['FaithPromise\FellowshipOne\Auth'] = function ($app) {
//            return $app->make('faithpromise.f1.api');
//        };
//
//    }

}