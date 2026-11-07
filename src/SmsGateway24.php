<?php


namespace SmsGateway24;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use SmsGateway24\DataObjects\DeviceStatus;
use SmsGateway24\DataObjects\LastSeenDate;
use SmsGateway24\DataObjects\SmsStatus;
use SmsGateway24\Exceptions\SDKException;

/**
 * Class SmsGateway24
 *
 * @link    https://smsgateway24.com/en/docs/apidocumentation
 *
 * @package SmsGateway24
 */
class SmsGateway24
{
    protected const DEFAULT_TIMEOUT = 10.0;
    protected const BASE_URL = 'https://smsgateway24.com/getdata/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * SmsGateway24 constructor.
     *
     * @param string|Token $token
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
     * @param string $sendTo
     * @param string $body
     * @param int $deviceId
     * @param string|null $timeToSend
     * @param int|null $sim
     * @param int|null $customerId
     * @param int|null $urgent
     *
     * @return int
     * @throws Exceptions\SDKException
     */
    public function addSms(
        string $sendTo,
        string $body,
        int $deviceId,
        ?string $timeToSend = null,
        ?int $sim = null,
        ?int $customerId = null,
        ?int $urgent = null
    ): int
    {
        $apiMethod = 'addsms';

        if (!$sim) {
            $sim = 0;
        }
        $response = $this->client->post($apiMethod, [
            'sendto' => $sendTo,
            'body' => $body,
            'device_id' => $deviceId,
            'timetosend' => $timeToSend,
            'sim' => $sim,
            'customerid' => $customerId,
            'urgent' => $urgent
        ]);

        return (int)$this->requireField($response, 'sms_id');
    }

    /**
     * You can find out the status of each SMS using this method
     *
     * @param int $smsId
     *
     * @return SmsStatus
     * @throws Exceptions\SDKException
     */
    public function getSmsStatus(int $smsId): SmsStatus
    {
        $apiMethod = 'getsmsstatus';

        $response = $this->client->get($apiMethod, [
            'sms_id' => $smsId
        ]);

        return new SmsStatus(
            (int)$this->requireField($response, 'sms_id'),
            (int)$this->requireField($response, 'status'),
            (string)($response['statusdescription'] ?? '')
        );
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

        $lastSeenRaw = $this->requireField($response, 'lastseen');
        if (!is_array($lastSeenRaw) || !isset($lastSeenRaw['date'], $lastSeenRaw['timezone_type'], $lastSeenRaw['timezone'])) {
            throw new SDKException("Unexpected smsgateway24 API response: malformed 'lastseen'");
        }

        $lastSeen = new LastSeenDate(
            (string)$lastSeenRaw['date'],
            (int)$lastSeenRaw['timezone_type'],
            (string)$lastSeenRaw['timezone']
        );

        return new DeviceStatus(
            $lastSeen,
            (int)$this->requireField($response, 'device_id'),
            (string)$this->requireField($response, 'title'),
            isset($response['color']) ? (string)$response['color'] : null,
            isset($response['emoji']) ? (string)$response['emoji'] : null
        );
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

        return (int)$this->requireField($response, 'tag_id');
    }

    /**
     * Add contacts with a tag
     *
     * Add contacts for any tag. For example, for the tag * Employees * your colleagues will perfectly fit.
     *
     * @param string $fullName
     * @param string $phone
     * @param int $tagId
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

        return (int)$this->requireField($response, 'contact_id');
    }

    /**
     * Create a Newsletter
     *
     * Once you have created the tag, you can do the mailing on the tag phones.
     *
     * @param string           $title
     * @param int              $deviceId
     * @param string           $body
     * @param string|int|int[] $tags       tag id, comma-separated ids or array of ids
     * @param int              $sim        sim slot: 0 or 1 for SMS (2 - WhatsApp Business, 3 - WhatsApp Personal, 4/5 - RCS)
     * @param string|null      $timeToSend when to send, in your account time zone (e.g. "2026-01-12 10:00:00"). Default: now
     *
     * @return int newsletter (paket) id
     * @throws Exceptions\SDKException
     */
    public function savePaket(string $title, int $deviceId, string $body, $tags, int $sim = 0, ?string $timeToSend = null): int
    {
        $apiMethod = 'savepaket';

        $response = $this->client->post($apiMethod, [
            'title' => $title,
            'device_id' => $deviceId,
            'sim' => $sim,
            'body' => $body,
            'tags' => is_array($tags) ? implode(',', $tags) : $tags,
            'time_to_send' => $timeToSend
        ]);

        $id = $response['result']['data']['id'] ?? null;
        if ($id === null) {
            throw new SDKException("Unexpected smsgateway24 API response: newsletter id is missing");
        }

        return (int)$id;
    }

    /**
     * @param array  $response
     * @param string $field
     *
     * @return mixed
     * @throws SDKException
     */
    protected function requireField(array $response, string $field)
    {
        if (!isset($response[$field])) {
            throw new SDKException("Unexpected smsgateway24 API response: '" . $field . "' is missing");
        }

        return $response[$field];
    }

}
