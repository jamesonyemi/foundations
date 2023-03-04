<?php

namespace App\Repositories;

use App\Models\Dormitory;

class DormitoryRepositoryEloquent implements DormitoryRepository
{
    /**
     * @var Dormitory
     */
    private $model;

    /**
     * DormitoryRepositoryEloquent constructor.
     * @param Dormitory $model
     */
    public function __construct(Dormitory $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
