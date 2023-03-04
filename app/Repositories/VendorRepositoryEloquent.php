<?php

namespace App\Repositories;

use App\Models\Vendor;

class VendorRepositoryEloquent implements VendorRepository
{
    /**
     * @var Vendor
     */
    private $model;

    /**
     * JournalRepositoryEloquent constructor.
     * @param Vendor $model
     */
    public function __construct(Vendor $model)
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
