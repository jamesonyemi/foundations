<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyActivity extends Model
{
    //
    /*use SoftDeletes;*/

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];




    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function companies()
    {
        return $this->belongsToMany(Company::class)->withTimeStamps();
    }


    public function companyIds()
    {
        return $this->belongsToMany(Company::class);
    }



}
