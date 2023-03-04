<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Center extends Model {
	use SoftDeletes;

	protected $dates = [ 'deleted_at' ];
	protected $guarded = array ( 'id' );
	protected $table = 'centers';



    public function employees()
    {
            return $this->hasMany(StudentStatus::class)->whereHas('employee', function ($q) {
                $q->where('student_statuses.company_year_id', session( 'current_company_year' ));
            });
    }


    public function male()
    {
            return $this->hasMany(StudentStatus::class)->whereHas('employee', function ($q) {
                $q->where('student_statuses.company_year_id', session( 'current_company_year' ));
            })->whereHas('employee.user', function ($q) {
        $q->where('users.gender', 1);
    });

    }
    public function female()
    {
        return $this->hasMany(StudentStatus::class)->whereHas('employee', function ($q) {
            $q->where('student_statuses.company_year_id', session( 'current_company_year' ));
        })->whereHas('employee.user', function ($q) {
            $q->where('users.gender', 0);
        });
    }



    public function attended()
    {
        return $this->hasMany(Employee::class, 'center_id')
            ->whereHas('active', function ($q) {
                $q->where('employees.company_id', session('current_company'))
                    ->where('student_statuses.company_year_id', session( 'current_company_year' ))
                    ->where('student_statuses.attended', 1);
            });
    }

    public function registrations()
    {
        return $this->hasManyThrough(Registration::class, Employee::class, 'center_id', 'student_id')
            ->where('registrations.company_id','=', session('current_company'))
            ->where('registrations.company_year_id','=',session('current_company_year'));
    }

    public function confirmations()
    {
        return $this->hasManyThrough(Registration::class, Employee::class, 'center_id', 'student_id')
            ->where('registrations.company_id','=', session('current_company'))
            ->where('registrations.company_year_id','=',session('current_company_year'))
            ->join( 'student_statuses', 'student_statuses.student_id', '=', 'employees.id' )
            ->where('student_statuses.confirm','=','1');
    }


    public function studentsAttended()
    {
        return $this->hasMany(Student::class, 'section_id')
            ->whereHas('active', function ($q) {
                $q->where('company_id', session('current_company'))
                    ->where('student_statuses.company_year_id', session( 'current_company_year' ))
                    ->where('student_statuses.attended', 1 );
            });
    }

    public function participants()
    {
        return $this->hasMany(StudentStatus::class)->whereHas('employee', function ($q) {
            $q->where('student_statuses.company_year_id', session( 'current_company_year' ));
        });

    }




    public function activeSession()
    {
        $activeSession      = ConferenceSession::where('active', 'Yes')->first();
        return $this->hasManyThrough(SessionAttendance::class, StudentStatus::class, 'center_id', 'employee_id')
            ->where('session_attendances.company_year_id','=',session('current_company_year'))
            ->where('session_attendances.conference_session_id','=',$activeSession->id);
    }

    public function currentSession($id)
    {
        return $this->hasMany(SessionAttendance::class)->where('session_attendances.conference_session_id','=',$id);
    }

    /*
    public function currentSession($id)
    {
        return $this->hasManyThrough(StudentStatus::class, SessionAttendance::class, 'center_id', 'employee_id')
            ->where('student_statuses.company_year_id','=',session('current_company_year'))
            ->where('session_attendances.conference_session_id','=',$id);
    }*/

    public function currentSessionAbsent($id)
    {
        return $this->hasManyThrough(SessionAttendance::class, StudentStatus::class, 'center_id', 'employee_id')
            ->where('session_attendances.company_year_id','=',session('current_company_year'))
            ->where('session_attendances.conference_session_id','!=',$id);
    }


}
