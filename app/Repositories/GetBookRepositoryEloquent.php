<?php

namespace App\Repositories;

use App\Models\GetBook;

class GetBookRepositoryEloquent implements GetBookRepository
{
    /**
     * @var GetBook
     */
    private $model;

    /**
     * GetBookRepositoryEloquent constructor.
     * @param GetBook $model
     */
    public function __construct(GetBook $model)
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
        return $this->model->where('user_id', $user_id)->where('book_id', $book_id)->sum('get_books_count');
    }
}
