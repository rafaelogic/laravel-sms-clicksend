<?php

namespace NotificationChannels\ClickSend;

use ClickSend\Api\SMSApi;
use ClickSend\Configuration;
use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ClickSendServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     *
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/clicksend.php' => config_path('clicksend.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/clicksend.php',
            'clicksend'
        );
    }

    /**
     * @throws \Exception
     */
    public function register()
    {
        $this->checkConfig();

        $this->app->singleton(SMSApi::class, function () {
            $configuration = Configuration::getDefaultConfiguration()
                                          ->setUsername($this->app['config']['clicksend.user_name'])
                                          ->setPassword($this->app['config']['clicksend.api_key']);

            return new SMSApi(new Client(), $configuration);
        } );

        $this->app->singleton( ClickSendApi::class, function (Application $app) {
            return new ClickSendApi($app->make(SMSApi::class), $this->app['config']['clicksend.sms_from']);
        } );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ClickSendApi::class];
    }

    /**
     * @throws \Exception
     */
    public function checkConfig(): void
    {
        if (! isset($this->app['config']['clicksend.user_name']) || empty($this->app['config']['clicksend.user_name'])) {
            throw new \Exception('CLICKSEND_ENABLED is missing or blank');
        }

        if (! isset($this->app['config']['clicksend.api_key']) || empty($this->app['config']['clicksend.api_key'])) {
            throw new \Exception('CLICKSEND_API_KEY is missing or blank');
        }
    }
}
