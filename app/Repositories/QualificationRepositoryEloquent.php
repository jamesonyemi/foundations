<?php

namespace App\Repositories;

use App\Models\Qualification;

class QualificationRepositoryEloquent implements QualificationRepository
{
    /**
     * @var Qualification
     */
    private $model;

    /**
     * BehaviorRepositoryEloquent constructor.
     * @param Qualification $model
     */
    public function __construct(Qualification $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
