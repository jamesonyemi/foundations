<?php

namespace App\Repositories;

use function App\Helpers\randomString;
use App\Helpers\Settings;
use App\Models\DailyActivity;
use App\Models\HrPolicy;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sentinel;
use Session;

class HrPolicyRepositoryEloquent implements HrPolicyRepository
{
    /**
     * @var DailyActivity
     */
    private $model;

    /**
     * HelpDeskRepositoryEloquent constructor.
     * @param HelpDesk $model
     */
    public function __construct(HrPolicy $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->whereHas('employee', function ($query) use ($company_id) {
            $query->where('employees.company_id', $company_id);
        })->with('employee');
    }
}
