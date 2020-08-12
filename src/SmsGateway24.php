<?php


namespace SmsGateway24;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use SmsGateway24\DataObjects\DeviceStatus;
use SmsGateway24\DataObjects\SmsStatus;

/**
 * Class SmsGateway24
 *
 * @link https://smsgateway24.com/en/docs/apidocumentation
 *
 * @package SmsGateway24
 */
class SmsGateway24
{
    protected const DEFAULT_TIMEOUT = 3.0;
    protected const BASE_URL = 'https://smsgateway24.com/getdata/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * SmsGateway24 constructor.
     *
     * @param string|Token         $token
     * @param ClientInterface|null $customGuzzleClient
     *
     */
    public function __construct($token, ?ClientInterface $customGuzzleClient = null)
    {
        // Token
        if ((!($token instanceof Token)) and !is_string($token)) {
            throw new \TypeError('Argument 1 passed to constructor must be string or an instance of ' . Token::class);
        }

        if (is_string($token)) {
            $token = new Token($token);
        }

        // Http client
        $httpClient = $customGuzzleClient ?? new Guzzle([
                'timeout' => static::DEFAULT_TIMEOUT
            ]);

        $this->client = new Client($httpClient, $token, static::BASE_URL);
    }

    /**
     * Creates SMS on the server to send.
     *
     * @param string      $sendTo
     * @param string      $body
     * @param string      $deviceId
     * @param string|null $timeToSend
     * @param int|null    $sim
     * @param int|null    $customerId
     * @param int|null    $urgent
     *
     * @return int
     * @throws Exceptions\SDKException
     */
    public function addSms(
        string $sendTo,
        string $body,
        string $deviceId,
        ?string $timeToSend = null,
        ?int $sim = null,
        ?int $customerId = null,
        ?int $urgent = null
    ): int {
        $apiMethod = 'addsms';

        $response = $this->client->post($apiMethod, [
            'sendto' => $sendTo,
            'body' => $body,
            'device_id' => $deviceId,
            'timetosend' => $timeToSend,
            'sim' => $sim,
            'customerId' => $customerId,
            'urgent' => $urgent

        ]);

        return (int)$response['sms_id'];
    }

    /**
     * You can find out the status of each SMS using this method
     *
     * @param string $smsId
     *
     * @return SmsStatus
     * @throws Exceptions\SDKException
     */
    public function getSmsStatus(string $smsId): SmsStatus
    {
        $apiMethod = 'getsmsstatus';

        $response = $this->client->get($apiMethod, [
            'sms_id' => $smsId
        ]);

        $smsId = $response['sms_id'];
        $status = $response['status'];
        $statusDescription = $response['status_description'];

        return new SmsStatus($smsId, $status, $statusDescription);
    }

    /**
     * You can find out the status of your devices
     *
     * @param int $deviceId
     *
     * @return DeviceStatus
     * @throws Exceptions\SDKException
     */
    public function getDeviceStatus(int $deviceId): DeviceStatus
    {
        $apiMethod = 'getdevicestatus';

        $response = $this->client->get($apiMethod, [
            'device_id' => $deviceId
        ]);

        $lastSeen = $response['lastseen'];
        $deviceId = $response['device_id'];
        $title = $response['title'];

        return new DeviceStatus($lastSeen, $deviceId, $title);
    }


    /**
     * Add tag.
     *
     * The tag is needed to create a newsletter on a group of numbers. For example, tag * Employees *.
     *
     * @param string $title
     *
     * @return int
     * @throws Exceptions\SDKException
     */
    public function saveTag(string $title): int
    {
        $apiMethod = 'savetag';

        $response = $this->client->post($apiMethod, [
            'title' => $title
        ]);

        return $response['tag_id'];
    }

    /**
     * Add contacts with a tag
     *
     * Add contacts for any tag. For example, for the tag * Employees * your colleagues will perfectly fit.
     *
     * @param string $fullName
     * @param string $phone
     * @param int    $tagId
     *
     * @return int
     * @throws Exceptions\SDKException
     */
    public function saveContact(string $fullName, string $phone, int $tagId): int
    {
        $apiMethod = 'savecontact';

        $response = $this->client->post($apiMethod, [
            'fullname' => $fullName,
            'phone' => $phone,
            'tag_id' => $tagId
        ]);

        return $response['contact_id'];
    }

    /**
     * Create a Newsletter
     *
     * Once you have created the tag, you can do the mailing on the tag phones.
     *
     * @param string           $title
     * @param string           $deviceId
     * @param string           $body
     * @param string|int|int[] $tags
     *
     * @return int
     * @throws Exceptions\SDKException
     */
    public function savePaket(string $title, string $deviceId, string $body, $tags): int
    {
        $apiMethod = 'savepaket';

        $response = $this->client->post($apiMethod, [
            'title' => $title,
            'device_id' => $deviceId,
            'body' => $body,
            'tags' => is_array($tags) ? implode(',', $tags) : $tags
        ]);

        return $response['paket'];
    }
}