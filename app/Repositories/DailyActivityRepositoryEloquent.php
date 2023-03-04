<?php

namespace App\Repositories;

use function App\Helpers\randomString;
use App\Helpers\Settings;
use App\Models\DailyActivity;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sentinel;
use Session;

class DailyActivityRepositoryEloquent implements DailyActivityRepository
{
    /**
     * @var DailyActivity
     */
    private $model;

    /**
     * HelpDeskRepositoryEloquent constructor.
     * @param HelpDesk $model
     */
    public function __construct(DailyActivity $model)
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
        return $this->model->whereHas('employee', function ($query) use ($company_id) {
            $query->where('employees.company_id', $company_id);
        })->with('employee');
    }

    public function getAllForSchoolDay($company_id, $date)
    {
        return $this->model->whereYear('daily_activities.created_at', $date)->whereMonth('daily_activities.created_at', $date)->whereDay('daily_activities.created_at', $date)->whereHas('employee', function ($query) use ($company_id) {
            $query->where('employees.company_id', $company_id);
        })->with('employee');
    }

    public function getAllForSchoolDepartmentDay($company_id, $section_id, $date)
    {
        return $this->model->whereYear('daily_activities.created_at', $date)->whereMonth('daily_activities.created_at', $date)->whereDay('daily_activities.created_at', $date)->whereHas('employee', function ($query) use ($company_id, $section_id) {
            $query->where('employees.company_id', $company_id)->where('employees.section_id', $section_id);
        })->with('employee');
    }

    public function getForEmployee($employee_id, $date)
    {
        return $this->model->whereYear('daily_activities.created_at', $date)->whereMonth('daily_activities.created_at', $date)->whereDay('daily_activities.created_at', $date)->whereHas('employee', function ($query) use ($employee_id) {
            $query->where('employee_id', $employee_id);
        })->with('employee');
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
        return $this->model->where('employee_id', $employee_id)->orderBy('id', 'Asc');
    }
}
