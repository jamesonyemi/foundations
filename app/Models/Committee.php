<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends Model {
	use SoftDeletes;

	protected $dates = [ 'deleted_at' ];
	protected $guarded = array ( 'id' );

    public function employees()
    {
        return $this->hasMany(Employee::class, 'committee_id')
            ->whereHas('active', function ($q) {
        $q->where('student_statuses.company_year_id',
                session( 'current_company_year' ));
    });
    }


    public function attended()
    {
        return $this->hasMany(Employee::class, 'committee_id')
            ->whereHas('active', function ($q) {
            $q->where('student_statuses.company_year_id', session( 'current_company_year' ))
                ->where('student_statuses.attended', 1);
        });
    }

    public function registrations()
    {
        return $this->hasManyThrough(Registration::class,
            Employee::class, 'committee_id', 'employee_id')
            /*->where('registrations.company_id','=', session('current_company'))*/
            ->where('registrations.company_year_id','=',session('current_company_year'));
    }

    public function confirmations()
    {
        return $this->hasManyThrough(Registration::class, Employee::class, 'committee_id', 'employee_id')
            /*->where('registrations.company_id','=', session('current_company'))*/
            ->where('registrations.company_year_id','=',session('current_company_year'))
            ->join( 'student_statuses', 'student_statuses.employee_id', '=', 'employees.id' )
            ->where('student_statuses.confirm','=','1');
    }
}
