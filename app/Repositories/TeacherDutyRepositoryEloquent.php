<?php

namespace App\Repositories;

use App\Models\TeacherDuty;

class TeacherDutyRepositoryEloquent implements TeacherDutyRepository
{
    /**
     * @var TeacherDuty
     */
    private $model;

    /**
     * TeacherDutyRepositoryEloquent constructor.
     *
     * @param TeacherDuty $model
     */
    public function __construct(TeacherDuty $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForTeacher($user_id)
    {
        return $this->model->where('user_id', $user_id);
    }

    public function getAllForWeek($date_start, $date_end)
    {
        return $this->model->where(function ($w) use ($date_start) {
            $w->where('start_date', '<=', $date_start)
              ->where('end_date', '>=', $date_start);
        })->orWhere(function ($w) use ($date_end) {
            $w->where('start_date', '<=', $date_end)
              ->where('end_date', '>=', $date_end);
        });
    }
}
