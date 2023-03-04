<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiObjective extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];




    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('sector', function (Builder $builder) {
            /*$school = Company::find(session('current_company'));;*/
           /* if (session('current_company_sector') > 0) {

            }

            else {
                $builder->whereHas('kra', function ($q) {
                    $q->where('kras.company_id', session('current_company'));;
                });

            }*/


            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->whereHas('kra', function ($q) {
                        $q->where('kras.company_id', session('current_company'));;
                    });
                }
                else {


                }

            }

        });


    }


    public function kra()
    {
        return $this->belongsTo(Kra::class);
    }

    public function kpis()
    {
        return $this->hasMany(Kpi::class)->where('approved', 1);
    }

    public function sector_kpis($sector_id)
    {
        return $this->hasMany(Kpi::class)->where('approved', 1)
            ->whereHas('employee.company', function ($q) use($sector_id) {
                $q->where('companies.sector_id', $sector_id);
            });
    }

   public function getFullTitleAttribute()
    {
        return "{$this->kra->full_title} - {$this->title}" ?? "{$this->title}";
    }



}
