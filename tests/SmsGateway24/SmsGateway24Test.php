<?php

declare(strict_types=1);

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SmsGateway24\DataObjects\DeviceStatus;
use SmsGateway24\DataObjects\SmsStatus;
use SmsGateway24\Exceptions\SDKException;
use SmsGateway24\SmsGateway24;

/**
 * Response fixtures mirror what the smsgateway24 backend actually returns.
 */
final class SmsGateway24Test extends TestCase
{
    private const TOKEN = 'test-token';

    /** @var array */
    private $history = [];

    private function gateway(Response ...$responses): SmsGateway24
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new SmsGateway24(self::TOKEN, new Guzzle(['handler' => $stack]));
    }

    private static function json(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    private function lastRequest(): RequestInterface
    {
        return end($this->history)['request'];
    }

    private function lastFormParams(): array
    {
        parse_str((string)$this->lastRequest()->getBody(), $params);

        return $params;
    }

    public function testAddSmsSendsBackendFieldNames(): void
    {
        $gateway = $this->gateway(self::json(['error' => 0, 'sms_id' => 555, 'message' => 'Sms has been saved successfully']));

        $smsId = $gateway->addSms('+4915752982212', 'Hello', 2523, '2026-01-01 00:12:12', 1, 777, 1);

        $this->assertSame(555, $smsId);
        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/getdata/addsms', $request->getUri()->getPath());
        $this->assertSame([
            'sendto' => '+4915752982212',
            'body' => 'Hello',
            'device_id' => '2523',
            'timetosend' => '2026-01-01 00:12:12',
            'sim' => '1',
            'customerid' => '777',
            'urgent' => '1',
            'token' => self::TOKEN,
        ], $this->lastFormParams());
    }

    public function testAddSmsOmitsOptionalFieldsAndDefaultsSim(): void
    {
        $gateway = $this->gateway(self::json(['error' => 0, 'sms_id' => 1, 'message' => 'ok']));

        $gateway->addSms('+49157', 'Hi', 10);

        $params = $this->lastFormParams();
        $this->assertSame('0', $params['sim']);
        $this->assertArrayNotHasKey('customerid', $params);
        $this->assertArrayNotHasKey('timetosend', $params);
        $this->assertArrayNotHasKey('urgent', $params);
    }

    public function testAddSmsError(): void
    {
        $gateway = $this->gateway(self::json(['error' => 1, 'sms_id' => null, 'message' => 'Device is not in your list']));

        $this->expectException(SDKException::class);
        $this->expectExceptionMessage('Device is not in your list');
        $gateway->addSms('+49157', 'Hi', 10);
    }

    public function testGetSmsStatus(): void
    {
        $gateway = $this->gateway(self::json([
            'error' => 0,
            'sms_id' => 555,
            'status' => 7,
            'statusdescription' => 'SMS has been Delivered',
            'message' => 'Ok',
        ]));

        $status = $gateway->getSmsStatus(555);

        $this->assertInstanceOf(SmsStatus::class, $status);
        $this->assertSame(555, $status->sms_id);
        $this->assertSame(7, $status->status);
        $this->assertSame('SMS has been Delivered', $status->status_description);
        $request = $this->lastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/getdata/getsmsstatus', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame(['sms_id' => '555', 'token' => self::TOKEN], $query);
    }

    public function testGetSmsStatusError(): void
    {
        $gateway = $this->gateway(self::json([
            'error' => 1, 'sms_id' => null, 'status' => null, 'statusdescription' => null, 'message' => 'Bad sms_id',
        ]));

        $this->expectException(SDKException::class);
        $this->expectExceptionMessage('Bad sms_id');
        $gateway->getSmsStatus(1);
    }

    public function testGetDeviceStatus(): void
    {
        $gateway = $this->gateway(self::json([
            'device_id' => 2523,
            'lastseen' => ['date' => '2026-11-07 10:00:00.000000', 'timezone_type' => 3, 'timezone' => 'UTC'],
            'title' => 'online',
            'color' => 'success',
            'emoji' => '😊',
        ]));

        $device = $gateway->getDeviceStatus(2523);

        $this->assertInstanceOf(DeviceStatus::class, $device);
        $this->assertSame(2523, $device->device_id);
        $this->assertSame('online', $device->title);
        $this->assertSame('success', $device->color);
        $this->assertSame('😊', $device->emoji);
        $this->assertSame('2026-11-07 10:00:00.000000', $device->lastseen->date);
        $this->assertSame(3, $device->lastseen->timezone_type);
        $this->assertSame('UTC', $device->lastseen->timezone);
        $this->assertSame('/getdata/getdevicestatus', $this->lastRequest()->getUri()->getPath());
    }

    public function testGetDeviceStatusError(): void
    {
        $gateway = $this->gateway(self::json(['status' => 'error', 'message' => 'Device not found']));

        $this->expectException(SDKException::class);
        $this->expectExceptionMessage('Device not found');
        $gateway->getDeviceStatus(1);
    }

    public function testSaveTag(): void
    {
        $gateway = $this->gateway(self::json(['error' => 0, 'tag_id' => 42, 'message' => 'OK']));

        $this->assertSame(42, $gateway->saveTag('Rich customers'));
        $this->assertSame('/getdata/savetag', $this->lastRequest()->getUri()->getPath());
        $this->assertSame(['title' => 'Rich customers', 'token' => self::TOKEN], $this->lastFormParams());
    }

    public function testSaveTagHttp400UsesApiMessage(): void
    {
        $gateway = $this->gateway(self::json([
            'error' => 1, 'message' => 'Missing required parameters: token and title are required.',
        ], 400));

        try {
            $gateway->saveTag('');
            $this->fail('SDKException expected');
        } catch (SDKException $e) {
            $this->assertSame('Missing required parameters: token and title are required.', $e->getMessage());
            $this->assertSame(400, $e->getCode());
        }
    }

    public function testSaveContact(): void
    {
        $gateway = $this->gateway(self::json(['error' => 0, 'contact_id' => 77, 'message' => 'ok']));

        $this->assertSame(77, $gateway->saveContact('Support Team', '+4915752982212', 42));
        $this->assertSame('/getdata/savecontact', $this->lastRequest()->getUri()->getPath());
        $this->assertSame([
            'fullname' => 'Support Team',
            'phone' => '+4915752982212',
            'tag_id' => '42',
            'token' => self::TOKEN,
        ], $this->lastFormParams());
    }

    public function testSaveContactError(): void
    {
        $gateway = $this->gateway(self::json(['error' => 1, 'message' => 'Bad tag_id']));

        $this->expectException(SDKException::class);
        $this->expectExceptionMessage('Bad tag_id');
        $gateway->saveContact('A', '+49157', 1);
    }

    public function testSavePaket(): void
    {
        $gateway = $this->gateway(self::json([
            'result' => [
                'error' => false,
                'msgTitle' => 'success',
                'msgBody' => "The newsletter is ready to go. Press 'Start' on your phone to begin.",
                'color' => 'success',
                'data' => ['id' => 900],
            ],
        ]));

        $paketId = $gateway->savePaket('Newsletter', 2523, 'Body', [12, 13], 1, '2026-01-12 10:00:00');

        $this->assertSame(900, $paketId);
        $this->assertSame('/getdata/savepaket', $this->lastRequest()->getUri()->getPath());
        $this->assertSame([
            'title' => 'Newsletter',
            'device_id' => '2523',
            'sim' => '1',
            'body' => 'Body',
            'tags' => '12,13',
            'time_to_send' => '2026-01-12 10:00:00',
            'token' => self::TOKEN,
        ], $this->lastFormParams());
    }

    public function testSavePaketDefaultsSimAndAcceptsSingleTag(): void
    {
        $gateway = $this->gateway(self::json(['result' => ['error' => false, 'msgTitle' => '', 'msgBody' => '', 'color' => '', 'data' => ['id' => 1]]]));

        $gateway->savePaket('N', 1, 'B', 12);

        $params = $this->lastFormParams();
        $this->assertSame('0', $params['sim']);
        $this->assertSame('12', $params['tags']);
        $this->assertArrayNotHasKey('time_to_send', $params);
    }

    public function testSavePaketValidationError(): void
    {
        $gateway = $this->gateway(self::json([
            'result' => ['error' => true, 'msgTitle' => 'error', 'msgBody' => 'no tag selected', 'color' => 'warning', 'data' => []],
        ]));

        $this->expectException(SDKException::class);
        $this->expectExceptionMessage('error: no tag selected');
        $gateway->savePaket('N', 1, 'B', []);
    }

    public function testSavePaketWrongToken(): void
    {
        $gateway = $this->gateway(self::json(['error' => 'wrong token']));

        $this->expectException(SDKException::class);
        $this->expectExceptionMessage('wrong token');
        $gateway->savePaket('N', 1, 'B', 1);
    }

    public function testNonJsonServerError(): void
    {
        $gateway = $this->gateway(new Response(500, [], '<html>Internal Server Error</html>'));

        $this->expectException(SDKException::class);
        $this->expectExceptionCode(500);
        $gateway->getDeviceStatus(1);
    }
}
