<?php

namespace App\Repositories;

interface SmsMessageRepository
{
    public function getAll();

    public function getAllForSender($user_id_sender);
}
