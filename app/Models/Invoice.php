<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $dates = [ 'deleted_at' ];

    protected $guarded = array ( 'id' );

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function generalLedger()
    {
        return $this->hasMany(GeneralLedger::class);
    }

    public function getBalanceAttribute()
    {
        $bql = $this->items()->sum('amount');
        if ($bql < 0){
            $newbql = '('. $bql.')';

            return number_format($newbql, 2);
        }

        elseif ($bql > 0){
            $newbql = $bql;

            return number_format($newbql,2);
        }

        else {
            return number_format($bql, 2);
        }

    }

    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function fee_category()
    {
        return $this->belongsTo(FeeCategory::class);
    }



    public function student()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
