<?php

namespace App\Repositories;

use App\Models\ConferenceDay;
use App\Models\Section;

class ConferenceDayRepositoryEloquent implements ConferenceDayRepository
{
    /**
     * @var ConferenceDay
     */
    private $model;

    /**
     * SectionRepositoryEloquent constructor.
     *
     * @param ConferenceDay $model
     */
    public function __construct(ConferenceDay $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYear($school_year_id)
    {
        return $this->model->where('school_year_id', $school_year_id);
    }

    public function getAllForSchoolYearSchool($school_year_id, $company_id)
    {
        return $this->model->where('school_year_id', $school_year_id)->where('company_id', $company_id);
    }

    public function getAllForSchoolYearSchoolAndHeadTeacher($school_year_id, $company_id, $user_id)
    {
        return $this->model->where('school_year_id', '=', $school_year_id)
                           ->where('company_id', '=', $company_id)
                           ->where('section_teacher_id', '=', $user_id);
    }

    public function getAllForSchoolYearSchoolAndSession($school_year_id, $company_id, $session_id)
    {
        return $this->model->where('school_year_id', '=', $school_year_id)
                           ->where('company_id', '=', $company_id)
                           ->where('session_id', '=', $session_id);
    }

    public function getAllSession($session_id)
    {
        return $this->model->where('session_id', '=', $session_id);
    }
}
