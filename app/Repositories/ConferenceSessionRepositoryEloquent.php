<?php

namespace App\Repositories;

use App\Models\ConferenceSession;

class ConferenceSessionRepositoryEloquent implements ConferenceSessionRepository
{
    /**
     * @var ConferenceSession
     */
    private $model;

    /**
     * SectionRepositoryEloquent constructor.
     *
     * @param ConferenceSession $model
     */
    public function __construct(ConferenceSession $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id);
    }

    public function getAllForSchoolYearSchool($company_year_id, $company_id)
    {
        return $this->model->where('company_year_id', $company_year_id);
    }

    public function getAllForSchoolYearSchoolAndHeadTeacher($company_year_id, $company_id, $user_id)
    {
        return $this->model->where('company_year_id', '=', $company_year_id)
                           ->where('company_id', '=', $company_id)
                           ->where('section_teacher_id', '=', $user_id);
    }

    public function getAllForDay($company_id, $company_year_id, $conference_day_id)
    {
        return $this->model->where('company_year_id', '=', $company_year_id)
            ->where('company_id', '=', $company_id)
            ->where('conference_day_id', '=', $conference_day_id);
    }

    public function getAllForSchoolYearSchoolAndSession($company_year_id, $company_id, $session_id)
    {
        return $this->model->where('company_year_id', '=', $company_year_id)
                           ->where('company_id', '=', $company_id)
                           ->where('session_id', '=', $session_id);
    }

    public function getAllSession($session_id)
    {
        return $this->model->where('session_id', '=', $session_id);
    }
}
