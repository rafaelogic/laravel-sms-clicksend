<?php

namespace NotificationChannels\ClickSend;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Events\NotificationFailed;
use NotificationChannels\ClickSend\Exceptions\CouldNotSendNotification;


class ClickSendChannel {
    /** @var \NotificationChannels\ClickSend\ClickSendApi */
    protected $client;

    /** @var Dispatcher */
    protected $events;

    /**
     * ClickSendChannel constructor.
     *
     * @param ClickSendApi $client
     * @param Dispatcher   $events
     */
    public function __construct( ClickSendApi $client, Dispatcher $events ) {
        $this->client = $client;
        $this->events = $events;
    }

    /**
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @return array|mixed
     * @throws CouldNotSendNotification
     */
    public function send( $notifiable, Notification $notification ) {
        $to = $notifiable->routeNotificationForClicksend();

        if ( !$to ) {
            throw CouldNotSendNotification::missingRecipient();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $message = $notification->toClickSend( $notifiable );

        if ( is_string( $message ) ) {
            $message = new ClickSendMessage( $to, $message );
        }

        try {
            $result = $this->client->sendSms( $message );
        } catch ( Exceptions\CouldNotSendNotification $e ) {
            $this->events->dispatch(
                new NotificationFailed( $notifiable, $notification, get_class( $this ), [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data'    => []
                ] )
            );

            // by throwing exception NotificationSent event is not triggered and we trigger NotificationFailed above instead
            throw $e;
        }

        if ( empty( $result['success'] ) || !$result['success'] ) {
            $this->events->dispatch(
                new NotificationFailed( $notifiable, $notification, get_class( $this ), $result )
            );

            // by throwing exception NotificationSent event is not triggered and we trigger NotificationFailed above instead
            throw CouldNotSendNotification::clickSendErrorMessage( $result['message'] );
        }

        return $result;
    }

}
