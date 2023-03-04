<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicationCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('group', function (Builder $builder) {

            $company = Company::find(session('current_company'));
            if (isset($company))
            {
                if ($company->stand_alone == 1) {
                    $builder->where('company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('company.sector', function ($q) use ($company) {
                        $q->where('sectors.group_id', $company->sector->group_id);;
                    });
                }
            }

        });
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    public function publications()
    {
        return $this->hasMany(Publication::class);
    }





}
