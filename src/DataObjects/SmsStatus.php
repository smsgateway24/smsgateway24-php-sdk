<?php


namespace SmsGateway24\DataObjects;


class SmsStatus
{
    /**
     * @var integer
     */
    public $sms_id;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var string
     */
    public $status_description;

    /**
     * SmsStatus constructor.
     *
     * @param int    $sms_id
     * @param int    $status
     * @param string $status_description
     */
    public function __construct(int $sms_id, int $status, string $status_description)
    {
        $this->sms_id = $sms_id;
        $this->status = $status;
        $this->status_description = $status_description;
    }


}