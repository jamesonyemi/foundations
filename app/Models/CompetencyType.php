<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyType extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];



    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function competencies()
    {
        return $this->hasMany(Competency::class);
    }

    public function competency_frameworks()
    {
        return $this->hasManyThrough(CompetencyLevel::class, Competency::class);
    }

}
