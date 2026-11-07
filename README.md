# SmsGateway24 PHP SDK

[![Latest Version](https://img.shields.io/packagist/v/smsgateway24/smsgateway24-php-sdk.svg)](https://packagist.org/packages/smsgateway24/smsgateway24-php-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/smsgateway24/smsgateway24-php-sdk.svg)](https://packagist.org/packages/smsgateway24/smsgateway24-php-sdk)
[![License](https://img.shields.io/packagist/l/smsgateway24/smsgateway24-php-sdk.svg)](LICENSE)

The official PHP library for [SmsGateway24](https://smsgateway24.com): it turns your Android phone into an SMS gateway.

Your code puts a message in the queue. The SmsGateway24 app on your phone picks it up and sends it from your own SIM card.

```php
$gateway = new \SmsGateway24\SmsGateway24('your-api-token');

$smsId = $gateway->addSms('+4915752982212', 'Hello from PHP!', 10403);
```

---

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Before you start](#before-you-start)
- [Sending SMS](#sending-sms)
- [Checking SMS status](#checking-sms-status)
- [Checking a device](#checking-a-device)
- [Contacts and tags](#contacts-and-tags)
- [Newsletters](#newsletters)
- [Error handling](#error-handling)
- [Configuring the HTTP client](#configuring-the-http-client)
- [SMS status codes](#sms-status-codes)
- [Useful links](#useful-links)

## Requirements

- PHP 7.2.5 or newer
- [Guzzle](https://github.com/guzzle/guzzle) 7 (installed automatically)
- An Android phone with the [SmsGateway24 app](https://smsgateway24.com) installed

## Installation

```bash
composer require smsgateway24/smsgateway24-php-sdk
```

## Before you start

You need two values from your SmsGateway24 account:

| What | Where to find it |
|------|------------------|
| **API token** | Profile menu → **API tokens** ([open](https://smsgateway24.com/en/token/list)) |
| **Device ID** | **Devices** page ([open](https://smsgateway24.com/en/device/list)), after the app is installed and signed in on your phone |

Create the gateway once and reuse it:

```php
use SmsGateway24\SmsGateway24;

$gateway = new SmsGateway24('your-api-token');
```

## Sending SMS

```php
$smsId = $gateway->addSms(
    '+4915752982212',      // recipient phone number, international format
    'Hello, how are you?', // message text
    10403                  // device ID
);

echo "Queued, SMS ID: $smsId";
```

Keep the returned ID: you'll need it to [check the delivery status](#checking-sms-status).

All parameters:

```php
$smsId = $gateway->addSms(
    '+4915752982212',      // $sendTo      required
    'Your code is 4821',   // $body        required
    10403,                 // $deviceId    required
    '2026-12-31 23:59:00', // $timeToSend  optional — when to send, in your account time zone. Default: now
    1,                     // $sim         optional — SIM slot: 0 or 1. Default: 0
    12345,                 // $customerId  optional — your own ID (e.g. order or customer ID), returned with the SMS
    1                      // $urgent      optional — 1 marks the SMS as urgent (OTP, high-priority traffic)
);
```

Want to skip some optional parameters? Pass `null` for them:

```php
// Send from SIM 2 right now
$gateway->addSms('+4915752982212', 'Hi!', 10403, null, 1);
```

## Checking SMS status

```php
$status = $gateway->getSmsStatus($smsId);

echo $status->sms_id;             // 555
echo $status->status;             // 7
echo $status->status_description; // "delivered"
```

See [SMS status codes](#sms-status-codes) for the meaning of each code.

> **Tip:** instead of polling, set up a [delivery status webhook](https://smsgateway24.com/en/docs/webhook) in the device settings. Our server will notify yours whenever a status changes.

## Checking a device

Find out whether a phone is online and ready to send:

```php
$device = $gateway->getDeviceStatus(10403);

echo $device->title;          // "online", "recently" or "offline"
echo $device->lastseen->date; // last time the phone contacted the server, e.g. "2026-11-07 10:00:00.000000"
```

| `title`    | Meaning                                    |
|------------|--------------------------------------------|
| `online`   | Seen in the last 100 seconds               |
| `recently` | Seen in the last 10 minutes                |
| `offline`  | Not seen for more than 10 minutes          |

## Contacts and tags

Tags group your contacts (for example *Employees* or *VIP customers*) so you can send a [newsletter](#newsletters) to the whole group at once.

```php
// 1. Create a tag
$tagId = $gateway->saveTag('VIP customers');

// 2. Add contacts to it
$gateway->saveContact('Anna Schmidt', '+4915752982212', $tagId);
$gateway->saveContact('Max Müller', '+4915111111111', $tagId);
```

Both methods return the ID of the created record.

## Newsletters

Send one message to every contact in one or more tags:

```php
$newsletterId = $gateway->savePaket(
    'Black Friday',              // $title       required — shown only to you
    10403,                       // $deviceId    required
    '-30% on everything today!', // $body        required
    [12, 13],                    // $tags        required — tag ID, array of IDs or "12,13"
    0,                           // $sim         optional — SIM slot. Default: 0
    '2026-11-27 10:00:00'        // $timeToSend  optional — in your account time zone. Default: now
);
```

> **Important:** a newsletter does not start by itself. Open the SmsGateway24 app on your phone and tap **Start**.

## Error handling

When something goes wrong, every method throws `SmsGateway24\Exceptions\SDKException`. That covers a wrong token, an unknown device, an inactive subscription, invalid input or a network error. The exception message is the error text returned by the API:

```php
use SmsGateway24\Exceptions\SDKException;

try {
    $smsId = $gateway->addSms('+4915752982212', 'Hello!', 10403);
} catch (SDKException $e) {
    error_log('SMS was not queued: ' . $e->getMessage());
    // e.g. "Device is not in your list"
    //      "Device is not available for sending sms. Subscription is not active"
}
```

If the server responds with an HTTP error, `$e->getCode()` contains the HTTP status code.

## Configuring the HTTP client

The default timeout is 10 seconds. To change it, or to use a proxy or your own middleware, pass your own Guzzle client:

```php
use GuzzleHttp\Client;
use SmsGateway24\SmsGateway24;

$http = new Client([
    'timeout' => 30,
    'proxy'   => 'http://proxy.local:3128',
]);

$gateway = new SmsGateway24('your-api-token', $http);
```

## SMS status codes

`status` is the code, `status_description` is a short machine-friendly name.

| Code | `status_description` | Meaning |
|-----:|----------------------|---------|
| 1 | `pending` | Created, waiting for the phone |
| 2 | `processing` | The phone downloaded the SMS, no delivery info yet |
| 3 | `queue` | Queued for sending |
| 5 | `income` | An incoming SMS received by your phone |
| 6 | `sent` | ✅ The phone sent the SMS, no delivery report yet |
| 7 | `delivered` | ✅ The carrier confirmed delivery |
| 8 | `not delivered` | ❌ Sent, but the carrier did not deliver it |
| 9 | `failed` | ❌ Generic failure: usually a blocked SIM or no balance. [What to do](https://smsgateway24.com/en/docs/generic_failure) |
| 10 | `failed` | ❌ No mobile network |
| 11 | `failed` | ❌ Null PDU: the message could not be encoded |
| 12 | `failed` | ❌ Radio is off (airplane mode) |
| 100, 101 | `failed` | ❌ Android blocked sending to a short or premium number. Allow it on the phone |

## Useful links

- [SmsGateway24 website](https://smsgateway24.com)
- [API documentation](https://smsgateway24.com/en/docs/device) for calling the HTTP API directly
- [Webhooks](https://smsgateway24.com/en/docs/webhook)

## Contributing

Issues and pull requests are welcome. To run the tests:

```bash
composer install
vendor/bin/phpunit tests
```

## Credits

- [Igor](https://github.com/underwear/)
- [Nik](https://github.com/nikitospush/)

## License

MIT. See [LICENSE](LICENSE).
