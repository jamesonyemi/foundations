<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\CompanyYear;
use App\Models\Department;
use App\Models\Direction;
use App\Models\Employee;
use App\Models\FeeCategory;
use App\Models\GeneralLedger;
use App\Models\Invoice;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Session;

class StudentRepositoryEloquent implements StudentRepository
{
    /**
     * @var Student
     */
    private $model;

    /**
     * StudentRepositoryEloquent constructor.
     * @param Student $model
     */
    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->Has('alumni', '=', 0);
    }

    public function getAllMale()
    {
        return $this->model->whereHas('user', function ($query) {
            $query->where('gender', '=', '1');
        });
    }

    public function getAllFeMale()
    {
        return $this->model->whereHas('user', function ($query) {
            $query->where('gender', '=', '0');
        });
    }

    public function getAllForSchoolYearAndSection($company_year_id, $company_id, $section_id)
    {
        return $this->model->whereHas('user', function ($q) use ($company_year_id, $company_id, $section_id) {
            $q->where('students.department_id', $section_id)
                ->where('students.company_id', $company_id);
        });

        /* return $this->model->where('company_year_id', $company_year_id)
             ->where('section_id', $section_id);*/
    }

    public function getAllForSchoolYearAndDirection($company_year_id, $company_id, $direction_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('active', function ($q) use ($company_year_id, $company_id, $direction_id) {
                $q->where('students.direction_id', $direction_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_statuses.company_year_id', $company_year_id);
            });

        /* return $this->model->where('company_year_id', $company_year_id)
             ->where('section_id', $section_id);*/
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->where('company_year_id', $company_year_id)->with('user');
    }

    /* public function getAllActive($company_year_id, $semester_id, $company_id)
     {
         return $this->model->whereHas('active', function ($q) use ($company_year_id, $semester_id, $company_id) {
             $q->where('student_statuses.company_year_id', $company_year_id)
                 ->where('students.company_id', $company_id);
         });
     }*/

    public function getAllActive($company_year_id, $semester_id, $company_id)
    {
        return $this->model->whereHas('active', function ($q) {
            $q->where('student_statuses.company_year_id', session('current_company_year'))
                    ->where('student_statuses.semester_id', session('current_company_semester'));
        })
            ->whereHas('section')
            ->whereHas('programme')
            ->whereHas('session')
            ->whereHas('level')
            ->whereHas('country')
            ->whereHas('academicyear')
            ->whereHas('entrymode')
            ->where('company_id', '=', session('current_company'))
            ->whereNull('students.deleted_at')
            ->groupby('students.id')
            ->orderBy('students.id', 'Desc');
    }

    public function getAllPendingApproval($company_year_id, $semester_id, $company_id)
    {
        return $this->model->where('status', '=', 'pending')
            ->whereHas('section')
            ->whereHas('programme')
            ->whereHas('session')
            ->whereHas('level')
            ->whereHas('country')
            ->whereHas('academicyear')
            ->whereHas('entrymode')
            ->where('company_id', '=', session('current_company'))
            /*->where('company_year_id', session('current_company_year'))
            ->where('semester_id', session('current_company_semester'))*/
            ->whereNull('students.deleted_at')
            ->groupby('students.id')
            ->orderBy('students.id', 'Desc');
    }

    public function getAllActiveExport_($request)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('active')
            ->where('students.company_id', '=', session('current_company'))
            ->where('students.section_id', '=', $request->section_id);
    }

    public function getAllActiveExport($request)
    {
        $query = Student::query()->Has('alumni', '=', 0)
            ->whereHas('active')
            ->whereHas('section')
            ->whereHas('programme')
            ->whereHas('session')
            ->whereHas('level')
            ->whereHas('country')
            ->whereHas('academicyear')
            ->whereHas('entrymode')
            ->whereHas('invoice')
            ->whereNull('students.deleted_at')
            ->where('students.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'students.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('section_id', '=', $request->section_id);
        }

        if (isset($request->direction_id) && ! empty($request->direction_id)) {
            $query = $query->where('direction_id', '=', $request->direction_id);
        }

        if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
            $query = $query->where('company_year_id', '=', $request->company_year_id);
        }

        if (isset($request->graduation_year_id) && ! empty($request->graduation_year_id)) {
            $query = $query->where('graduation_year_id', '=', $request->graduation_year_id);
        }

        if (isset($request->semester_id) && ! empty($request->semester_id)) {
            $query = $query->where('semester_id', '=', $request->semester_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('level_id', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('religion_id', '=', $request->religion_id);
        }

        if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        if (isset($request->intake_period_id) && ! empty($request->intake_period_id)) {
            $query = $query->where('students.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllExport($request)
    {
        $query = Student::query()->Has('alumni', '=', 0)
            ->where('students.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'students.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('section_id', '=', $request->section_id);
        }

        if (isset($request->direction_id) && ! empty($request->direction_id)) {
            $query = $query->where('direction_id', '=', $request->direction_id);
        }

        if (isset($request->graduation_year_id) && ! empty($request->graduation_year_id)) {
            $query = $query->where('graduation_year_id', '=', $request->graduation_year_id);
        }

        if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
            $query = $query->where('company_year_id', '=', $request->company_year_id);
        }

        if (isset($request->semester_id) && ! empty($request->semester_id)) {
            $query = $query->where('semester_id', '=', $request->semester_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('level_id', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('religion_id', '=', $request->religion_id);
        }

        if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        if (isset($request->intake_period_id) && ! empty($request->intake_period_id)) {
            $query = $query->where('students.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('students.company_id', session('current_company'))
            ->whereHas('section')
            ->whereHas('programme')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '0') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllActiveFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('students.deleted_at')
            ->whereNull('sections.deleted_at')
            /*->where('students.company_id', session('current_company'))
            ->where('students.semester_id', session('current_semester'))*/
            ->whereHas('section')
            ->whereHas('programme')
            ->whereHas('session')
            ->whereHas('level')
            ->whereHas('country')
            ->whereHas('academicyear')
            ->whereHas('entrymode')
            ->whereHas('invoice')
            ->whereHas('active')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }
                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllInvoiceFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('students.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('students.company_id', session('current_company'))
            ->whereHas('section')
            ->whereHas('programme')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }
                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllDeferred($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('deferred', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_deferrals.company_year_id', $company_year_id)
                ->where('students.company_id', $company_id)
                ->where('student_deferrals.semester_id', $semester_id);
            });
    }

    public function getAllDeferredFemale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->whereHas('deferred', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_deferrals.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_deferrals.semester_id', $semester_id);
            });
    }

    public function getAllDeferredMale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            })
            ->whereHas('deferred', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_deferrals.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_deferrals.semester_id', $semester_id);
            });
    }

    public function getAllDeferredFilter($request)
    {
        $studentItems = $this->model->whereHas('deferred')
            ->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->join('student_deferrals', 'student_deferrals.student_id', '=', 'students.id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            /*->where('students.company_year_id', '=', session('current_company_year'))*/
            ->where('students.company_id', '=', session('current_company'))
            ->whereHas('programme')
            /*->where('student_deferrals.company_year_id', '=', session('current_company_year'))
            ->where('student_deferrals.semester_id', '=', session('current_company_semester'))*/
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllDeferredExport($request)
    {
        $query = Student::query()->Has('alumni', '=', 0)
            ->whereHas('deferred')
            ->where('students.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'students.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('section_id', '=', $request->section_id);
        }

        if (isset($request->direction_id) && ! empty($request->direction_id)) {
            $query = $query->where('direction_id', '=', $request->direction_id);
        }

        if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
            $query = $query->where('company_year_id', '=', $request->company_year_id);
        }

        if (isset($request->semester_id) && ! empty($request->semester_id)) {
            $query = $query->where('semester_id', '=', $request->semester_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('level_id', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('religion_id', '=', $request->religion_id);
        }

        if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        if (isset($request->intake_period_id) && ! empty($request->intake_period_id)) {
            $query = $query->where('students.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllDrop($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('drop', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_drops.company_year_id', $company_year_id)
                ->where('students.company_id', $company_id)
                ->where('student_drops.semester_id', $semester_id);
            });
    }

    public function getAllDropFemale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->whereHas('drop', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_drops.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_drops.semester_id', $semester_id);
            });
    }

    public function getAllDropMale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            })
            ->whereHas('drop', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_drops.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_drops.semester_id', $semester_id);
            });
    }

    public function getAllDropFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->whereHas('drop')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->join('student_drops', 'student_drops.student_id', '=', 'students.id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            /*->where('students.company_year_id', '=', session('current_company_year'))*/
            ->where('students.company_id', '=', session('current_company'))
            ->whereHas('programme')
            /*->where('student_drops.company_year_id', '=', session('current_company_year'))
            ->where('student_drops.semester_id', '=', session('current_company_semester'))*/
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllGraduating($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('graduation', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_graduations.company_year_id', $company_year_id)
                ->where('students.company_id', $company_id)
                ->where('student_graduations.semester_id', $semester_id);
            });
    }

    public function getAllGraduatingMale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            })
            ->whereHas('graduation', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_graduations.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_graduations.semester_id', $semester_id);
            });
    }

    public function getAllGraduatingFemale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->whereHas('graduation', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_graduations.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->where('student_graduations.semester_id', $semester_id);
            });
    }

    public function getAllGraduatingFilter($request)
    {
        $studentItems = $this->model->whereHas('graduation')
            ->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->join('student_graduations', 'student_graduations.student_id', '=', 'students.id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            /*->where('students.company_year_id', '=', session('current_company_year'))
            ->where('students.company_id', '=', session('current_company'))*/
            ->whereHas('programme')
            ->where('student_graduations.company_year_id', '=', session('current_company_year'))
            ->where('student_graduations.semester_id', '=', session('current_company_semester'))
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllAlumni($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('active', '=', 0)
            ->whereHas('alumni', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('students.company_id', $company_id);
            });
    }

    public function getAllAlumniExport_($request)
    {
        return $this->model->Has('active', '=', 0)
            ->whereHas('alumni')
            ->where('students.company_id', '=', session('current_company'));
    }

    public function getAllAlumniExport($request)
    {
        $query = Student::query()->Has('active', '=', 0)
            ->whereHas('alumni')
            ->where('students.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'students.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('section_id', '=', $request->section_id);
        }

        if (isset($request->direction_id) && ! empty($request->direction_id)) {
            $query = $query->where('direction_id', '=', $request->direction_id);
        }

        if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
            $query = $query->where('company_year_id', '=', $request->company_year_id);
        }

        if (isset($request->semester_id) && ! empty($request->semester_id)) {
            $query = $query->where('semester_id', '=', $request->semester_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('level_id', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('religion_id', '=', $request->religion_id);
        }

        if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        if (isset($request->intake_period_id) && ! empty($request->intake_period_id)) {
            $query = $query->where('students.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllAlumniMale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('active', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            })
            ->whereHas('alumni', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('students.company_id', $company_id);
            });
    }

    public function getAllAlumniFemale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('active', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->whereHas('alumni', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('students.company_id', $company_id);
            });
    }

    public function getAllAlumniFilter($request)
    {
        $studentItems = $this->model->whereHas('alumni')
            ->Has('active', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('students.company_id', '=', session('current_company'))
            ->whereHas('programme')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllAdmittedForSchool($company_year_id, $semester_id, $company_id)
    {
        return $this->model->whereHas('programme')
            ->whereHas('section')
            ->whereHas('user')
            ->where('company_id', '=', $company_id)
            ->where('status', '=', 'active')
            ->where('company_year_id', $company_year_id)
            ->where('semester_id', '=', $semester_id)
            ->whereNull('students.deleted_at');
    }

    public function getAllAdmittedForAward($company_year_id, $semester_id, $company_id)
    {
        return $this->model->whereHas('programme')
            ->whereHas('section')
            ->where('discount', '=', 0)
            ->where('company_id', '=', $company_id)
            ->where('company_year_id', $company_year_id)
            ->where('semester_id', '=', $semester_id)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->whereNotNull('users.email')
            ->whereNull('students.deleted_at')
            ->whereNull('users.deleted_at')
            ->select('students.*');
    }

    public function getAllAdmittedForSchoolMale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('admission', function ($query) {
                /*$query->whereStatus(0);*/
            })
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            })
            ->where('company_id', $company_id)
            ->where('company_year_id', $company_year_id)
            ->where('semester_id', $semester_id)
            ->with('user');
    }

    public function getAllAdmittedForSchoolFemale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('admission', function ($query) {
                /*$query->whereStatus(0);*/
            })
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->where('company_id', $company_id)
            ->where('company_year_id', $company_year_id)
            ->where('semester_id', $semester_id)
            ->with('user');
    }

    public function getAllAdmittedForSchoolFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('students.deleted_at')
            ->whereNull('sections.deleted_at')
           /* ->where('students.company_year_id', '=', session('current_company_year'))
            ->where('students.semester_id', '=', session('current_company_semester'))*/
            ->where('students.company_id', '=', session('current_company'))
            ->whereHas('section')
            ->whereHas('programme')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }

                if (! is_null($request['intake_period_id']) && $request['intake_period_id'] != '' && $request['intake_period_id'] != 'null') {
                    $w->where('students.intake_period_id', $request['intake_period_id']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'users.mobile as mobile',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllAdmittedForSchoolExport($request)
    {
        $query = Student::query()->Has('alumni', '=', 0)
            ->whereHas('section')
            ->whereHas('programme')
            ->whereNull('users.deleted_at')
            ->whereNull('students.deleted_at')
            ->where('students.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'students.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('section_id', '=', $request->section_id);
        }

        if (isset($request->direction_id) && ! empty($request->direction_id)) {
            $query = $query->where('direction_id', '=', $request->direction_id);
        }

        if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
            $query = $query->where('company_year_id', '=', $request->company_year_id);
        }

        if (isset($request->graduation_year_id) && ! empty($request->graduation_year_id)) {
            $query = $query->where('graduation_year_id', '=', $request->graduation_year_id);
        }

        if (isset($request->semester_id) && ! empty($request->semester_id)) {
            $query = $query->where('semester_id', '=', $request->semester_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('level_id', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('religion_id', '=', $request->religion_id);
        }

        if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        if (isset($request->intake_period_id) && ! empty($request->intake_period_id)) {
            $query = $query->where('students.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllRegistration($company_year_id, $semester_id, $company_id)
    {
        return $this->model->whereHas('registration', function ($q) use ($company_year_id, $semester_id, $company_id) {
            $q->where('registrations.company_year_id', $company_year_id)
                ->where('students.company_id', $company_id)
                ->where('registrations.semester_id', $semester_id);
        });
    }

    public function getAllRegistrationForSubject($company_year_id, $semester_id, $subject_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('registration', function ($q) use ($company_year_id, $semester_id, $subject_id) {
                $q->where('registrations.company_year_id', $company_year_id)
                    ->where('students.company_id', session('current_company'))
                    ->where('registrations.semester_id', $semester_id)
                    ->where('registrations.subject_id', $subject_id);
            });
    }

    public function getAllRegistrationForSubjectAndSession($company_year_id, $semester_id, $subject_id, $session_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('registration', function ($q) use ($company_year_id, $semester_id, $subject_id, $session_id) {
                $q->where('registrations.company_year_id', $company_year_id)
                    ->where('students.company_id', session('current_company'))
                    ->where('students.session_id', $session_id)
                    ->where('registrations.semester_id', $semester_id)
                    ->where('registrations.subject_id', $subject_id);
            });
    }

    public function getAllRegistrationFilter($request)
    {
        $studentItems = $this->model->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereHas('registration', function ($w) use ($request) {
                if (! is_null($request['subject_id']) && $request['subject_id'] != '*') {
                    $w->where('registrations.subject_id', $request['subject_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('registrations.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('registrations.semester_id', $request['semester_id']);
                }
            })
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
               /* 'registrations.mid_sem as mid_sem',
                'registrations.exams as exams',*/
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllRegistrationExport($request)
    {
        $query = Student::query()->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereHas('registration', function ($query) use ($request) {
                if (isset($request->subject_id) && ! empty($request->subject_id)) {
                    $query = $query->where('registrations.subject_id', '=', $request->subject_id);
                }

                if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
                    $query = $query->where('registrations.company_year_id', '=', $request->company_year_id);
                }

                if (isset($request->semester_id) && ! empty($request->semester_id)) {
                    $query = $query->where('registrations.semester_id', '=', $request->semester_id);
                }
            });

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('students.country_id', '=', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('students.section_id', '=', $request->section_id);
        }

        if (isset($request->direction_id) && ! empty($request->direction_id)) {
            $query = $query->where('students.direction_id', '=', $request->direction_id);
        }

        if (isset($request->graduation_year_id) && ! empty($request->graduation_year_id)) {
            $query = $query->where('students.graduation_year_id', '=', $request->graduation_year_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('students.level_id', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('students.entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('students.session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('students.marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('students.religion_id', '=', $request->religion_id);
        }

        if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        if (isset($request->intake_period_id) && ! empty($request->intake_period_id)) {
            $query = $query->where('students.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllScoreSheetFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->leftJoin('directions', 'directions.id', '=', 'students.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('students.deleted_at')
            ->whereNull('sections.deleted_at')
            /* ->where('students.company_year_id', '=', session('current_company_year'))
             ->where('students.semester_id', '=', session('current_company_semester'))*/
            ->where('students.company_id', '=', session('current_company'))
            ->whereHas('section')
            ->whereHas('programme')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('students.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id']) && $request['section_id'] != '*') {
                    $w->where('students.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('students.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('students.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['graduation_year_id']) && $request['graduation_year_id'] != '*') {
                    $w->where('students.graduation_year_id', $request['graduation_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('students.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('students.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('students.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }

                if (! is_null($request['intake_period_id']) && $request['intake_period_id'] != '' && $request['intake_period_id'] != 'null') {
                    $w->where('students.intake_period_id', $request['intake_period_id']);
                }
            })->orderBy('students.id')
            ->select(
                'users.id as user_id',
                'students.id as student_id',
                'students.student_no as student_no',
                'students.sID as sID',
                'students.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllStudentGroupsForStudentUserAndSchoolYear($student_user_id, $company_year_id)
    {
        $studentgroups = new Collection([]);
        $this->model->Has('alumni', '=', 0)
            ->with('user', 'studentsgroups')
            ->get()
            ->filter(function ($studentItem) use ($student_user_id, $company_year_id) {
                return isset($studentItem->user) &&
                    $studentItem->user->id == $student_user_id &&
                    $studentItem->company_year_id == $company_year_id;
            })
            ->each(function ($studentItem) use ($studentgroups) {
                foreach ($studentItem->studentsgroups as $studentsgroup) {
                    $studentgroups->push($studentsgroup->id);
                }
            });

        return $studentgroups;
    }

    public function getAllForStudentGroup($student_group_id)
    {
        $studentitems = new Collection([]);
        $this->model->Has('alumni', '=', 0)
            ->with('user', 'studentsgroups')
            ->orderBy('order')
            ->get()
            ->each(function ($studentItem) use ($studentitems, $student_group_id) {
                foreach ($studentItem->studentsgroups as $studentsgroup) {
                    if ($studentsgroup->id == $student_group_id) {
                        $studentitems->push($studentItem);
                    }
                }
            });

        return $studentitems;
    }

    public function getAllForStudentDirection($direction_id)
    {
        $studentitems = new Collection([]);
        $this->model->Has('alumni', '=', 0)
            ->with('user', 'programme', 'active')
            ->orderBy('order')
            ->get()
            ->each(function ($studentItem) use ($studentitems, $direction_id) {
                foreach ($studentItem->programme as $program) {
                    if ($program->id == $direction_id) {
                        $studentitems->push($studentItem);
                    }
                }
            });

        return $studentitems;
    }

    /*   public function getAllForSchoolYearAndSchool($company_id)
       {
           return $this->model->where('company_id', $company_id);
       }*/

    public function getAllForSchoolYearAndSchool($school_year_id)
    {
        return $this->model->whereHas('active');
    }

    public function getAllForSchoolFees($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForSchoolYearSchoolAndSection($company_year_id, $company_id, $section_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('company_id', $company_id)
            ->where('section_id', $section_id);
    }

    public function getSchoolForStudent($student_user_id, $company_year_id)
    {
        return $this->model->whereIn('user_id', $student_user_id)->where('company_year_id', $company_year_id);
    }

    public function getAllForSubject($subject_ids)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('registration', function ($q) use ($subject_ids) {
                $q->where('registrations.company_year_id', session('current_company_year'))
                    ->where('students.company_id', session('current_company'))
                    ->where('registrations.semester_id', session('current_company_semester'))
                    ->whereIn('registrations.subject_id', $subject_ids);
            });
    }

    public function create(array $data, $activate = true)
    {
        $user_exists = User::where('email', $data['email'])->first();
        if (! isset($user_exists->id)) {
            $user_tem = Sentinel::registerAndActivate($data, $activate);
            $user = User::find($user_tem->id);
        } else {
            $user = $user_exists;
        }
        try {
            $role = Sentinel::findRoleBySlug('student');
            $role->users()->attach($user);
        } catch (\Exception $e) {
        }
        $user->update(['birth_date'=>$data['birth_date'],
            'middle_name'=>isset($data['middle_name']) ? $data['middle_name'] : ' ',
            'birth_city'=>isset($data['birth_city']) ? $data['birth_city'] : '-',
            'gender' => isset($data['gender']) ? $data['gender'] : 0,
            'address' => isset($data['address']) ? $data['address'] : '-',
            'mobile' => isset($data['mobile']) ? $data['mobile'] : 0,
            'phone' => isset($data['phone']) ? $data['phone'] : 0, ]);

        if (is_null(session('current_company')) && Settings::get('multi_school') == 'no' && isset(Company::first()->id)) {
            session('current_company', Company::first()->id);
        }

        /*$student = new Student();
        $student->section_id = $data['section_id'];
        $student->order = $data['order'];
        $student->company_year_id = session('current_company_year');
        $student->company_id = session('current_company');
        $student->user_id = $user->id;
        $student->save();

        $school = Company::find(session('current_company'));;
        $student->student_no = $school->student_card_prefix . $student->id;
        $student->save();

        //if(!is_null($data['student_group_id'])){
            //$studentGroup = StudentGroup::find($data['student_group_id']);
            //$studentGroup->students()->attach($student->id);
        //}

        return $user;*/

        $student = new Student();
        $student->section_id = $data['section_id'];
        if (session('current_company_type') == 3) {
            $student->country_id = $data['country_id'];
            $student->direction_id = $data['direction_id'];
            $student->intake_period_id = $data['intake_period_id'];
            $student->entry_mode_id = $data['entry_mode_id'];
            $student->session_id = $data['session_id'];
            $student->marital_status_id = $data['marital_status_id'];
            $student->no_of_children = $data['no_of_children'];
            $student->religion_id = $data['religion_id'];
            $student->denomination = $data['denomination'];
            $student->disability = $data['disability'];
            $student->contact_email = $data['contact_email'];
            $student->order = $data['order'];
        }
        $student->level_of_adm = $data['level_of_adm'];
        $student->level_id = $data['level_id'];
        $student->company_year_id = session('current_company_year');
        $student->graduation_year_id = $data['graduation_year_id'];
        $student->company_id = session('current_company');
        $student->semester_id = session('current_company_semester');
        $student->campus_id = $data['campus_id'];
        $student->contact_relation = $data['contact_relation'];
        $student->contact_name = $data['contact_name'];
        $student->contact_address = $data['contact_address'];
        $student->contact_phone = $data['contact_phone'];
        $student->user_id = $user->id;
        $student->save();

        $school = Company::find(session('current_company'));
        $yearCode = CompanyYear::find(session('current_company_year'));
        $departmentCode = Department::find($data['section_id']);

        if (session('current_company_type') == 3) {
            $programmeCode = Direction::find($data['direction_id']);
            $student->student_no = $school->student_card_prefix.'/'.$departmentCode->id_code.'/'.$programmeCode->id_code.'/'.$yearCode->id_code.$school->next_id_no;
            $student->sID = $school->student_card_prefix.'/'.$departmentCode->id_code.'/'.$programmeCode->id_code.'/'.$yearCode->id_code.str_pad($school->next_id_no, 3, '0', STR_PAD_LEFT);
            if ($data['country_id'] == '1') {
                $student->currency_id = '1';
            } else {
                $student->currency_id = '2';
            }
        }

        if (session('current_company_type') != 3) {
            $student->currency_id = '1';

            $student->student_no = $school->student_card_prefix.'/'.$departmentCode->id_code.'/'.$yearCode->id_code.$school->next_id_no;
            $student->sID = $school->student_card_prefix.'/'.$departmentCode->id_code.'/'.$yearCode->id_code.$school->next_id_no;
        }
        $student->save();

        if (session('current_company_type') != 3) {
            StudentStatus::firstOrCreate(['company_id' => session('current_company'), 'company_year_id' => session('current_company_year'), 'semester_id' => session('current_company_semester'), 'student_id' => $student->id]);
        }

        $school2 = Company::find(session('current_company'));
        $school2->next_id_no = $school2->next_id_no + $school2->id_interval;
        $school2->save();

        $gs = Department::where('company_id', '=', session('current_company'))
            ->where('title', '=', 'General')
            ->get()->first();

        $fees = FeeCategory::all()->whereIn('section_id', [$data['section_id'], $gs->id])
            ->where('company_id', '=', session('current_company'))
            ->where('currency_id', '=', $student->currency_id)
            ->where('level_id', '=', $student->level_id);

        $invoice = new Invoice();
        $invoice->student_id = $student->id;
        $invoice->user_id = $student->user_id;
        $invoice->company_id = session('current_company');
        $invoice->company_year_id = session('current_company_year');
        $invoice->semester_id = session('current_company_semester');
        $invoice->currency_id = $student->currency_id;
        $invoice->total_fees = $fees->sum('amount');
        $invoice->amount = $fees->sum('amount');
        $invoice->save();

        foreach ($fees as $fee) {
            $feesStatus = new FeesStatus();
            $feesStatus->invoice_id = $invoice->id;
            $feesStatus->user_id = $student->user_id;
            $feesStatus->student_id = $student->id;
            $feesStatus->company_id = session('current_company');
            $feesStatus->company_year_id = session('current_company_year');
            $feesStatus->semester_id = session('current_company_semester');

            $feesStatus->title = $fee->title;
            $feesStatus->currency_id = $student->currency_id;
            $feesStatus->amount = $fee->amount;
            $feesStatus->fee_category_id = $fee->id;
            $feesStatus->save();
        }

        if (session('current_company_type') == 3) {
            $studentGroup = StudentGroup::where('section_id', $student->section_id)
            ->where('direction_id', $student->direction_id)->first();

            StudentStudentGroup::create(['student_group_id'=>$studentGroup->id, 'student_id'=>$student->id]);
        }

        return $user;
    }

    public function enroll(array $data)
    {
        $student = new Student();
        $student->section_id = Direction::find($data['direction_id'])->section_id;
        $student->country_id = $data['country_id'];
        $student->direction_id = $data['direction_id'];
        $student->intake_period_id = $data['intake_period_id'];
        $student->entry_mode_id = $data['entry_mode_id'];
        $student->level_of_adm = $data['level_of_adm'];
        $student->level_id = $data['level_id'];
        $student->session_id = $data['session_id'];
        $student->applicant_id = $data['applicant_id'];
        $student->marital_status_id = $data['marital_status_id'];
        $student->no_of_children = $data['no_of_children'];
        $student->order = $data['order'];
        $student->graduation_year_id = $data['graduation_year_id'];
        $student->company_year_id = session('current_company_year');
        $student->semester_id = session('current_company_semester');
        $student->company_id = session('current_company');
        $student->campus_id = $data['campus_id'];
        $student->religion_id = $data['religion_id'];
        $student->denomination = $data['denomination'];
        $student->disability = $data['disability'];
        $student->contact_relation = $data['contact_relation'];
        $student->contact_name = $data['contact_name'];
        $student->contact_address = $data['contact_address'];
        $student->contact_phone = $data['contact_phone'];
        $student->contact_email = $data['contact_email'];
        $student->user_id = $data['user_id'];
        $student->save();

        $school = Company::find(session('current_company'));
        $yearCode = CompanyYear::find(session('current_company_year'));
        $departmentCode = Department::find($data['section_id']);
        $programmeCode = Direction::find($data['direction_id']);

        /*Check for Duplicate ID*/

        $duplicateID = Student::where('sID', $departmentCode->id_code.'/'.$programmeCode->id_code.'/'.$yearCode->id_code.str_pad($school->next_id_no, 3, '0', STR_PAD_LEFT))->first();

        if (is_null($duplicateID)) {
            $student->student_no = $school->student_card_prefix.$student->id;
            $student->sID = $departmentCode->id_code.'/'.$programmeCode->id_code.'/'.$yearCode->id_code.str_pad($school->next_id_no, 3, '0', STR_PAD_LEFT);
        }

        if ($data['country_id'] == '1') {
            $student->currency_id = 1;
        } elseif ($data['country_id'] > 1 && $data['section_id'] == '8') { /*foreing DTS Students*/
            $student->currency_id = 1;
        } elseif ($data['country_id'] > 1 && $data['section_id'] == '6') { /*foreing Theology Students*/
            $student->currency_id = 1;
        } else {
            $student->currency_id = 2;
        }

        $student->save();

        StudentAdmission::firstOrCreate(['company_id' => session('current_company'), 'company_year_id' => session('current_company_year'), 'semester_id' => session('current_company_semester'), 'student_id' => $student->id]);

        $school2 = Company::find(session('current_company'));
        $school2->next_id_no = $school2->next_id_no + $school2->id_interval;
        $school2->save();

        /*$fees = FeeCategory::all()->whereIn('section_id', array($data['section_id'], 7))
            ->where('company_id','=', session('current_company'))
            ->where('currency_id','=', $student->currency_id);*/

        $gs = Department::where('company_id', '=', session('current_company'))
            ->where('title', '=', 'General')
            ->get()->first();

        /*  if ($data['section_id'] == '8') { /*   for DTS Students  */
        /*
            $fees = FeeCategory::all()->whereIn('id', [22, 24, 25, 32, 54, 55])
            ->where('company_id', '=', session('current_company'))
            ->where('currency_id', '=', $student->currency_id);
        } else {
            $fees = FeeCategory::all()->whereIn('section_id', [$data['section_id'], $gs->id])
                ->where('company_id', '=', session('current_company'))
                ->where('currency_id', '=', $student->currency_id);
        }*/

        $fees = FeeCategory::all()->where('section_id', $data['section_id'])
            ->where('company_id', '=', session('current_company'));

        $invoice = new Invoice();
        $invoice->student_id = $student->id;
        $invoice->user_id = $student->user_id;
        $invoice->company_id = session('current_company');
        $invoice->company_year_id = session('current_company_year');
        $invoice->semester_id = session('current_company_semester');
        $invoice->currency_id = $student->currency_id;
        $invoice->total_fees = $fees->sum('amount');
        $invoice->amount = $fees->sum('amount');
        $invoice->save();

        foreach ($fees as $fee) {
            $feesStatus = new FeesStatus();
            $feesStatus->invoice_id = $invoice->id;
            $feesStatus->user_id = $student->user_id;
            $feesStatus->student_id = $student->id;
            $feesStatus->company_id = session('current_company');
            $feesStatus->company_year_id = session('current_company_year');
            $feesStatus->semester_id = session('current_company_semester');
            $feesStatus->title = $fee->title;
            $feesStatus->currency_id = $student->currency_id;
            $feesStatus->amount = $fee->amount;
            $feesStatus->fee_category_id = $fee->id;
            $feesStatus->save();

            $generalLedger = new GeneralLedger();
            $generalLedger->student_id = $student->id;
            $generalLedger->user_id = $student->user_id;
            $generalLedger->company_id = session('current_company');
            $generalLedger->company_year_id = session('current_company_year');
            $generalLedger->semester_id = session('current_company_semester');
            $generalLedger->narration = $fee->title;
            $generalLedger->account_id = $fee->credit_account_id;
            if ($student->country_id == 1) {
                $generalLedger->credit = $fee->local_amount;
            } else {
                $generalLedger->credit = $fee->foreign_amount;
            }
            $generalLedger->fee_category_id = $fee->id;
            $generalLedger->transaction_date = now();
            $generalLedger->transaction_type = 'credit';
            $generalLedger->save();
        }

        return $student;
    }

    public function getAllForSection($section_id)
    {
        $studentitems = new Collection([]);
        $this->model->Has('alumni', '=', 0)
            ->with('user')
            ->orderBy('order')
            ->get()
            ->each(function ($studentItem) use ($studentitems, $section_id) {
                if ($studentItem->section_id == $section_id && isset($studentItem->user)) {
                    $studentitems->push($studentItem);
                }
            });

        return $studentitems;
    }

    public function getAllForSection2($section_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->where('section_id', $section_id);
    }

    public function getAllForSectionCurrency($section_id, $currency_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->where('section_id', $section_id)
            ->where('currency_id', $currency_id);
    }

    public function getAllForDirection($direction_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->where('direction_id', $direction_id);
    }

    public function getAllForDirectionCurrency($direction_id, $currency_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->where('direction_id', $direction_id)
            ->where('currency_id', $currency_id);
    }

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids)
    {
        $studentItems = new Collection([]);
        $this->model->Has('alumni', '=', 0)
                    ->with('user', 'section')
                    ->orderBy('order')
                    ->get()
                    ->each(function ($studentItem) use ($studentItems, $student_user_ids, $company_year_id) {
                        if (in_array($studentItem->user_id, $student_user_ids) &&
                            $studentItem->company_year_id == $company_year_id) {
                            $studentItems->push($studentItem);
                        }
                    });

        return $studentItems;
    }

    public function getCountStudentsForSchoolAndSchoolYear($company_id, $schoolYearId)
    {
        return $this->model->Has('alumni', '=', 0)
                           ->where('company_id', $company_id)
                           ->where('company_year_id', $schoolYearId)
                           ->count();
    }
}
