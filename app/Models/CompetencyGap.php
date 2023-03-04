<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyGap extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function learningGap()
    {
        return $this->hasMany(LearningGap::class);
    }


}
