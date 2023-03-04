<?php

namespace App\Helpers;

class NatureDev
{
    public static function checkDBConnection()
    {
        /*$link = @mysqli_connect(
            config('database.connections.primary.host'),
            config('database.connections.primary.username'),
            config('database.connections.primary.password')
        );

        if ($link) {
            return mysqli_select_db($link, config('database.connections.primary.database'));
        } else {*/
        return false;
        /* }*/
    }
}
