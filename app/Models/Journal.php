<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Journal extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];




    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function academicyear()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

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
}
