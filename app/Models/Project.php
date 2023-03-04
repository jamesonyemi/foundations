<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $appends = ['cost'];

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function comments()
    {
        return $this->hasMany(ProjectComment::class);
    }



    public function components()
    {
        return $this->hasMany(ProjectComponent::class);
    }


    public function artisans()
    {
        return $this->hasMany(ProjectComponent::class);
    }


    public function getCostAttribute()
    {
        return $this->components()->sum('cost');
    }


    public function project_category()
    {
        return $this->belongsTo(ProjectCategory::class);
    }


    public function project_status()
    {
        return $this->belongsTo(ProjectStatus::class);
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


    public function stakeHolders()
    {
        return $this->belongsToMany(Employee::class)->withTimeStamps();
    }


    public function stakeHolderIds()
    {
        return $this->belongsToMany(Employee::class);
    }




}