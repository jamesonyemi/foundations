<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function branches()
    {
        return $this->hasMany(BankBranch::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }


    public function payrollTransactionEmployees()
    {
        return $this->hasMany(PayrollPeriodTransaction::class)->where('payment_mode', 'Bank Transfer');
    }


    public function getPayrollTransactionEmployeeList($year, $month)
    {
        return $this->payrollTransactionEmployees()
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->get()->unique('employee_id');
    }
/*
    public function getPayrollTransactionEmployeeList($year, $month)
    {
        return $this->payrollTransactionEmployees()->whereHas('payrollPeriodTransactions', function ($query) use ($year, $month)  {
            $query->where('payroll_period_transactions.period_year', $year)
                ->where('payroll_period_transactions.period_month', $month);
        })->get();
    }*/

    public function payrollPeriodTransactions()
    {
        return $this->hasMany(PayrollPeriodTransaction::class)->where('payment_mode', 'Bank Transfer');
    }


}
