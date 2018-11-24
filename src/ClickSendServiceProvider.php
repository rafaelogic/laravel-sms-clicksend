<?php

namespace NotificationChannels\ClickSend;

use ClickSend\Api\SMSApi;
use ClickSend\Configuration;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ClickSendServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register() {
        $this->app->singleton( SMSApi::class, function ( Application $app ) {
            $config = array_get( $app['config'], 'services.clicksend' );

            $configuration = Configuration::getDefaultConfiguration()
                                          ->setUsername( $config['username'] )
                                          ->setPassword( $config['api_key'] );

            return new SMSApi( $app->make( ClientInterface::class ), $configuration );
        } );

        $this->app->singleton( ClickSendApi::class, function ( Application $app ) {
            $config = array_get( $app['config'], 'services.clicksend' );

            return new ClickSendApi( $app->make( SMSApi::class ), $config['sms_from'] );
        } );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [ ClickSendApi::class ];
    }
}
