<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualificationFramework extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function section()
    {
        return $this->belongsTo(Department::class);
    }


    public function position()
    {
        return $this->belongsTo(Position::class);
    }


    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }


}
