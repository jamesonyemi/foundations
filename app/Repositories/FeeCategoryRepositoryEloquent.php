<?php

namespace App\Repositories;

use App\Models\FeeCategory;

class FeeCategoryRepositoryEloquent implements FeeCategoryRepository
{
    /**
     * @var FeeCategory
     */
    private $model;

    /**
     * FeeCategoryRepositoryEloquent constructor.
     * @param FeeCategory $model
     */
    public function __construct(FeeCategory $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSection($section_id)
    {
        return $this->model->whereIn('section_id', [$section_id, '7']);
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForSectionCurrency($section_id, $currency_id)
    {
        return $this->model->whereIn('section_id', [$section_id, '7'])->where('currency_id', $currency_id);
    }
}
