# ClickSend notifications channel for Laravel 5.6+

This package makes it easy to send notifications using [clicksend.com](//clicksend.com) with Laravel 5.6+.
Uses ClickSend PHP API wrapper [https://github.com/ClickSend/clicksend-php]

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Events](#events)
- [Api Client](#api-client)
- [Changelog](#changelog)
- [Testing](#testing)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

Install the package via composer:
```bash
composer require cca-bheath/laravel-sms-clicksend
```

Add the service provider to `config/app.php`:
```php
...
'providers' => [
    ...
    NotificationChannels\ClickSend\ClickSendServiceProvider::class,
],
...
```

Publish the clicksend config file `config/clicksend.php`:

```php
php artisan vendor:publish --provider="NotificationChannels\ClickSend\ClickSendServiceProvider" --tag="config"
```

## Usage

Use ClickSendChannel in `via()` method inside your notification classes. Example:

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\ClickSend\ClickSendMessage;
use NotificationChannels\ClickSend\ClickSendChannel;

class ClickSendTest extends Notification
{

    public $token;

    /**
     * Create a notification instance.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }
    
    /**
     * Required
     */
    public function via($notifiable)
    {
        return [ClickSendChannel::class];
    }

    /**
     * Required
     */
    public function getMessage($notifiable)
    {  	
       	return "SMS test to user #{$notifiable->id} with token {$this->token} by ClickSend";
    }
    
    /**
     * Optional
     */
    public function updateClickSendMessage($message)
    {
        $message->setFrom('+15555555555');

        return $message;
    }
}
```

In notifiable model (User), include method `routeNotificationForClickSend()` that returns recipient mobile number:

```php
...
public function routeNotificationForClickSend()
{
    return $this->phone;
}
...
```
From controller then send notification standard way:
```php

$user = User::find(1);

try {
	$user->notify(new ClickSendTest('ABC123'));
}
catch (\Exception $e) {
	// do something when error
	return $e->getMessage();
}
```

## Events
Following events are triggered by Notification. By default:
- Illuminate\Notifications\Events\NotificationSending
- Illuminate\Notifications\Events\NotificationSent

and this channel triggers one when submission fails for any reason:
- Illuminate\Notifications\Events\NotificationFailed

To listen to those events create listener classes in `app/Listeners` folder e.g. to log failed SMS:

```php

namespace App\Listeners;
	
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NotificationChannels\ClickSend\ClickSendChannel;
	
class NotificationFailedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Notification failed event handler
     *
     * @param  NotificationFailed  $event
     * @return void
     */
    public function handle(NotificationFailed $event)
    {
        // Handle fail event for ClickSend
        //
        if($event->channel == ClickSendChannel::class) {
	
            echo 'failed'; dump($event);
            
            $logData = [
            	'notifiable'    => $event->notifiable->id,
            	'notification'  => get_class($event->notification),
            	'channel'       => $event->channel,
            	'data'      => $event->data
            	];
            	
            Log::error('Notification Failed', $logData);
         }
         // ... handle other channels ...
    }
}
```
 
 
 
Then register listeners in `app/Providers/EventServiceProvider.php`
```php
...
protected $listen = [

	'Illuminate\Notifications\Events\NotificationFailed' => [
		'App\Listeners\NotificationFailedListener',
	],

	'Illuminate\Notifications\Events\NotificationSent' => [
		'App\Listeners\NotificationSentListener',
	],

	'Illuminate\Notifications\Events\NotificationSending' => [
		'App\Listeners\NotificationSendingListener',
	],
];
...
```


## API Client

To access the rest of ClickSend API you can get client from ClickSendApi:
```php
$client = app(ClickSendApi::class)->getClient();
	
// then get for eaxample yor ClickSend account details:
$account =  $client->getAccount()->getAccount();
	
// or list of countries:
$countries =  $client->getCountries()->getCountries();

```

## Config

- `CLICKSEND_ENABLED` 
    - If set to false the channel will not run and return true.  This is good for testing
- `CLICKSEND_USERNAME`
    - Username on ClickSend
    - You can see this information by click on the API Credentials link at the top of the dashboard
- `CLICKSEND_API_KEY`
    - API Key on ClickSend
    - You can see this information by click on the API Credentials link at the top of the dashboard
- `CLICKSEND_SMS_FROM`
    - Override the FROM on SMS and MMS messages
    - Can leave blank
- `CLICKSEND_PREFIX`
    - Enforce that all `to` have this prefix
    - For example +1
    - This should only be used if you are sure that **_all_** `to` **_must_** have this prefix

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

Incomplete
``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [deshack](https://github.com/deshack)
- [vladski](https://github.com/vladski)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
