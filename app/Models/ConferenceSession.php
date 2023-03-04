<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class ConferenceSession extends Model
{
    protected $guarded = array('id');

    public function day()
    {
        return $this->belongsTo(ConferenceDay::class, 'conference_day_id');
    }

    public function students()
    {
        return $this->hasMany(SessionAttendance::class, 'conference_session_id')
            ->where('company_year_id', session('current_company_year'));
    }

    public function studentActive($id)
    {
        return $this->hasMany(SessionAttendance::class, 'conference_session_id')
            ->where('company_year_id', session('current_company_year'))
            ->where('employee_id', $id);
    }




}
