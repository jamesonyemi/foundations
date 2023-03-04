<?php

namespace App\Repositories;

use App\Models\Journal;

class JournalRepositoryEloquent implements JournalRepository
{
    /**
     * @var Journal
     */
    private $model;

    /**
     * JournalRepositoryEloquent constructor.
     * @param Journal $model
     */
    public function __construct(Journal $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }
}
