<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRequestCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each tag can have many suppliers.
     *
     */
    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('year', function (Builder $builder) {
            @$school= Company::find(session('current_company'));;

            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->where('company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('company.sector', function ($query) use ($school) {
                        $query->where('sectors.group_id', $school->sector->group_id);
                    });


                    /*$builder->where('group_id', $school->sector->group_id);*/

                }

            }

        });


    }



    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    public function employeeRequests()
    {
        return $this->hasMany(EmployeeRequest::class);
    }




}
