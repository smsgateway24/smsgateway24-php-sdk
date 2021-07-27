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

$to = "+4915752982212";  // Also this is our Support number. Text us to WhatsApp
$message = "Hello, how are you?";
$deviceId = 12345; // get it in your profile after app installation on your android
$customerid = 12; // Optional. your internal customer ID. 
$urgent = 1; // Optional. 1 or 0 to make sms Urgent.  
$sim=1;  // 0 or 1. For Dual SIM devices. (default sim = 0)
$customerid=777; // your internal customer ID. 
$gateway->addSms($to, $message, $deviceId, $customerid, $sim, $customerid, $urgent);


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

$to = "+4915752982212";  // Also this is our Support number. Text us to WhatsApp
$message = "Hello, how are you?";
$deviceId = 12345; // get it in your profile after app installation on your android
$customerid = 12; // Optional. your internal customer ID. 
$urgent = 1; // Optional. 1 or 0 to make sms Urgent.  
$sim=1;  // 0 or 1. For Dual SIM devices. (default sim = 0)
$customerid=777; // your internal customer ID. 
$smsId = $gateway->addSms($to, $message, $deviceId, $customerid, $sim, $customerid, $urgent);

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
2 - Taken from Server.
5 - Income
6 - Sent by Phone
7 - Delivered
8 - Sms Not Delivered
9 - Not SENT - Generic failure
10 - Not sent - No service
11 - Not Sent - Null PDU
12 - Not Sent - Radio off
100 - not sent - NOT ALLOWED
101 - not sent - Not Allowed At all
*/
```

#### Get device status

```php
use \SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token-here'); // get it in your profile

$deviceId = 12345; // get it in your profile

$statusResult = $gateway->getDeviceStatus($deviceId);

print_r($statusResult);
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

$paketId = $gateway->savePaket($paketTitle, $deviceId, $body, $tags);

echo $paketId;
```

## Contributing
Contributions are welcome!

## Credits
* [Igor Filippov](https://github.com/underwear/)
* [Nikita Pushkar](https://github.com/nikitospush/)
