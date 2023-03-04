<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'course';

    protected $guarded = ['id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }


    public function CourseCategory()
    {
        return $this->belongsTo(CourseCategory::class, 'category');
    }

    public function teacher_subjects()
    {
        return $this->hasMany(TeacherSubject::class, 'subject_id');
    }



    public function lecturers()
    {
        return $this->hasMany(TeacherSubject::class, 'subject_id')
            ->where('teacher_subjects.school_year_id', '=', session('current_company_year'))
            ->where('teacher_subjects.semester_id', '=', session('current_company_semester'));;
    }

    public function markSystems()
    {
        return $this->hasMany(MarkSystem::class, 'subject_id')->orderBy('grade', 'asc');
    }

    public function markSystem()
    {
        return $this->belongsTo(MarkSystem::class, 'mark_system_id');
    }

    public function getTitleWithCodeAttribute()
    {
        return  $this->shortname. ' (' . $this->code .')';
    }


    public function getFullNameWithCodeAttribute()
    {
        return  $this->shortname. ' (' . $this->code .')';
    }

    public function students()
    {

        return $this->hasMany(Registration::class, 'subject_id', 'id')
            ->where('registrations.school_year_id', '=', session('current_company_year'))
            ->where('registrations.semester_id', '=', session('current_company_semester'));
    }

    public function students2($school_year_id,$semester_id)
    {

        return $this->hasMany(Registration::class, 'subject_id', 'id')
            ->where('registrations.school_year_id', '=', $school_year_id)
            ->where('registrations.semester_id', '=', $semester_id);
    }

    public function mark_system()
    {
        return $this->belongsTo(MarkSystem::class, 'mark_system_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }


    public function setHighestMarkAttribute($highest_mark)
    {
        if ($highest_mark!=null && $highest_mark!="") {
            $this->attributes['highest_mark'] = $highest_mark;
        } else {
            $this->attributes['highest_mark'] = null;
        }
    }

   /* public function enrol()
    {
        return $this->belongsTo(Enrol::class, 'courseid', 'id');
    }*/
}
