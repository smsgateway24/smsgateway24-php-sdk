#SmsGateWay24
SmsGateWay24 is a service that can turn you android phone to gateway and you can start sending messages throw it.

## PHP SDK

PHP library for SmsGateWay24 API interaction.

Requires:
* PHP 7.1+

Here's a demo of how you can use it:

```php

use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$to = "+7 (900) 123 45-67";
$message = "Hello, how are you?";
$deviceId = 12345; // get it in your profile after app installation on your android

$gateway->addSms($to, $message, $deviceId);

```

After that, the phone with the Smsgateway24 application calls the server and takes the SMS and sends it from your sim card.


### Getting Started
[https://smsgateway24.com/ru/apps](our website)


You can install the package via composer:
```bash
composer require smsgateway24/smsgateway24-php-sdk
```

### Features

#### Add SMS to queue

Creates SMS on the server to send.

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$to = "+7 (900) 123 45-67";
$message = "Hello, how are you?";
$deviceId = 12345;
$timeToSend = '2018-10-25 00:00:00'; // (optional) you can set the date when you want to send the message
$sim = 0; // (optional) if you phone have some sim cards you can choose which you want to use

$gateway->addSms($to, $message, $deviceId, $timeToSend, $sim);
```

#### Get SMS status

You can find out the status of each SMS using this method

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$smsId = 12345;
$statusResult = $gateway->getSmsStatus($smsId);

print_r($statusResult);
```

#### Get device status

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$deviceId = 12345;
$statusResult = $gateway->getDeviceStatus($deviceId);

print_r($statusResult);
```