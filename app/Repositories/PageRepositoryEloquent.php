<?php

namespace App\Repositories;

use App\Models\Page;

class PageRepositoryEloquent implements PageRepository
{
    /**
     * @var Page
     */
    private $model;

    /**
     * PageRepositoryEloquent constructor.
     * @param Page $model
     */
    public function __construct(Page $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
