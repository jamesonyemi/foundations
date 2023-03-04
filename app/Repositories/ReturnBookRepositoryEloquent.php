<?php

namespace App\Repositories;

use App\Models\ReturnBook;

class ReturnBookRepositoryEloquent implements ReturnBookRepository
{
    /**
     * @var ReturnBook
     */
    private $model;

    /**
     * ReturnBookRepositoryEloquent constructor.
     * @param ReturnBook $model
     */
    public function __construct(ReturnBook $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForUser($user_id)
    {
        return $this->model->where('user_id', $user_id);
    }

    public function getSumForUserAndBook($user_id, $book_id)
    {
        return $this->model->where('user_id', $user_id)->where('book_id', $book_id)->sum('return_books_count');
    }
}
