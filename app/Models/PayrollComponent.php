<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollComponent extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'company_id',
        'title',
        'code',
        'description',
        'balance_type',
        'transaction_type',
        'frequency',
        'calculate_from_basic_salary',
        'basic_salary_percentage',
        'employee_fixed_amount',
        'cash',
        'taxable',
        'tax_percentage',
        'formular',
        'interest_rate',
        'repayment_method',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function payrollPeriodTransactions()
    {
        return $this->hasMany(PayrollPeriodTransaction::class);
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

}
