<?php

namespace App\Repositories;

interface MealRepository
{
    public function getAll();

    public function getAllForDate($date);
}
