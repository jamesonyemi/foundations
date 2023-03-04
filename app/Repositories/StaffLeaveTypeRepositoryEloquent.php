<?php

namespace App\Repositories;

use App\Models\StaffLeaveType;

class StaffLeaveTypeRepositoryEloquent implements StaffLeaveTypeRepository
{
    /**
     * @var StaffLeaveType
     */
    private $model;

    /**
     * StaffLeaveTypeRepositoryEloquent constructor.
     * @param StaffLeaveType $model
     */
    public function __construct(StaffLeaveType $model)
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

    public function getAllForEmployee($employee_id)
    {
        return $this->model->where('user_id', $employee_id);
    }

    public function getAllForApprover($employee_id)
    {
        return $this->model->where('user_id', $employee_id);
    }
}
