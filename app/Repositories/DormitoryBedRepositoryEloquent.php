<?php

namespace App\Repositories;

use App\Models\DormitoryBed;

class DormitoryBedRepositoryEloquent implements DormitoryBedRepository
{
    /**
     * @var DormitoryBed
     */
    private $model;

    /**
     * DormitoryBedRepositoryEloquent constructor.
     * @param DormitoryBed $model
     */
    public function __construct(DormitoryBed $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
