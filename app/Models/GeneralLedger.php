<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class GeneralLedger extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    protected $table = 'general_ledger';


   /* public function getAmountAttribute($value) {
        return number_format($value,2);
    }*/


    public function getAmountDisplayAttribute()
    {
        $val = $this->amount;
        if ($val < 0){
            $val = $val * -1;
            $val = number_format($val,2);


            return $val;
        }

        else {
            return number_format($val,2);
        }


    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function student()
    {
        return $this->belongsTo(Employee::class, 'student_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Vendor::class, 'invoice_id');
    }
}
