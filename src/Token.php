<?php


namespace SmsGateway24;


class Token
{
    /**
     * @var string
     */
    public $value;

    /**
     * Token constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

}