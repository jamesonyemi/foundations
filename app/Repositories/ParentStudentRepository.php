<?php

namespace App\Repositories;

interface ParentStudentRepository
{
    public function getAll();

    public function create(array $data, $activate = true);
}
