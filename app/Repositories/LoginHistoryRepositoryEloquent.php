<?php

namespace App\Repositories;

use App\Models\LoginHistory;
use Carbon\Carbon;

class LoginHistoryRepositoryEloquent implements LoginHistoryRepository
{
    /**
     * @var LoginHistory
     */
    private $model;

    /**
     * LoginHistoryRepositoryEloquent constructor.
     * @param LoginHistory $model
     */
    public function __construct(LoginHistory $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }


    public function getAllToday()
    {
        return $this->model->whereDate('created_at', Carbon::today());
    }
}
