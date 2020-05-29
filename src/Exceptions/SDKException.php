<?php


namespace SmsGateway24\Exceptions;


class SDKException extends \Exception
{

    /**
     * SDKException constructor.
     *
     * @param string          $message
     * @param int|null        $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, ?int $code = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}