<?php

namespace App\Repositories;

use App\Models\StaffAttendance;

class StaffAttendanceRepositoryEloquent implements StaffAttendanceRepository
{
    /**
     * @var StaffAttendance
     */
    private $model;

    /**
     * StaffAttendanceRepository constructor.
     * @param StaffAttendance $model
     */
    public function __construct(StaffAttendance $model)
    {
        $this->model = $model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id);
    }

    public function getAllForSchoolSchoolYear($company_id, $company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('company_id', $company_id);
    }

    public function getAllForSchoolSchoolYearStaff($company_id, $company_year_id, $staff_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('company_id', $company_id)
            ->where('user_id', $staff_id);
    }
}
