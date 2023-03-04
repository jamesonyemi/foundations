<?php

namespace App\Repositories;

use App\Models\MarkValue;

class MarkValueRepositoryEloquent implements MarkValueRepository
{
    /**
     * @var MarkValue
     */
    private $model;

    /**
     * MarkValueRepositoryEloquent constructor.
     * @param MarkValue $model
     */
    public function __construct(MarkValue $model)
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

    public function getAllForSubject($subject_id)
    {
        return $this->model->join('subjects', 'subjects.mark_system_id', '=', 'mark_values.mark_system_id')
            ->where('subjects.id', $subject_id)
            ->select('mark_values.*');
    }

    public function getAllMarkValueOptions($subject_id)
    {
        return $this->model->join('subjects', 'subjects.mark_system_id', '=', 'mark_values.mark_system_id')
            ->where('subjects.id', $subject_id)
            ->whereIn('mark_values.grade', ['Y', 'I', 'Z'])
            ->select('mark_values.*');
    }
}
