<?php

namespace Ehking\Excation;
//require_once 'ExceptionInterface.php';


class InvalidRequestException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct($message = array(), $code = 400, Exception $previous = null)
    {
        $message['error'] = 'invalid_request';
        parent::__construct(serialize($message), $code, $previous);
    }
}
