<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkSystem extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('sector', function (Builder $builder) {
            /*$school = Company::find(session('current_company'));;*//*
            if (session('current_company_sector') > 0) {
                $builder->where('sector_id', session('current_company_sector'));
            }

            else {
                $builder->where('.kras.company_id', session('current_company'));;
            }*/

            @$school= Company::find(session('current_company'));;

            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->where('mark_systems.company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('school.sector.group', function ($q) use($school) {
                        $q->where('sectors.group_id', $school->sector->group_id);
                    });

                }

            }

        });


    }




    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }


    public function markSystems()
    {
        return $this->hasMany(MarkValue::class)->orderBy('id', 'asc');
    }

}
