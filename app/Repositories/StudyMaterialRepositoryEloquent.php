<?php

namespace App\Repositories;

use App\Models\StudyMaterial;

class StudyMaterialRepositoryEloquent implements StudyMaterialRepository
{
    /**
     * @var StudyMaterial
     */
    private $model;

    /**
     * StudyMaterialRepositoryEloquent constructor.
     * @param StudyMaterial $model
     */
    public function __construct(StudyMaterial $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForUser($user_id)
    {
        return $this->model->where('user_id', $user_id);
    }

    public function getAllForUserAndGroup($user_id, $student_group_id)
    {
        return $this->model->where('user_id', $user_id)->where('student_group_id', $student_group_id);
    }

    public function getAllForStudent($employee_id)
    {
        return $this->model
            ->where('employee_id', $employee_id)
            /*->where('study_materials.date_off', '>=', date('Y-m-d'))*/
            ->distinct();
    }
}
