<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntakePeriod extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'intake_periods';

    public function total()
    {
        return $this->hasMany(Student::class, 'intake_period_id');
    }

    public function adminIntakeDates()
    {
        return $this->hasMany(AdmIntakeDate::class, 'intake_period_id');
    }
}
