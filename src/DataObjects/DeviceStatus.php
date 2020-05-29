<?php


namespace SmsGateway24\DataObjects;


class DeviceStatus
{
    /**
     * @var string
     */
    public $lastseen;

    /**
     * @var integer
     */
    public $device_id;

    /**
     * @var string
     */
    public $title;

    /**
     * DeviceStatus constructor.
     *
     * @param string $lastseen
     * @param int    $device_id
     * @param string $title
     */
    public function __construct(string $lastseen, int $device_id, string $title)
    {
        $this->lastseen = $lastseen;
        $this->device_id = $device_id;
        $this->title = $title;
    }


}