<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSchool extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function courses()
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id', 'user_id')
            ->where('company_year_id', '=', session('current_company_year'))->where('semester_id', '=', session('current_company_semester'));
    }
}
