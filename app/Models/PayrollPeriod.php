<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'company_year_id',
        'company_id',
        'employee_id',
        'status',
        'title',
        'period_year',
        'period_month',
    ];


    public function payrollPeriodTransactions()
    {
        return $this->hasMany(PayrollPeriodTransaction::class);
    }

}
