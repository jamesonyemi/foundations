<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalRequest extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function legalRequestCategory()
    {
        return $this->belongsTo(LegalRequestCategory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function comments()
    {
        return $this->hasMany(LegalRequestComment::class)->orderByDesc('id');
    }


    public function companies()
    {
        return $this->belongsToMany(Company::class)->withTimeStamps();
    }


    public function companyIds()
    {
        return $this->belongsToMany(Company::class);
    }


    public function stakeHolders()
    {
        return $this->belongsToMany(Employee::class)->withTimeStamps();
    }


    public function stakeHolderIds()
    {
        return $this->belongsToMany(Employee::class);
    }




}
