<?php

namespace App\Repositories;

use App\Models\Country;

class CountryRepositoryEloquent implements CountryRepository
{
    /**
     * @var Country
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param Country $model
     */
    public function __construct(Country $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getCountryStudents()
    {
        return $this->model->whereHas('students', function ($query) {
            $query->where('id', '!=', '0');
        });
    }
}
