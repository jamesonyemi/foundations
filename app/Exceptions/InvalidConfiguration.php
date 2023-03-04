<?php

namespace App\Exceptions;

class InvalidConfiguration extends \Exception
{
    public static function configurationNotSet()
    {
        return new static('In order to send notification via Whatsapp you need to add credentials in settings.');
    }
}
