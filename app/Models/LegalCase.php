<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCase extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function legalCaseCategory()
    {
        return $this->belongsTo(LegalCaseCategory::class);
    }

    public function legalFirm()
    {
        return $this->belongsTo(LegalFirm::class);
    }

    public function comments()
    {
        return $this->hasMany(LegalCaseComment::class)->orderByDesc('id');
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
