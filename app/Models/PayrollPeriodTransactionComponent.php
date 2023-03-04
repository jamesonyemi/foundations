<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriodTransactionComponent extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'payroll_period_transaction_id',
        'transaction_code',
        'transaction_type',
        'payment_mode',
        'transaction_name',
        'group_text',
        'amount',
        'tax_amount',
        'amount_fcy',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function payrollPeriodTransaction()
    {
        return $this->belongsTo(PayrollPeriodTransaction::class);
    }



}
