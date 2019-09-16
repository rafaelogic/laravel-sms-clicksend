<?php

return [
    /*
     * If set to false this will be disabled
     */
    'enabled'   => env('CLICKSEND_ENABLED', true),

    /**
     * ClickSend username
     */
    'user_name' => env('CLICKSEND_USERNAME'),

    /**
     * ClickSend API Key
     */
    'api_key'   => env('CLICKSEND_API_KEY'),

    /**
     * ClickSend Send From
     */
    'sms_from'  => env('CLICKSEND_SMS_FROM'),

    /**
     * ClickSend enforced prefix
     * For example +1
     * This should only be used if you are absolutely sure every to will need this prefix
     */
    'prefix'  => env('CLICKSEND_PREFIX', ''),
];