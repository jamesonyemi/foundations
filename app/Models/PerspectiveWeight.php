<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerspectiveWeight extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function kpiTimeline()
    {
        return $this->belongsTo(BscPerspective::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(CompanyYear::class);
    }


}
