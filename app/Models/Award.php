<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{

    protected $guarded = ['id'];

    protected $hidden = [];

   /* protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('company', function (Builder $builder) {
            if (admin()) {
                $builder->where('awards.company_id', admin()->company_id);
            }
            if (employee()) {
                $builder->where('awards.company_id', employee()->company_id);
            }
        });


    }*/

    public function employee()
    {

        return $this->belongsTo(Employee::class);
    }

    public function scopeCompany($query, $id)
    {
        return $query->join('employees', 'awards.employeeID', '=', 'employees.employeeID')
            ->where('employees.company_id', '=', $id);
    }



}
