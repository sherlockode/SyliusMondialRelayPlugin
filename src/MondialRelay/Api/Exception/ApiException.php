<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception;

class ApiException extends \Exception
{
    public function __construct(int $code = 0, string $message = "", ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
