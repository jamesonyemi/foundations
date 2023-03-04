<?php

namespace App\Repositories;

interface ReturnBookRepository
{
    public function getAll();

    public function getAllForUser($user_id);

    public function getSumForUserAndBook($user_id, $book_id);
}
