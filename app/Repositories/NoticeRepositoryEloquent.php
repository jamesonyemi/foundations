<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepositoryEloquent implements NoticeRepository
{
    /**
     * @var Notice
     */
    private $model;

    /**
     * NoticeRepositoryEloquent constructor.
     * @param Notice $model
     */
    public function __construct(Notice $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYearAndSchool($company_year_id, $company_id)
    {
        return $this->model->with('subject', 'student_group', 'school_year')
            ->where('notices.company_year_id', $company_year_id)
            ->where('notices.company_id', $company_id);
    }

    public function getAllForSchoolYearAndGroup($company_year_id, $student_group_id, $user_id)
    {
        return $this->model->with('subject', 'student_group', 'school_year')
            ->where('notices.student_group_id', $student_group_id)
            ->where('notices.company_year_id', $company_year_id)
            ->where('notices.user_id', $user_id);
    }
}
