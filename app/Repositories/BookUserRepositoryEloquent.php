<?php

namespace App\Repositories;

use App\Models\BookUser;

class BookUserRepositoryEloquent implements BookUserRepository
{
    /**
     * @var BookUser
     */
    private $model;

    /**
     * BookUserRepositoryEloquent constructor.
     * @param BookUser $model
     */
    public function __construct(BookUser $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
