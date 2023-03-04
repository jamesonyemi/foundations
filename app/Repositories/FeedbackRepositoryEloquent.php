<?php

namespace App\Repositories;

use App\Models\Feedback;

class FeedbackRepositoryEloquent implements FeedbackRepository
{
    /**
     * @var Feedback
     */
    private $model;

    /**
     * FeedbackRepositoryEloquent constructor.
     * @param Feedback $model
     */
    public function __construct(Feedback $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
