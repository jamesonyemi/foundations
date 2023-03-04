<?php

namespace App\Repositories;

use App\Models\DormitoryRoom;

class DormitoryRoomRepositoryEloquent implements DormitoryRoomRepository
{
    /**
     * @var DormitoryRoom
     */
    private $model;

    /**
     * DormitoryRoomRepositoryEloquent constructor.
     * @param DormitoryRoom $model
     */
    public function __construct(DormitoryRoom $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
