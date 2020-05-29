<?php


namespace SmsGateway24;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use SmsGateway24\DataObjects\DeviceStatus;
use SmsGateway24\DataObjects\SmsStatus;

/**
 * Class SmsGateway24
 *
 * @link    https://smsgateway24.com/en/docs/apidocumentation
 *
 * @package SmsGateway24
 */
class SmsGateway24
{
    protected const DEFAULT_TIMEOUT = 3.0;
    protected const BASE_URL = ' https://smsgateway24.com/getdata/';

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
     *
     * @return int
     * @throws Exceptions\SDKException
     */
    public function addSms(
        string $sendTo,
        string $body,
        string $deviceId,
        ?string $timeToSend = null,
        ?int $sim = null
    ): int {
        $apiMethod = 'addsms';

        $response = $this->client->post($apiMethod, [
            'send_to' => $sendTo,
            'body' => $body,
            'device_id' => $deviceId,
            'timetosend' => $timeToSend,
            'sim' => $sim
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
     * Add contacts for any tag.
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
}