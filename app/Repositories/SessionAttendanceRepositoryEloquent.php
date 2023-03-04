<?php

namespace App\Repositories;

use App\Models\ConferenceSession;
use App\Models\SessionAttendance;
use Illuminate\Support\Facades\DB;

class SessionAttendanceRepositoryEloquent implements SessionAttendanceRepository
{
    /**
     * @var SessionAttendance
     */
    private $model;

    /**
     * RegistrationRepositoryEloquent constructor.
     *
     * @param SessionAttendance $model
     */
    public function __construct(SessionAttendance $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->join('employees', 'employees.id', 'session_attendances.employee_id')
                           ->join('users', 'users.id', 'registrations.user_id')
                           ->join('company_years', 'company_years.id', 'registrations.company_year_id')
                           ->join('subjects', 'subjects.id', 'registrations.subject_id')
                           ->select('registrations.id', 'employees.sID',
                               DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                               'semesters.title as semester', 'company_years.title as company_year', 'subjects.title as subject',
                               'registrations.created_at');
    }

    public function getAllStudentsForSchool($center_id, $company_year_id)
    {
        $activeSession = ConferenceSession::where('active', 'Yes')
            ->where('company_year_id', $company_year_id)->first();

        return $this->model->join('employees', 'employees.id', 'session_attendances.employee_id')
                           ->join('users', 'users.id', 'employees.user_id')
                           ->join('company_years', 'company_years.id', 'session_attendances.company_year_id')
                           ->where('session_attendances.company_year_id', $company_year_id)
                           ->where('session_attendances.conference_session_id', $activeSession->id)
                           ->select('employees.id', 'employees.sID',
                               DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                                'company_years.title as company_year', 'users.gender as gender', 'session_attendances.id as session_attendance_id', 'session_attendances.created_at');
    }

    public function getAllStudentsForSchoolSession($company_id, $company_year_id, $session_id)
    {
        return $this->model->join('employees', 'employees.id', 'session_attendances.employee_id')
            ->join('users', 'users.id', 'employees.user_id')
            ->join('positions', 'positions.id', 'employees.position_id')
            ->join('company_years', 'company_years.id', 'session_attendances.company_year_id')
            ->where('session_attendances.company_year_id', $company_year_id)
            ->where('session_attendances.conference_session_id', $session_id)
            ->select('employees.id', 'employees.sID',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'company_years.title as company_year', 'positions.name as level', 'users.gender as gender', 'session_attendances.created_at');
    }

    public function getAllStudentsForSchoolSessionMale($company_id, $company_year_id, $session_id)
    {
        return $this->model->join('employees', 'employees.id', 'session_attendances.employee_id')
            ->join('users', 'users.id', 'employees.user_id')
            ->join('positions', 'positions.id', 'employees.position_id')
            ->join('company_years', 'company_years.id', 'session_attendances.company_year_id')
            ->where('session_attendances.company_year_id', $company_year_id)
            ->where('session_attendances.conference_session_id', $session_id)
            ->where('users.gender', 1)
            ->select('employees.id', 'employees.sID',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'company_years.title as company_year', 'positions.name as level', 'users.gender as gender', 'session_attendances.created_at');
    }

    public function getAllStudentsForSchoolSessionFemale($company_id, $company_year_id, $session_id)
    {
        return $this->model->join('employees', 'employees.id', 'session_attendances.employee_id')
            ->join('users', 'users.id', 'employees.user_id')
            ->join('positions', 'positions.id', 'employees.position_id')
            ->join('company_years', 'company_years.id', 'session_attendances.company_year_id')
            ->where('session_attendances.company_year_id', $company_year_id)
            ->where('session_attendances.conference_session_id', $session_id)
            ->where('users.gender', 0)
            ->select('employees.id', 'employees.sID',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'company_years.title as company_year', 'positions.name as level',
                'users.gender as gender', 'session_attendances.created_at');
    }

    public function getAllConfirmForSchool($company_id)
    {
        return $this->model->join('employees', 'employees.id', 'registrations.employee_id')
            ->join('users', 'users.id', 'registrations.user_id')
            ->join('company_years', 'company_years.id', 'registrations.company_year_id')
            ->select('registrations.id', 'employees.sID',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'company_years.title as company_year', 'users.gender as gender', 'registrations.created_at');
    }
}
