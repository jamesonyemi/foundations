<?php

namespace App\Models;

use App\Helpers\Settings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeKpiActivity extends Model
{
    //


    protected $dates = ['deleted_at', 'created_at', 'updated_at', 'due_date'];
    protected $guarded = ['id'];


    public function date_format()
    {
        return Settings::get('date_format');
    }

 /*   public function getDueDateAttribute($due_date)
    {
        if ($due_date == "0000-00-00" || $due_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($due_date));
        }
    }*/

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }



    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function companies()
    {
        return $this->belongsToMany(Company::class)->withTimeStamps();
    }


    public function companyIds()
    {
        return $this->belongsToMany(Company::class);
    }


    public function kpi_activity_documents()
    {
        return $this->hasMany(KpiActivityDocument::class);
    }

    public function kpi_activity_comments()
    {
        return $this->hasMany(KpiActivityComment::class)->orderByDesc('id');
    }


    public function status()
    {
        return $this->belongsTo(KpiActivityStatus::class, 'kpi_activity_status_id');
    }

    public function getFullTitleAttribute()
    {
        return "{$this->kpi->title}  - {$this->title}" ?? "{$this->title}";
    }

}
