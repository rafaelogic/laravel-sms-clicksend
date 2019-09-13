<?php

namespace NotificationChannel\ClickSend\Tests;

use ClickSend\Api\SMSApi;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use Mockery;
use Illuminate\Notifications\Notification;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use NotificationChannels\ClickSend\ClickSendApi;
use NotificationChannels\ClickSend\ClickSendChannel;
use NotificationChannels\ClickSend\ClickSendMessage;
use NotificationChannels\ClickSend\Exceptions\CouldNotSendNotification;

class ClickSendChannelTest extends MockeryTestCase {

    /**
     * @var Mockery\MockInterface
     */
    private $api;

    /**
     * @var ClickSendChannel
     */
    private $channel;

    public function setUp(): void {
        parent::setUp();

        $app = new Container();
        $app->singleton( 'app', 'Illuminate\Container\Container' );
        $app->singleton( 'events', function ( $app ) {
            return new Dispatcher( $app );
        } );
        $app->singleton( 'config', function () {
            return new Repository( ['clicksend.enabled' => true] );
        } );

        $api           = Mockery::mock( SMSApi::class );
        $this->api     = Mockery::mock( ClickSendApi::class, [ $api, 'from' ] );
        $this->channel = new ClickSendChannel( $this->api, $app->make( 'events' ), $app->make( 'config' ) );
    }

    /**
     * @throws CouldNotSendNotification
     */
    public function testChannelCallsApi() {
        $this->expectException( CouldNotSendNotification::class );

        $this->api->shouldReceive( 'sendSms' )
                  ->once()
                  ->withArgs( function ( $arg ) {
                      if ( $arg instanceof ClickSendMessage ) {
                          return true;
                      }

                      return false;
                  } );

        $this->channel->send( new TestNotifiable(), new TestNotification() );
    }

    /**
     * @throws CouldNotSendNotification
     */
    public function testDoesNotSendSmsWhenMissingRecipient() {
        $this->expectException( CouldNotSendNotification::class );

        $this->api->shouldReceive( 'sendSms' )
                  ->atMost()
                  ->once()
                  ->andThrow( CouldNotSendNotification::class );

        $this->channel->send( new TestNotifiableWithoutRouteNotificationFor(), new TestNotification() );
    }
}

class TestNotifiable {
    public function routeNotificationForClicksend() {
        return '+1234567890';
    }
}

class TestNotifiableWithoutRouteNotificationFor extends TestNotifiable {
    public function routeNotificationFor() {
        return false;
    }
}

class TestNotification extends Notification {
    public function toClickSend() {
        return new ClickSendMessage( 'to', 'message', 'from' );
    }
}
