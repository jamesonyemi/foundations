<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineExamQuestion extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function online_exam()
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function answers()
    {
        return $this->hasMany(OnlineExamAnswer::class);
    }
}
