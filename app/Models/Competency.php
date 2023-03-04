<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competency extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function competency_type()
    {
        return $this->belongsTo(CompetencyType::class);
    }

    public function employeeCompetencyMatrix()
    {
        return $this->hasMany(EmployeeCompetencyMatrix::class, 'competency_id');
    }

    public function competency_framework()
    {
        return $this->hasManyThrough(CompetencyFramework::class, CompetencyMatrix::class,);
    }

    public function competency_matrix()
    {
        return $this->hasMany(CompetencyMatrix::class);
    }

 /*   public function position()
    {
        return $this->belongsTo(Position::class);
    }*/

    public function getFullTitleAttribute()
    { if (isset($this->competency_type->title))
    {
        return "{$this->competency_type->title} - {$this->title}";
    }
    else
        return "{$this->title}";
    }

}
