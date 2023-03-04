<?php

namespace App\Repositories;

use function App\Helpers\randomString;
use App\Helpers\Settings;
use App\Models\HelpDesk;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sentinel;
use Session;

class HelpDeskRepositoryEloquent implements HelpDeskRepository
{
    /**
     * @var HelpDesk
     */
    private $model;

    /**
     * HelpDeskRepositoryEloquent constructor.
     * @param HelpDesk $model
     */
    public function __construct(HelpDesk $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYear($company_id, $company_year_id)
    {
        return $this->model->where('company_id', $company_id)->where('company_year_id', $company_year_id)->with('employee');
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id)->with('employee');
    }

    public function getAllOpen($company_id)
    {
        return $this->model->where('company_id', $company_id)->with('employee');
    }

    public function getAllClosed($company_id)
    {
        return $this->model->where('company_id', $company_id)->with('employee');
    }

    public function getAllMe($employee_id)
    {
        return $this->model->where('employee_id', $employee_id)->with('employee');
    }

    public function getAllMine($employee_id)
    {
        return $this->model->where('employee_id', $employee_id)->with('employee');
    }
}
