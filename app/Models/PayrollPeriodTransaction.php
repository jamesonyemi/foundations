<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriodTransaction extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'payroll_period_id',
        'company_year_id',
        'employee_id',
        'department_id',
        'department_name',
        'position_id',
        'position_name',
        'payroll_component_id',
        'employee_name',
        'bank_account_number',
        'social_security_number',
        'bank_id',
        'transaction_code',
        'transaction_type',
        'basic_pay',
        'payment_mode',
        'transaction_name',
        'group_text',
        'amount',
        'mobile_money_network',
        'mobile_money_number',
        'employee_pf_amount',
        'employer_pf_amount',
        'employee_ssf_amount',
        'employer_ssf_amount',
        'balance',
        'period_month',
        'period_year',
        'payroll_period',
        'gl_account',
        'salary_grade',
        'salary_notch',
        'currency',
        'amount_fcy',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function payrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class);
    }

    public function payrollPeriodTransactionComponents()
    {
        return $this->hasMany(PayrollPeriodTransactionComponent::class);
    }

}
