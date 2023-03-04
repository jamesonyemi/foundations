<?php

namespace App\Exceptions;

use Exception;

class ShoutoutFailedNotification extends Exception
{
    /**
     * @param Exception $exception
     * @return static
     */
    public static function serviceRespondedWithAnError(Exception $exception)
    {
        return new static("Shoutout service responded with an error '{$exception->getCode()}: {$exception->getMessage()}'");
    }
}
