<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Kra extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'kras';



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


    public function bscPerspective()
    {
        return $this->belongsTo(BscPerspective::class, 'bsc_perspective_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function kpiObjectives()
    {
        return $this->hasMany(KpiObjective::class);
    }

    public function kpis()
    {
        return $this->hasManyThrough(Kpi::class, KpiObjective::class)->where('kpis.company_year_id',  session('current_company_year'));
    }

    public function sector_kpis($sector_id)
    {
        return $this->hasManyThrough(Kpi::class, KpiObjective::class)->where('kpis.company_year_id',  session('current_company_year'))
            ->whereHas('employee.company', function ($q) use($sector_id) {
            $q->where('companies.sector_id', $sector_id);
    });
    }

    public function getFullTitleAttribute()
    {
        return "{$this->bscPerspective->title} - {$this->title}"?? "{$this->title}";
    }

}
