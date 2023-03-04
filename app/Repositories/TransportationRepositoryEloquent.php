<?php

namespace App\Repositories;

use App\Models\Transportation;

class TransportationRepositoryEloquent implements TransportationRepository
{
    /**
     * @var Transportation
     */
    private $model;

    /**
     * TransportationRepositoryEloquent constructor.
     * @param Transportation $model
     */
    public function __construct(Transportation $model)
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

    public function getAllForUser($user_id)
    {
        return $this->model
            ->join('transportation_passengers', 'transportation_passengers.transportation_id', '=', 'transportations.id')
            ->whereNull('transportation_passengers.deleted_at')
            ->where('transportation_passengers.user_id', $user_id)
            ->select('transportations.*');
    }
}
