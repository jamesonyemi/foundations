<?php

namespace App\Repositories;

use App\Models\StudentGroup;

class StudentGroupRepositoryEloquent implements StudentGroupRepository
{
    /**
     * @var StudentGroup
     */
    private $model;

    /**
     * StudentRepositoryEloquent constructor.
     * @param StudentGroup $model
     */
    public function __construct(StudentGroup $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYearSchool($company_year_id, $company_id)
    {
        return $this->model->whereHas('section', function ($q) use ($company_year_id, $company_id) {
            $q->where('sections.company_id', $company_id);
        });
    }

    public function getAllForSection($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }
}
