# SmsGateWay24 PHP SDK

PHP library for SmsGateWay24 API interaction.

Here's a demo of how you can use it:
```php

use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$to = "+7 (900) 123 45-67";
$message = "Hello, how are you?";
$deviceId = 12345; // get it in your profile after app installation on your android

$gateway->addSms($to, $message, $deviceId);

```

## Getting Started

Requires:
* PHP 7.1+

You can install the package via composer:
```bash
composer require smsgateway24/smsgateway24-php-sdk
```

