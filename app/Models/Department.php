<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Department extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'departments';


    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('year', function (Builder $builder) {
            @$school= Company::find(session('current_company'));;

            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->where('company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('company.sector', function ($query) use ($school) {
                        $query->where('sectors.group_id', $school->sector->group_id);
                    });


                    /*$builder->where('group_id', $school->sector->group_id);*/

                }

            }

        });


    }



    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    public function teacher()
    {
        return $this->belongsTo(User::class, 'section_teacher_id');
    }

    public function setSchoolYearIdAttribute($company_year_id)
    {
        $this->attributes['company_year_id'] = ($company_year_id != '') ? $company_year_id : session('current_company_year');
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class);
    }



    public function total()
    {
        return $this->hasMany(Student::class, 'department_id')->where('status', '=', 'active');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id')->where('status', '=', 1);
    }

    public function competencyLevels()
    {
        return $this->belongsToMany(CompetencyLevel::class, 'competency_frameworks');
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'competency_frameworks');
    }

    public function feeCategory()
    {
        return $this->hasMany(FeeCategory::class, 'department_id');
    }



    public function admission()
    {
        return $this->hasMany(Student::class, 'department_id')->where('status', '=', 'active')
            ->whereHas('admission', function ($query) {
                /*$query->whereStatus(1);*/
            })
            ->where('students.company_year_id', '=', session('current_company_year'))
            ->where('students.semester_id', '=', session('current_company_semester'));
    }


    public function kpis()
    {
        return $this->hasManyThrough(Kpi::class, Employee::class)
            ->where('kpis.company_id', '=', session('current_company'))
            ->where('kpis.company_year_id', session('current_company_year'))
            ->where('employees.status', '=', 1);
    }



    public function dailyAttendance()
    {
        return $this->hasManyThrough(DailyAttendance::class, Employee::class)
            ->where('employees.company_id', '=', session('current_company'))
            ->where('employees.status', '=', 1);
    }



    public function attendance()
    {
        return $this->hasManyThrough(Attendance::class, Employee::class)
            ->where('employees.company_id', '=', session('current_company'))
            ->where('employees.status', '=', 1);
    }



    public function dailyActivities()
    {
        return $this->hasManyThrough(DailyActivity::class, Employee::class)
            ->where('employees.company_id', '=', session('current_company'))
            ->where('employees.status', '=', 1);
    }



    public function getKpiActivitiesAttribute()
    {
        $kpiactivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->id);;
        })->get();

        return $kpiactivities;
    }


    public function getCompletedKpiActivitiesAttribute()
    {
        $kpiactivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->id);;
        })->get();

        return $kpiactivities;
    }


    public function getKpiScoreAttribute()
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', '=', $this->id);
        })->get();
            $data = @$review->average('score');
        return round($data);
    }

    public function admission_registrations()
    {
        return $this->hasMany(Student::class, 'department_id')->where('status', '=', 'active')->whereHas('registration', function ($q) {
            $q->where('registrations.company_year_id', session('current_company_year'))
                ->where('students.company_id', session('current_company'))
                ->where('students.semester_id', '=', session('current_company_semester'))
                ->where('registrations.semester_id', session('current_company_semester'));
        });
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'department_id')
            ->where('applicants.company_year_id', '=', session('current_company_year'));
    }



    public function getAllActive()
    {
        return $this->hasManyThrough(StudentStatus::class, Student::class, 'department_id', 'student_id')
            ->where('students.company_id', session('current_company'))
            ->where('student_statuses.semester_id', session('current_company_semester'));
    }


    public function getAllAlumni()
    {
        return $this->hasManyThrough(Alumnus::class, Student::class, 'department_id', 'student_id')
            ->where('students.company_id', session('current_company'));;
    }

    public function getAllDeferral()
    {
        return $this->hasManyThrough(StudentDeferral::class, Student::class, 'department_id', 'student_id')
            ->where('student_deferrals.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('student_deferrals.semester_id', session('current_company_semester'));
    }

    public function getAllDrop()
    {
        return $this->hasManyThrough(StudentDrop::class, Student::class, 'department_id', 'student_id')
            ->where('student_drops.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('student_drops.semester_id', session('current_company_semester'));
    }

    public function getAllGraduating()
    {
        return $this->hasManyThrough(StudentGraduation::class, Student::class, 'department_id', 'student_id')
            ->where('student_graduations.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('student_graduations.semester_id', session('current_company_semester'));
    }


    public function directions()
    {
        return $this->hasMany(Direction::class, 'department_id');
    }

    public function directions2()
    {
        return $this->hasMany(Direction::class, 'department_id')->whereNotIn('title',  ['General Programme','DTS General Programme','HND General Programme', 'ABE General Programme']);
    }


    public function registrations()
    {
        return $this->hasMany(Student::class, 'department_id')->where('status', '=', 'active')->whereHas('registration', function ($q) {
            $q->where('registrations.company_year_id', session('current_company_year'))
                ->where('students.company_id', session('current_company'))
                ->where('registrations.semester_id', session('current_company_semester'));
        });
    }




    public function studentsPaidAllFees()
    {
        return $this->hasManyThrough(Invoice::class, Student::class, 'department_id', 'student_id')
            ->where('invoices.company_id', '=', session('current_company'))
            ->where('invoices.company_year_id', '=', session('current_company_year'))
            ->where('invoices.semester_id', '=', session('current_company_semester'))
            ->where('invoices.amount', '<=', '0');
    }




    public function studentsPaidPartFees()
    {
        return $this->hasManyThrough(Invoice::class, Student::class, 'department_id', 'student_id')
            ->where('invoices.company_id', '=', session('current_company'))
            ->where('invoices.company_year_id', '=', session('current_company_year'))
            ->where('invoices.semester_id', '=', session('current_company_semester'))
            ->where('invoices.paid', '>', '0')
            ->where('invoices.amount', '>', '0');
    }

    public function studentsNotPaidFees()
    {
        return $this->hasManyThrough(Invoice::class, Student::class, 'department_id', 'student_id')
            ->where('invoices.company_id', '=', session('current_company'))
            ->where('invoices.company_year_id', '=', session('current_company_year'))
            ->where('invoices.semester_id', '=', session('current_company_semester'))
            ->where('invoices.paid', '=', '0')
            ->where('invoices.amount', '>', '0');
    }


    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }



    public function payrollTransactionEmployees()
    {
        return $this->hasMany(PayrollPeriodTransaction::class);
    }


    public function getPayrollTransactionEmployeeList($year, $month)
    {
        return $this->payrollTransactionEmployees()
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->get()->unique('employee_id');
    }

    public function payrollPeriodTransactions()
    {
        return $this->hasMany(PayrollPeriodTransaction::class);
    }
}
