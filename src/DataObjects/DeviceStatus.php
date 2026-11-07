<?php


namespace SmsGateway24\DataObjects;


class DeviceStatus
{
    /**
     * @var LastSeenDate
     */
    public $lastseen;

    /**
     * @var integer
     */
    public $device_id;

    /**
     * Connection state: "online", "recently" or "offline" (not the device name).
     *
     * @var string
     */
    public $title;

    /**
     * @var string|null "success", "warning" or "danger"
     */
    public $color;

    /**
     * @var string|null
     */
    public $emoji;

    /**
     * DeviceStatus constructor.
     *
     * @param LastSeenDate $lastseen
     * @param int          $device_id
     * @param string       $title
     * @param string|null  $color
     * @param string|null  $emoji
     */
    public function __construct(LastSeenDate $lastseen, int $device_id, string $title, ?string $color = null, ?string $emoji = null)
    {
        $this->lastseen = $lastseen;
        $this->device_id = $device_id;
        $this->title = $title;
        $this->color = $color;
        $this->emoji = $emoji;
    }


}