<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyFramework extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }


    public function competencyMatrix()
    {
        return $this->belongsTo(CompetencyMatrix::class);
    }


    public function position()
    {
        return $this->belongsTo(Position::class);
    }

}
