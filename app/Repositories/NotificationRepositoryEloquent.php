<?php

namespace App\Repositories;

use App\Models\Notification;

class NotificationRepositoryEloquent implements NotificationRepository
{
    /**
     * @var Notification
     */
    private $model;

    /**
     * NotificationRepositoryEloquent constructor.
     * @param Notification $model
     */
    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
