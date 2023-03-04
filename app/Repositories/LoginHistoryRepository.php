<?php

namespace App\Repositories;

interface LoginHistoryRepository
{
    public function getAll();

    public function getAllToday();
}
