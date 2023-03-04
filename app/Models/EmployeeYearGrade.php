<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeYearGrade extends Model
{

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'employee_id',
        'company_year_id',
        'performance_score',
        'performance_score_grade_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function company_year()
    {
        return $this->belongsTo(CompanyYear::class);
    }

    public function performance_score_grade()
    {
        return $this->belongsTo(PerformanceScoreGrade::class);
    }

}
