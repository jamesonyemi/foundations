<?php

namespace App\Repositories;

use App\Models\JoinDate;
use Illuminate\Support\Collection;
use Session;

class JoinDateRepositoryEloquent implements JoinDateRepository
{
    /**
     * @var JoinDate
     */
    private $model;

    /**
     * JoinDateEloquent constructor.
     * @param JoinDate $model
     */
    public function __construct(JoinDate $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with('user', 'school');
    }

    public function getAllForSchool($company_id)
    {
        $joinDates = new Collection([]);
        $this->model->with('user', 'school')
            ->get()
            ->each(function ($joinDate) use ($joinDates, $company_id) {
                if ($joinDate->company_id == $company_id) {
                    $joinDates->push($joinDate);
                }
            });

        return $joinDates;
    }

    public function getAllForSchoolAndStaff($company_id, $user_id)
    {
        return $this->model->with('user', 'school')
            ->where('company_id', $company_id)
            ->where('user_id', $user_id);
    }
}
