<?php

namespace App\Repositories;

use App\Models\Option;

class OptionRepositoryEloquent implements OptionRepository
{
    /**
     * @var Option
     */
    private $model;

    /**
     * OptionRepositoryEloquent constructor.
     * @param Option $model
     */
    public function __construct(Option $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->where('company_id', 0);
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where(function ($query) use ($company_id) {
            return $query->where('company_id', $company_id)
                ->orWhere('company_id', 0);
        });
    }
}
