<?php

namespace App\Exceptions;

class CouldNotSendWhatsapp extends \Exception
{
    public static function serviceRespondedWithAnError($response)
    {
        return new static('WhatsApp responded with an error: '.$response);
    }
}
