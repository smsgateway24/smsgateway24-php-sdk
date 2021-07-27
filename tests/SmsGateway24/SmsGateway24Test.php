<?php

declare(strict_types=1);


use PHPUnit\Framework\TestCase;
use SmsGateway24\SmsGateway24;

final class SmsGateway24Test extends TestCase
{

    private  $sms;

    private $token;
    private $deviceId;
    private $sim;
    private $sendTo;
    private $body;
    private $newsletterBody;
    private $timeToSend;
    private $customerId;
    private $urgent;
    private $tagTitle;
    private $fullname;
    private $phoneNumber;
    private $paketTitle;

    private $smsId;
    private $tagId;
    private $contactId;
    private $paketId;

    private function defineVar()
    {
        $this->token = "217d0e07eb665e306639cdacc6b1f6c6";
        $this->deviceId = 2523;
        $this->sim = 0;
        $this->sendTo = "+4915752982212";
        $this->body = "Test Message";
        $this->newsletterBody = "Test Message For Newssletters";
        $this->timeToSend = '2021-01-01 00:12:12';
        $this->customerId = 777;
        $this->urgent = 1;
        $this->tagTitle ="Test Tag";
        $this->fullname ="Support Team";
        $this->phoneNumber ="+4915752982212";
        $this->paketTitle ="Newsletter for testing";
        $this->sms = new SmsGateway24($this->token);
    }

    public function testAddSmsAndSmsStatus(): void
    {
        $this->defineVar();

        $this->smsId = $this->sms->addSms(
            $this->sendTo,
            $this->body,
            $this->deviceId,
            $this->timeToSend,
            $this->sim,
            $this->customerId,
            $this->urgent);

        $this->assertIsInt($this->smsId);

        $smsInfo = $this->sms->getSmsStatus($this->smsId);

        $this->assertIsObject($smsInfo);
        $this->assertObjectHasAttribute("sms_id", $smsInfo);
        $this->assertObjectHasAttribute("status", $smsInfo);
        $this->assertEquals($smsInfo->sms_id,$this->smsId);
        $this->assertEquals($smsInfo->status,1);
        $this->assertEquals($smsInfo->status_description,"New");
    }

    public function testDeviceStatus()
    {
        $this->defineVar();

        $deviceInfo = $this->sms->getDeviceStatus($this->deviceId);

        $this->assertIsObject($deviceInfo);
        $this->assertObjectHasAttribute("device_id", $deviceInfo);
        $this->assertObjectHasAttribute("title", $deviceInfo);
    }



    public function testTagsContactsPaket()
    {
        $this->defineVar();

        $this->tagId = $this->sms->saveTag($this->tagTitle);
        $this->assertIsInt($this->tagId);

        $this->contactId = $this->sms->saveContact($this->fullname, $this->phoneNumber, $this->tagId);
        $this->assertIsInt($this->contactId);

        $this->paketId = $this->sms->savePaket($this->paketTitle, $this->deviceId, $this->newsletterBody, $this->tagId);
        $this->assertIsInt($this->paketId);

    }
}
