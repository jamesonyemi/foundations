<?php

namespace App\Repositories;

use App\Models\Currency;

class CurrencyRepositoryEloquent implements CurrencyRepository
{
    /**
     * @var Currency
     */
    private $model;

    /**
     * SalaryRepositoryEloquent constructor.
     * @param Country $model
     */
    public function __construct(Currency $model)
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
