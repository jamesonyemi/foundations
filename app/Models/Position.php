<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('sector', function (Builder $builder) {

            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->where('positions.company_id', session('current_company'));;
                }
                /*else {
                    $builder->whereHas('employees', function ($q) {
                        $q->where('employees.company_id', session('current_company'));;
                    });

                }*/

            }

        });


    }



    public function employees()
    {
        return $this->hasMany(Employee::class);
    }


    public function competencies()
    {
        return $this->hasMany(Competency::class);
    }


    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function section()
    {
        return $this->belongsTo(Department::class);
    }


    public function kpis()
    {
        return $this->hasManyThrough(Kpi::class, Employee::class);
    }

/*
    public function competencyMatrix()
    {
        return $this->hasMany(CompetencyFramework::class);
    }*/

    public function competencyMatrix()
    {
        return $this->belongsToMany(CompetencyMatrix::class, 'competency_frameworks')->withPivot('department_id');
    }

    public function qualificationMatrix()
    {
        return $this->hasMany(QualificationFramework::class);
    }

    public function competencyFrameworks()
    {
        return $this->hasMany(CompetencyFramework::class);
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
