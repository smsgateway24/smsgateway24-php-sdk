# SmsGateWay24

SmsGateWay24 is a service that can turn you android phone to gateway and you can start sending messages throw it.

## PHP SDK

PHP library for SmsGateWay24 API interaction.

Requires:
* PHP 7.1+

Here's a demo of how you can use it:

```php

use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$to = "+4915752982212";  //required. Target Phone number
$message = "Hello, how are you?"; // required. Body Message. 
$deviceId = 10403; //required.  get it in your profile after app installation on your android
$sim=0;  // Optional. 0 or 1. For Dual SIM devices. (if you skip it -> default sim is  0)
$timeToSend = "2022-01-12 00:00:00"; // Optional. time when you want to send SMS
$customerid = 12; // Optional. your internal customer ID.
$urgent = 1; // Optional. 1 or 0 to make sms Urgent.
$smsId = $gateway->addSms($to, $message, $deviceId, $timeToSend, $sim, $customerid, $urgent);



```

After that, the phone with the Smsgateway24 application calls the server and takes the SMS and sends it from your sim card.


### Getting Started
Visit our [website](https://smsgateway24.com/), sign up and install app on your phone. Get your API token and device id.


Install the package via composer:
```bash
composer require smsgateway24/smsgateway24-php-sdk
```

### API Methods

#### Add SMS to queue

Creates SMS on the server to send.

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$to = "+4915752982212";  //required. Target Phone number
$message = "Hello, how are you?"; // required. Body Message. 
$deviceId = 10403; //required.  get it in your profile after app installation on your android
$sim=0;  // Optional. 0 or 1. For Dual SIM devices. (if you skip it -> default sim is  0)
$timeToSend = "2022-01-12 00:00:00"; // Optional. time when you want to send SMS
$customerid = 12; // Optional. your internal customer ID.
$urgent = 1; // Optional. 1 or 0 to make sms Urgent.
$smsId = $gateway->addSms($to, $message, $deviceId, $timeToSend, $sim, $customerid, $urgent);


echo $smsId; 
```

#### Get SMS status

You can find out the status of each SMS using this method

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$smsId = 12345;

$statusResult = $gateway->getSmsStatus($smsId);

print_r($statusResult);
/*
Statuses values: 
1 - New (just created)
2 - Taken from Server. The phone picked up the SMS from the server, but there is no information about the delivery yet. 
3 - Queued for sending.
5 - Income. 
6 - Sent by Phone. Good status. The phone sent a text message. But there is no information about the delivery yet. 
7 - Delivered. Good status. The phone texted and it was 100% delivered by the operator. The delivery status was sent to the server. (You need a webhook to your server - we have one! Write your server in the device settings on the website)
8 - Sms Not Delivered. The text message was not delivered. This usually means that the SIM card is blocked or the balance is negative
9 - Not SENT - Generic failure. The text message was not delivered. This usually means that the SIM card is blocked or the balance is negative
10 - Not sent - No service. 
11 - Not Sent - Null PD.
12 - Not Sent - Radio off.
100 - not sent - NOT ALLOWED. Click the permission button in the app
101 - not sent - Not Allowed At all. Click the permission button in the app
*/
```

#### Get device status

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$deviceId = 12345; // get it in your profile

$statusResult = $gateway->getDeviceStatus($deviceId);

echo $statusResult->title;          // connection state: "online", "recently" or "offline"
echo $statusResult->lastseen->date; // last time the phone contacted the server
```

#### Add Tag

Add contacts for any tag. For example, for the tag * Employees * your colleagues will perfectly fit.

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$tagId = $gateway->saveTag("Rich customers");

echo $tagId;
```

#### Save Contact

Add contacts for any tag. For example, for the tag * Employees * your colleagues will perfectly fit.

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$contactName = 'Support SmsGateWay24';
$phoneNum = '+4915752982212';
$tagId = 2456;

$contactId = $gateway->saveContact($contactName, $phoneNum, $tagId);

echo $contactId;
```

#### Create a Newsletter

Once you have created the tag, you can do the mailing on the tag phones.

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$paketTitle = 'some title';
$deviceId = 1234; // get it in your profile
$body = 'body text';
$tags = [12, 13, 14]; // ids
$sim = 0; // Optional. Sim slot: 0 or 1 for SMS (2 - WhatsApp Business, 3 - WhatsApp Personal, 4/5 - RCS). Default 0
$timeToSend = '2026-01-12 10:00:00'; // Optional. In your account time zone. Default: now

$paketId = $gateway->savePaket($paketTitle, $deviceId, $body, $tags, $sim, $timeToSend);

echo $paketId;
```

Don't forget to press "Start" in the app on your phone to begin the newsletter.

### Error handling

Every method throws `\SmsGateway24\Exceptions\SDKException` when the API reports an error
(wrong token, unknown device, validation error, etc.) or when the request fails.

```php
use \SmsGateway24\Exceptions\SDKException;

try {
    $smsId = $gateway->addSms($to, $message, $deviceId);
} catch (SDKException $e) {
    echo $e->getMessage(); // e.g. "Device is not in your list"
}
```

## Contributing
Contributions are welcome!

## Credits
* [Igor](https://github.com/underwear/)
* [Nik](https://github.com/nikitospush/)
