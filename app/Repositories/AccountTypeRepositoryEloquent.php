<?php

namespace App\Repositories;

use App\Models\AccountType;

class AccountTypeRepositoryEloquent implements AccountTypeRepository
{
    /**
     * @var AccountType
     */
    private $model;

    /**
     * AccountRepositoryEloquent constructor.
     * @param Account $model
     */
    public function __construct(AccountType $model)
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
