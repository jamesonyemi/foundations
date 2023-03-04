<?php

namespace App\Repositories;

use App\Models\Certificate;

class CertificateRepositoryEloquent implements CertificateRepository
{
    /**
     * @var Certificate
     */
    private $model;

    /**
     * PageRepositoryEloquent constructor.
     * @param Certificate $model
     */
    public function __construct(Certificate $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
