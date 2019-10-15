<?php

namespace NotificationChannels\ClickSend\Exceptions;

use ClickSend\ApiException;

class CouldNotSendNotification extends \Exception
{
    /**
     * Thrown when content length is greater than 800 characters.
     *
     * @return static
     */
    public static function contentLengthLimitExceeded()
    {
        return new static(
            'Notification was not sent. Content length may not be greater than 800 characters.'
        );
    }

    /**
     * @return CouldNotSendNotification
     */
    public static function missingRecipient()
    {
        return static::notificationError('Missing recipient.');
    }

    /**
     * ClickSend returned an error message.
     *
     * @param string $message
     *
     * @return CouldNotSendNotification
     */
    public static function clickSendErrorMessage(?string $message)
    {
        return static::notificationError($message ?? 'No message.');
    }

    /**
     * Thrown when mesage status is not SUCCESS
     *
     * @param ApiException $e
     *
     * @return static
     */
    public static function clickSendApiException(ApiException $e)
    {
        return static::notificationError($e->getMessage());
    }

    /**
     * @param \Throwable $e
     *
     * @return CouldNotSendNotification
     */
    public static function genericError(\Throwable $e)
    {
        return new static(sprintf(
            'Generic Error: %s',
            $e->getMessage()
        ));
    }

    /**
     * @param string $error
     *
     * @return CouldNotSendNotification
     */
    public static function notificationError(string $error)
    {
        return new static(sprintf(
            'Notification Error: %s',
            $error
        ));
    }

    /**
     * @param string $driver
     * @return CouldNotSendNotification
     */
    public static function driverError(string $driver)
    {
        return new static(sprintf(
            'Invalid driver (%s)',
            $driver
        ));
    }
}
