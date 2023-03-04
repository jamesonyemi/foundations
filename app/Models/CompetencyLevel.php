<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyLevel extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees()
    {
        return $this->hasMany(EmployeeCompetencyMatrix::class);
    }

    public function competencyMatrix()
    {
        return $this->hasMany(CompetencyMatrix::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class);
    }


    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    public function getFullTitleAttribute()
    { if (isset($this->competency->title))
    {
        return "{$this->competency->full_title} - {$this->title}";
    }
    else
        return "{$this->title}";
    }

    public function getFullTitle2Attribute()
    { if (isset($this->competency->title))
    {
        return "{$this->competency->title} - {$this->title}";
    }
    else
        return "{$this->title}";
    }

}
