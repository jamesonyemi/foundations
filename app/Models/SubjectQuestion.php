<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectQuestion extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function school_year()
    {
        return $this->belongsTo(CompanyYear::class);
    }

    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function answers()
    {
        return $this->hasMany(SubjectAnswer::class);
    }
}
