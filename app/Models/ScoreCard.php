<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreCard extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    protected $guarded = ['id'];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }




}
