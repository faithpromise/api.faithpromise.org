<?php

use App\FaithPromise\F1\FellowshipOneApi;
use Illuminate\Support\ServiceProvider;

class F1ServiceProvider extends ServiceProvider {

    public function register() {

        $this->app->bind('faithpromise.f1.api', function ($app) {

            return new FellowshipOneApi(config('fellowshipone'));
        });

    }

    public function boot() {

        $this->app['FaithPromise\FellowshipOne\FellowshipOneApi'] = function($app) {
            return $app->make('faithpromise.f1.api');
        };

    }

}