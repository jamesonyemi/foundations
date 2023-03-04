<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class StudentStatus extends Model
{
    /*use SoftDeletes;*/
    protected $guarded = ['id'];
    protected $fillable = ['employee_id','confirm','attended','attended_date',
        'company_id','company_year_id', 'center_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }
}
