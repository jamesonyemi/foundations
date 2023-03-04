<?php

namespace App\Repositories;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RegistrationRepositoryEloquent implements RegistrationRepository
{
    /**
     * @var Registration
     */
    private $model;

    /**
     * InvoiceRepositoryEloquent constructor.
     * @param Invoice $model
     */
    public function __construct(Registration $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->where('company_id', '=', session('current_company'))
                           ->where('company_year_id', '=', session('current_company_year'))
                           ->whereHas('student');
    }

    public function getAllStudentsForSchool($company_id, $year_id, $semester_id)
    {
        return $this->model->join('students', 'students.id', '=', 'registrations.student_id')
                           ->where('students.company_id', $company_id)
                           ->where('registrations.company_year_id', $year_id)
                           ->where('registrations.semester_id', $semester_id)
                           ->select('registrations.*');
    }

    public function getAllStudentsForSemesterSubject($company_id, $year_id, $semester_id, $subject_id)
    {
        return $this->model->join('students', 'students.id', '=', 'registrations.student_id')
            ->where('students.company_id', $company_id)
            ->where('registrations.company_year_id', $year_id)
            ->where('registrations.semester_id', $semester_id)
            ->where('registrations.subject_id', $subject_id)
            ->select('registrations.*');
    }

    public function getAllRegistrationFilter($request)
    {
        return $this->model->where('company_id', '=', session('current_company'))
            ->whereHas('student', function ($w) use ($request) {
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
                    $w->where('registrations.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['subject_id']) && $request['subject_id'] != '*') {
                    $w->where('registrations.subject_id', $request['subject_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('registrations.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('students.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != '*') {
                    $w->where('students.level_id', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != '*') {
                    $w->where('students.religion_id', $request['religion_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != '*') {
                    $w->where('students.session_id', $request['session_id']);
                }

                if (! is_null($request['intake_period_id']) && $request['intake_period_id'] != '' && $request['intake_period_id'] != '*') {
                    $w->where('students.intake_period_id', $request['intake_period_id']);
                }
            });
    }

    public function getAllForStudent($user_id)
    {
        return $this->model
            ->join('subjects', 'subjects.id', '=', 'registrations.subject_id')
            ->where('user_id', $user_id)
            ->where('company_year_id', session('current_company_year'))
            ->where('registrations.semester_id', session('current_company_semester'))
            ->select('registrations.*', 'subjects.credit_hours as credit');
    }
}
