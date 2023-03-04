<?php

namespace App\Repositories;

use function App\Helpers\randomString;
use App\Helpers\Settings;
use App\Models\Employee;
use App\Models\StudentStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sentinel;
use Session;

class EmployeeRepositoryEloquent implements EmployeeRepository
{
    /**
     * @var Employee
     */
    private $model;

    /**
     * EmployeeRepositoryEloquent constructor.
     * @param Employee $model
     */
    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    /*
        public function getAll()
        {
            return $this->model->select('id','sID')->with(['user' => function ($query) {
                $query->select('full_name');
            }]);
        }*/
    public function getAll()
    {
        return $this->model;
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

    public function getAllMaleConfirm()
    {
        return $this->model->whereHas('registration')
            ->whereHas('active', function ($q) {
                $q->where('employees.company_id', session('current_company'))
                    ->where('student_statuses.school_year_id', session('current_company_year'));
            })
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
                $query->where('employees.confirm', '=', '1');
            });
    }

    public function getAllFeMaleConfirm()
    {
        return $this->model->whereHas('registration')
            ->whereHas('active', function ($q) {
                $q->where('employees.company_id', session('current_company'))
                    ->where('student_statuses.school_year_id', session('current_company_year'));
            })
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
                $query->where('employees.confirm', '=', '1');
            });
    }

    public function getAllMalePresent()
    {
        return $this->model->whereHas('active', function ($q) {
            $q->where('employees.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', session('current_company_year'))
                ->where('student_statuses.attended', 1);
        })
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            });
    }

    public function getAllFeMalePresent()
    {
        return $this->model->whereHas('active', function ($q) {
            $q->where('employees.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', session('current_company_year'))
                ->where('student_statuses.attended', 1);
        })
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            });
    }

    public function getAllForSchoolYearAndSection3($school_year_id, $section_id)
    {
        return $this->model->whereHas('active', function ($q) use ($school_year_id) {
            $q->where('employees.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', $school_year_id);
        })
            ->where('section_id', $section_id);
    }

    public function getAllForSchoolConfirm($school_year_id, $company_id)
    {
        return $this->model->join('registrations', 'registrations.student_id', 'employees.id')
            ->join('users', 'users.id', 'registrations.user_id')
            ->join('departments', 'departments.id', 'employees.section_id')
            ->join('positions', 'positions.id', 'employees.position_id')
            ->join('school_years', 'school_years.id', 'registrations.school_year_id')
            ->join('dormitories', 'dormitories.id', 'registrations.dormitory_id')
            ->join('dormitory_rooms', 'dormitory_rooms.id', 'registrations.dormitory_room_id')
            ->whereHas('active', function ($q) {
                $q->where('employees.company_id', session('current_company'))
                    ->where('student_statuses.school_year_id', session('current_company_year'))
                    ->where('student_statuses.confirm', 1);
            })
            ->whereNull('employees.deleted_at')
            ->whereNull('registrations.deleted_at')
            ->select('registrations.id', 'employees.student_no', 'employees.confirm_date as ddate',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'dormitories.title as dormitory', 'school_years.title as school_year', 'dormitory_rooms.title as dormitoryRoom',
                'sections.title as section', 'levels.name as level', 'users.gender as gender', 'registrations.created_at');
    }

    public function getAllForSchoolYearAndLevel($school_year_id, $position_id)
    {
        return $this->model->whereHas('active', function ($q) use ($school_year_id) {
            $q->where('employees.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', $school_year_id);
        })
            ->where('position_id', $position_id);
    }

    public function getAllForSchoolYearAndCommittee($school_year_id, $committee_id)
    {
        return $this->model->whereHas('active', function ($q) use ($school_year_id) {
            $q->where('employees.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', $school_year_id);
        })
            ->where('committee_id', $committee_id);
    }

    public function getAllForSchoolYearAndSection2($school_year_id, $section_id)
    {
        return $this->model->Has('registration', '=', 0)
            ->whereHas('active', function ($q) use ($school_year_id) {
                $q->where('student_statuses.school_year_id', $school_year_id);
            })
            ->where('company_id', $section_id);
    }

    public function getAllForCommitee($school_year_id, $company_id)
    {
        return $this->model->whereHas('committee')
            ->whereHas('active', function ($q) {
                $q->where('employees.company_id', session('current_company'))
                    ->where('student_statuses.school_year_id', session('current_company_year'));
            });
    }

    public function getAllForSchoolWithFilter($company_id, $school_year_id, $request = null)
    {
        $studentItems = new Collection([]);
        $this->model->whereHas('active', function ($q) use ($school_year_id, $company_id) {
            $q->where('employees.company_id', $company_id)
                ->where('student_statuses.school_year_id', $school_year_id);
        })
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.section_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_id', $company_id)
            ->where(function ($w) use ($request) {
                if (! is_null($request['first_name']) && $request['first_name'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['first_name'].'%');
                }
                if (! is_null($request['last_name']) && $request['last_name'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['last_name'].'%');
                }
                if (! is_null($request['student_no']) && $request['student_no'] != '*') {
                    $w->where('employees.student_no', 'LIKE', '%'.$request['student_no'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('positions.id', $request['position_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('order')
            ->select('users.id as user_id', 'employees.id as id', 'employees.student_no as student_no', 'employees.order as order',
                DB::raw('CONCAT(COALESCE(users.title, " " ), users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender', 'sections.title as section', 'levels.name as level', 'users.email as email')
            ->get()
            ->each(function ($studentItem) use ($studentItems) {
                $studentItems->push($studentItem);
            });

        return $studentItems;
    }

    public function getAllForSchoolWithFilterPresent($company_id, $school_year_id, $request = null)
    {
        $studentItems = new Collection([]);
        $this->model->whereHas('active', function ($q) use ($school_year_id, $company_id) {
            $q->where('employees.company_id', $company_id)
                ->where('student_statuses.school_year_id', $school_year_id)
                ->where('student_statuses.attended', 1);
        })
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.section_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_id', $company_id)
            /* ->where(function ($w) use ($request){
                 if ( ! is_null( $request['first_name'] ) && $request['first_name'] != "*" ) {
                     $w->where( 'users.first_name', 'LIKE', '%' . $request['first_name'] . '%' );
                 }
                 if ( ! is_null( $request['last_name'] ) &&  $request['last_name'] != "*" ) {
                     $w->where( 'users.last_name', 'LIKE', '%' . $request['last_name'] . '%' );
                 }
                 if ( ! is_null( $request['student_no'] ) &&  $request['student_no'] != "*" ) {
                     $w->where( 'employees.student_no', 'LIKE', '%' . $request['student_no'] . '%' );
                 }

                 if ( ! is_null( $request['position_id'] ) && $request['position_id'] != "*" && $request['position_id'] != "null" ) {
                     $w->where( 'positions.id', $request['position_id']);
                 }

                 if ( ! is_null( $request['gender'] ) && $request['gender'] != "*") {
                     $w->where( 'users.gender', $request['gender']);
                 }

             })*/->orderBy('order')
            ->select('users.id as user_id', 'employees.id as id', 'employees.student_no as student_no', 'employees.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender', 'sections.title as section', 'levels.name as level', 'users.email as email')
            ->get()
            ->each(function ($studentItem) use ($studentItems) {
                $studentItems->push($studentItem);
            });

        return $studentItems;
    }

    public function getAllForSessionAbsent($company_id, $school_year_id, $session_id)
    {
        return $this->model->has('sessionAttendance', '=', 0)
            ->whereHas('active', function ($q) use ($school_year_id) {
                $q->where('student_statuses.school_year_id', $school_year_id);
            })
            ->where('company_id', $company_id);
    }

    public function getAllForEmployeeSubordinates($company_id, $employee_id)
    {
        return $this->model->where('id', '!=', session('current_employee'))->whereHas('yearKpis', function ($q) use ($company_id, $employee_id) {
            $q->where('kpi_responsibilities.supervisor_employee_id', $employee_id);
        });
    }

    public function getAllForSchoolYearAndSection($company_year_id, $company_id, $department_id)
    {
        return $this->model->whereHas('user', function ($q) use ($company_year_id, $company_id, $department_id) {
            $q->where('employees.department_id', $department_id)
                    ->where('employees.status', 1)
                ->where('employees.company_id', $company_id);
        });

        /* return $this->model->where('company_year_id', $company_year_id)
             ->where('department_id', $department_id);*/
    }

    public function getAllForSchoolYearAndDirection($company_year_id, $company_id, $direction_id)
    {
        return $this->model->whereHas('active', function ($q) use ($company_year_id, $company_id, $direction_id) {
            $q->where('employees.direction_id', $direction_id)
                    ->where('employees.company_id', $company_id)
                    ->where('employees.company_year_id', $company_year_id);
        });
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id)->with('user');
    }

    public function getAllActive($company_year_id, $semester_id, $company_id)
    {
        return $this->model->whereHas('user')
            ->where('company_id', '=', session('current_company'))
            ->whereNull('employees.deleted_at')
            ->groupby('employees.id')
            ->orderBy('employees.id', 'Desc');
    }

    public function getAllForPayroll($company_id)
    {
        return $this->model->whereHas('user')
            ->where('company_id', $company_id)
            ->where('status', 1)
            /*->whereDate('contract_end_date', '>', Carbon::today())*/
            ->whereNull('employees.deleted_at');
    }

    public function getAllCompanyActive($company_id)
    {
        return $this->model->whereHas('user')
            ->where('company_id', $company_id)
            ->where('status', 1)
            /*->whereDate('contract_end_date', '>', Carbon::today())*/
            ->whereNull('employees.deleted_at');
    }

    public function getAllCompanyInActive($company_id)
    {
        return $this->model->whereHas('user')
            ->where('company_id', $company_id)
            ->whereNot('status', 1)
            /*->whereDate('contract_end_date', '>', Carbon::today())*/
            ->whereNull('employees.deleted_at');
    }

    public function getAllActiveForImprovement($company_year_id, $company_id)
    {
        return $this->model->whereHas('performance_improvements', function ($q) use ($company_year_id, $company_id) {
            $q->where('employees.company_id', $company_id)
                ->where('performance_improvements.company_year_id', $company_year_id);
        });
    }

    public function getAllPendingApproval($company_year_id, $semester_id, $company_id)
    {
        return $this->model->where('status', '=', 'pending')
            ->where('company_id', '=', session('current_company'))
            ->whereNull('employees.deleted_at')
            ->groupby('employees.id')
            ->orderBy('employees.id', 'Desc');
    }

    public function getAllActiveExport_($request)
    {
        return $this->model->whereHas('active')
            ->where('employees.company_id', '=', session('current_company'))
            ->where('employees.department_id', '=', $request->department_id);
    }

    public function getAllActiveExport($request)
    {
        $query = Student::query()->whereHas('active')
            ->where('employees.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query = $query->where('department_id', '=', $request->department_id);
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

        if (isset($request->position_id) && ! empty($request->position_id)) {
            $query = $query->where('position_id', '=', $request->position_id);
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
            $query = $query->where('employees.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllExport($request)
    {
        $query = Student::query()->where('employees.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query = $query->where('department_id', '=', $request->department_id);
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

        if (isset($request->position_id) && ! empty($request->position_id)) {
            $query = $query->where('position_id', '=', $request->position_id);
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
            $query = $query->where('employees.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllFilter($request)
    {
        $studentItems = $this->model
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_id', session('current_company'))
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
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '0') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
        $studentItems = $this->model
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_id', session('current_company'))
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
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
        return $this->model
            ->whereHas('deferred', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_deferrals.company_year_id', $company_year_id)
                ->where('employees.company_id', $company_id)
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
                    ->where('employees.company_id', $company_id)
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
                    ->where('employees.company_id', $company_id)
                    ->where('student_deferrals.semester_id', $semester_id);
            });
    }

    public function getAllDeferredFilter($request)
    {
        $studentItems = $this->model->whereHas('deferred')
            ->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->join('student_deferrals', 'student_deferrals.student_id', '=', 'employees.id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_year_id', '=', session('current_company_year'))
            ->where('employees.company_id', '=', session('current_company'))
            ->whereHas('programme')
            ->where('student_deferrals.company_year_id', '=', session('current_company_year'))
            ->where('student_deferrals.semester_id', '=', session('current_company_semester'))
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
            ->where('employees.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query = $query->where('department_id', '=', $request->department_id);
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

        if (isset($request->position_id) && ! empty($request->position_id)) {
            $query = $query->where('position_id', '=', $request->position_id);
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
            $query = $query->where('employees.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllDrop($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('drop', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('student_drops.company_year_id', $company_year_id)
                ->where('employees.company_id', $company_id)
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
                    ->where('employees.company_id', $company_id)
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
                    ->where('employees.company_id', $company_id)
                    ->where('student_drops.semester_id', $semester_id);
            });
    }

    public function getAllDropFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->whereHas('drop')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->join('student_drops', 'student_drops.student_id', '=', 'employees.id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_year_id', '=', session('current_company_year'))
            ->where('employees.company_id', '=', session('current_company'))
            ->whereHas('programme')
            ->where('student_drops.company_year_id', '=', session('current_company_year'))
            ->where('student_drops.semester_id', '=', session('current_company_semester'))
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
                ->where('employees.company_id', $company_id)
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
                    ->where('employees.company_id', $company_id)
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
                    ->where('employees.company_id', $company_id)
                    ->where('student_graduations.semester_id', $semester_id);
            });
    }

    public function getAllGraduatingFilter($request)
    {
        $studentItems = $this->model->whereHas('graduation')
            ->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->join('student_graduations', 'student_graduations.student_id', '=', 'employees.id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_year_id', '=', session('current_company_year'))
            ->where('employees.company_id', '=', session('current_company'))
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
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
                $q->where('employees.company_id', $company_id);
            });
    }

    public function getAllAlumniExport_($request)
    {
        return $this->model->Has('active', '=', 0)
            ->whereHas('alumni')
            ->where('employees.company_id', '=', session('current_company'));
    }

    public function getAllAlumniExport($request)
    {
        $query = Student::query()->Has('active', '=', 0)
            ->whereHas('alumni')
            ->where('employees.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query = $query->where('department_id', '=', $request->department_id);
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

        if (isset($request->position_id) && ! empty($request->position_id)) {
            $query = $query->where('position_id', '=', $request->position_id);
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
            $query = $query->where('employees.intake_period_id', '=', $request->intake_period_id);
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
                $q->where('employees.company_id', $company_id);
            });
    }

    public function getAllAlumniFemale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('active', '=', 0)
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->whereHas('alumni', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('employees.company_id', $company_id);
            });
    }

    public function getAllAlumniFilter($request)
    {
        $studentItems = $this->model->whereHas('alumni')
            ->Has('active', '=', 0)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_id', '=', session('current_company'))
            ->whereHas('programme')
            ->where(function ($w) use ($request) {
                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id']) && $request['country_id'] != '*') {
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
        return $this->model->Has('alumni', '=', 0)
           ->where('company_id', $company_id)
           ->where('company_year_id', $company_year_id)
           ->where('semester_id', $semester_id)
           ->with('user');
    }

    public function getAllAdmittedForSchoolMale($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
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
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('employees.company_year_id', '=', session('current_company_year'))
            ->where('employees.semester_id', '=', session('current_company_semester'))
            ->where('employees.company_id', '=', session('current_company'))
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
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('employees.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('employees.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'users.email as email'
            );

        return $studentItems;
    }

    public function getAllAdmittedForSchoolExport_($request)
    {
        return $this->model
            ->where('employees.company_year_id', '=', session('current_company_year'))
            ->where('employees.semester_id', '=', session('current_company_semester'))
            ->where('employees.company_id', '=', session('current_company'));
    }

    public function getAllAdmittedForSchoolExport($request)
    {
        $query = Student::query()->Has('alumni', '=', 0)
            ->where('employees.company_year_id', '=', session('current_company_year'))
            ->where('employees.semester_id', '=', session('current_company_semester'))
            ->where('employees.company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query = $query->where('department_id', '=', $request->department_id);
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

        if (isset($request->position_id) && ! empty($request->position_id)) {
            $query = $query->where('position_id', '=', $request->position_id);
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
            $query = $query->where('employees.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
    }

    public function getAllRegistration($company_year_id, $semester_id, $company_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->whereHas('registration', function ($q) use ($company_year_id, $semester_id, $company_id) {
                $q->where('registrations.company_year_id', $company_year_id)
                ->where('employees.company_id', $company_id)
                ->where('registrations.semester_id', $semester_id);
            });
    }

    public function getAllRegistrationFilter($request)
    {
        $studentItems = $this->model->Has('alumni', '=', 0)
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('directions', 'directions.id', '=', 'employees.direction_id')
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
                    $w->where('employees.country_id', $request['country_id']);
                }

                if (! is_null($request['department_id']) && $request['department_id'] != '*') {
                    $w->where('employees.department_id', $request['department_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('employees.direction_id', $request['direction_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('employees.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['id']) && $request['id'] != '*') {
                    $w->where('employees.sID', 'LIKE', '%'.$request['id'].'%');
                }

                if (! is_null($request['position_id']) && $request['position_id'] != '*' && $request['position_id'] != 'null') {
                    $w->where('employees.position_id', $request['position_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('employees.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('employees.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('employees.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('employees.id')
            ->select(
                'users.id as user_id',
                'employees.id as student_id',
                'employees.student_no as student_no',
                'employees.sID as sID',
                'employees.order as order',
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
        $query = Student::query()->Has('alumni', '=', 0)
            ->whereHas('registration', function ($q) use ($request) {
                $q->where('registrations.company_year_id', session('current_company_year'))
                    ->where('employees.company_id', session('current_company'))
                    ->where(function ($w) use ($request) {
                        if (! is_null($request['subject_id']) && $request['subject_id'] != '*') {
                            $w->where('registrations.subject_id', $request['subject_id']);
                        }
                    })
                    ->where('registrations.semester_id', session('current_company_semester'));
            })
            ->join('users', 'users.id', '=', 'employees.user_id');

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('country_id', '=', $request->country_id);
        }

        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query = $query->where('department_id', '=', $request->department_id);
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

        if (isset($request->position_id) && ! empty($request->position_id)) {
            $query = $query->where('position_id', '=', $request->position_id);
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
            $query = $query->where('employees.intake_period_id', '=', $request->intake_period_id);
        }

        return $query;
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

    public function getAllForSchoolYearAndSchoolAll($school_year_id)
    {
        return $this->model->whereHas('active');
    }

    public function getAllForSchoolYearAndSchool($school_year_id, $center_id)
    {
        return $this->model->whereHas('active', function ($q) use ($school_year_id, $center_id) {
            $q->where('student_statuses.center_id', $center_id);
        });
    }

    public function getAllSessionAttendance($school_year_id, $center_id, $session_id)
    {
        return $this->model->whereHas('sessionAttendance', function ($q) use ($school_year_id, $center_id, $session_id) {
            $q->where('session_attendances.conference_session_id', $session_id)
                ->where('session_attendances.center_id', $center_id);
        });
    }

    public function getAllSessionAttendanceExport($center_id, $session_id)
    {
        return $this->model->whereHas('sessionAttendance', function ($q) use ($center_id, $session_id) {
            $q->where('session_attendances.conference_session_id', $session_id)
                ->where('session_attendances.center_id', $center_id);
        });
    }

    public function getAllSessionAttendanceAll($school_year_id, $session_id)
    {
        return $this->model->whereHas('sessionAttendance', function ($q) use ($school_year_id, $session_id) {
            $q->where('session_attendances.conference_session_id', $session_id);
        });
    }

    /*public function getAllSessionAttendance( $school_year_id, $center_id, $session_id) {
        return $this->model->whereHas('sessionAttendance', function ($q) use ($school_year_id, $center_id, $session_id){
            $q->where('session_attendances.conference_session_id', $session_id);
        })->whereHas('active', function ($q) use ($school_year_id, $center_id){
            $q->where('student_statuses.center_id', $center_id);
        });
    }*/

    public function getAllForSchool($company_id)
    {
        return $this->model->where('company_id', $company_id);
    }

    public function getAllForKpiResponsibilities($company_id)
    {
        return $this->model->where('company_id', $company_id)->where('id', '!=', session('current_employee'));
    }

    public function getAllForSchoolAndGlobal($company_id)
    {
        return $this->model->where('company_id', $company_id)->orWhere('global', 1);
    }

    public function getAllForSchoolChat($company_id)
    {
        return $this->model->where('company_id', $company_id)
            ->where('id', '!=', session('current_employee'));
    }

    public function getAllForSchoolYearSchoolAndSection($company_year_id, $company_id, $department_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->Has('drop', '=', 0)
            ->Has('deferred', '=', 0)
            ->where('company_year_id', $company_year_id)
            ->where('company_id', $company_id)
            ->where('department_id', $department_id);
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
                    ->where('employees.company_id', session('current_company'))
                    ->where('registrations.semester_id', session('current_company_semester'))
                    ->whereIn('registrations.subject_id', $subject_ids);
            });
    }

    public function create(array $data, $activate = true)
    {
        $user_tem = Sentinel::registerAndActivate($data, $activate);
        $user = User::find($user_tem->id);

        try {
            $role = Sentinel::findRoleBySlug('employee');
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
        // Available alpha caracters
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // generate a pin based on 2 * 7 digits + a random character
        $pin = mt_rand(100000000, 999999999)
            .mt_rand(100000000, 999999999)
            .$characters[rand(0, strlen($characters) - 1)];
        $employee = new Employee(collect($data)
            ->except(['title', 'email', 'email2', 'first_name', 'middle_name', 'last_name', 'address', 'address_line2', 'address_line3', 'mobile', 'mobile2', 'gender', 'birth_date', 'password', 'profile_avatar_remove'])
            ->toArray());
        $employee->user_id = $user->id;
        $employee->company_id = isset($data['company_id']) ? $data['company_id'] : session('current_company');
        $employee->sID = $pin;
        $employee->bank_id = 8;
        $employee->bank_branch_id = 1;
        $employee->position_id = 1;
        $employee->department_id = 2;
        $employee->sID = $pin;
        $employee->status = 1;
        $employee->save();
        StudentStatus::firstOrCreate([
            'company_id' => session('current_company'),
            'company_year_id' => session('current_company_year'),
            'center_id' => Employee::find(session('current_employee'))->center_id,
            'employee_id' => $employee->id, ]);

        return $user;
    }

    public function erpSync(array $data, $activate = true)
    {
        /*$user_exists = User::where('email', $data['email'])->first();*/
        $user_exists = Employee::where('sID', $data['sID'])->where('company_id', $data['company_id'])
            ->first();

        if (! isset($user_exists->id)) {
            $user_tem = Sentinel::registerAndActivate($data, $activate);
            $user = User::find($user_tem->id);
            try {
                $role = Sentinel::findRoleBySlug('employee');
                $role->users()->attach($user);
            } catch (\Exception $e) {
            }
        } else {
            $user = $user_exists->user;
        }

        $user->update(['birth_date'=>$data['birth_date'],
            'first_name'=>isset($data['first_name']) ? $data['first_name'] : ' ',
            'middle_name'=>isset($data['middle_name']) ? $data['middle_name'] : ' ',
            'birth_city'=>isset($data['birth_city']) ? $data['birth_city'] : '-',
            'gender' => isset($data['gender']) ? $data['gender'] : 0,
            'address' => isset($data['address']) ? $data['address'] : '-',
            'mobile' => isset($data['mobile']) ? $data['mobile'] : 0,
            'phone' => isset($data['phone']) ? $data['phone'] : 0,
            'email2' => isset($data['email2']) ? $data['email2'] : 0,
            'address_line2' => isset($data['address_line2']) ? $data['address_line2'] : 0, ]);

        // Available alpha caracters
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // generate a pin based on 2 * 7 digits + a random character
        $pin = mt_rand(1000000, 9999999)
            .mt_rand(1000000, 9999999)
            .$characters[rand(0, strlen($characters) - 1)];

        $data['user_id'] = $user->id;

        Employee::firstOrCreate(
            [
                'sID' => $data['email'],
                'company_id' => $data['company_id'],
                'user_id' => $user->id,
            ],

            [
                'social_security_number' => $data['social_security_number'],
                'basic_pay' => $data['basic_pay'],
                'job_title' => $data['job_title'],
                'salary_grade' => $data['salary_grade'],
                'bank_account_number' => $data['bank_account_number'],
                'join_date' => $data['join_date'],
                'contract_end_date' => $data['contract_end_date'],
                'tin_number' => $data['tin_number'],
                'bank_id' => $data['bank_id'],
                'bank_branch_id' => $data['bank_branch_id'],
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'status' => 1,
            ]
        );

        /*$employee = Employee::updateOrCreate (['sID' =>  $data['email']],collect(@$data)
            ->except(['title', 'email', 'email2', 'first_name', 'middle_name', 'last_name','address', 'address_line2', 'address_line3', 'mobile', 'phone', 'mobile2', 'gender', 'birth_date', 'password', 'profile_avatar_remove',])
            ->toArray());*/
    }

    public function getAllForSection($department_id)
    {
        $studentitems = new Collection([]);
        $this->model->Has('alumni', '=', 0)
            ->with('user')
            ->orderBy('order')
            ->get()
            ->each(function ($studentItem) use ($studentitems, $department_id) {
                if ($studentItem->department_id == $department_id && isset($studentItem->user)) {
                    $studentitems->push($studentItem);
                }
            });

        return $studentitems;
    }

    public function getAllForSection2($department_id)
    {
        return $this->model->whereHas('user')
            ->where('department_id', $department_id);
    }

    public function getAllForSectionCurrency($department_id, $currency_id)
    {
        return $this->model->Has('alumni', '=', 0)
            ->where('department_id', $department_id)
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
