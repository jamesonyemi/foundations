<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePayrollComponent extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'employee_id',
        'payroll_component_id',
        'amount',
        'balance_type',
        'amount',
        'transaction_type',
        'frequency',
        'cash',
        'taxable',
        'is_formular',
        'formular',
        'start_date',
        'end_date',
        'original_amount',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll_component()
    {
        return $this->belongsTo(PayrollComponent::class);
    }

}
