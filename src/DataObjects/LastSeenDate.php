<?php


namespace SmsGateway24\DataObjects;


class LastSeenDate
{
    /**
     * @var string
     */
    public $date;

    /**
     * @var int
     */
    public $timezone_type;

    /**
     * @var string
     */
    public $timezone;

    /**
     * Lastseen constructor.
     *
     * @param string $date
     * @param int    $timezone_type
     * @param string $timezone
     */
    public function __construct(string $date, int $timezone_type, string $timezone)
    {
        $this->date = $date;
        $this->timezone_type = $timezone_type;
        $this->timezone = $timezone;
    }


}