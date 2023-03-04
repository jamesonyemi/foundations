<?php

namespace App\Repositories;

interface GetBookRepository
{
    public function getAll();

    public function getAllForUser($user_id);

    public function getSumForUserAndBook($user_id, $book_id);
}
