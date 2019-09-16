<?php

namespace NotificationChannels\ClickSend;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Events\NotificationFailed;
use NotificationChannels\ClickSend\Exceptions\CouldNotSendNotification;

class ClickSendChannel {
    /**
     * @var \NotificationChannels\ClickSend\ClickSendApi
     */
    protected $client;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    public $prefix;

    /**
     * ClickSendChannel constructor.
     *
     * @param ClickSendApi $client
     * @param Dispatcher $events
     * @param $config
     */
    public function __construct(ClickSendApi $client, Dispatcher $events, Repository $config)
    {
        $this->client = $client;
        $this->events = $events;
        $this->enabled = $config['clicksend.enabled'];
        $this->prefix = $config['clicksend.prefix'];
    }

    /**
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @return array|mixed
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $this->enabled) {
            return [];
        }

        $to = $notifiable->routeNotificationForClicksend();

        if (! $to) {
            throw CouldNotSendNotification::missingRecipient();
        }

        $to = $this->checkPrefix($to);

        $message = $this->getMessage($notifiable, $notification);

        $message = new ClickSendMessage($to, $message);

        $message = $this->updateClickSendMessage($message, $notification);

        try {
            $result = $this->client->sendSms($message);
        } catch (Exceptions\CouldNotSendNotification $e) {
            $this->events->dispatch(
                new NotificationFailed($notifiable, $notification, get_class($this), [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data'    => [],
                ] )
            );

            // by throwing exception NotificationSent event is not triggered and we trigger NotificationFailed above instead
            throw $e;
        }

        if (empty($result['success']) || ! $result['success']) {
            $this->events->dispatch(
                new NotificationFailed($notifiable, $notification, get_class($this), $result)
            );

            // by throwing exception NotificationSent event is not triggered and we trigger NotificationFailed above instead
            throw CouldNotSendNotification::clickSendErrorMessage($result['message']);
        }

        return $result;
    }

    /**
     * @param $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return mixed
     * @throws \Exception
     */
    public function getMessage($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'getMessage')) {
            throw new \Exception('The method getMessage() does not exists on ' . get_class($notification));
        }

        $message = $notification->getMessage($notifiable);

        if (! is_string($message)) {
            throw new \Exception('getMessage() on ' . get_class($notification) . ' did not return string');
        }

        return $message;
    }

    /**
     * @param \NotificationChannels\ClickSend\ClickSendMessage $message
     * @param \Illuminate\Notifications\Notification $notification
     * @return ClickSendMessage
     */
    public function updateClickSendMessage(ClickSendMessage $message, Notification $notification): ClickSendMessage
    {
        if (! method_exists($notification, 'updateClickSendMessage')) {
            return $message;
        }

        return $notification->updateClickSendMessage($message);
    }

    /**
     * @param string $to
     * @return string
     */
    public function checkPrefix($to)
    {
        if (! empty($this->prefix)) {
            if (substr($to, 0, strlen($this->prefix)) !== $this->prefix) {
                return $this->prefix . $to;
            }
        }

        return $to;
    }

}
