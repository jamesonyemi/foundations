<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionAttendance extends Model {
	use SoftDeletes;

	protected $dates = [ 'deleted_at' ];

	protected $guarded = array ( 'id' );

	public function user() {
		return $this->belongsTo( User::class );
	}

	public function school() {
		return $this->belongsTo( Company::class );
	}

    public function dormitory() {
        return $this->belongsTo( Dormitory::class, 'dormitory_id'  );
    }

    public function section() {
        return $this->belongsTo( Department::class, 'department_id'  );
    }

    public function dormitoryRoom() {
        return $this->belongsTo( DormitoryRoom::class, 'dormitory_room_id'  );
    }

	public function student() {
		return $this->belongsTo( Employee::class, 'employee_id' );
	}

	public function employee() {
		return $this->belongsTo( Employee::class, 'employee_id' );
	}

	public function school_year() {
		return $this->belongsTo( CompanyYear::class, 'company_year_id' );
	}


	public function level() {
		return $this->belongsTo( Level::class, 'level_id' );
	}


	public function conference_session(){
	    return $this->belongsTo(SessionAttendance::class,'conference_session_id');
    }

    public function conference_day(){
        return $this->belongsTo(ConferenceDay::class,'conference_day_id');

    }
}
