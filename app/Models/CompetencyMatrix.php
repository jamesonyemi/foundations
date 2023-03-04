<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyMatrix extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'competency_matrix';


    public function school()
    {
        return $this->belongsTo(Company::class);
    }


    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }


    public function competencyGrade()
    {
        return $this->belongsTo(CompetencyGrade::class);
    }

    public function getFullTitleAttribute()
    { if (isset($this->competency->title))
    {
        return "{$this->competency->title} - {$this->competencyGrade->title}";
    }
    else
        return "{$this->competencyGrade->title}";
    }


}
