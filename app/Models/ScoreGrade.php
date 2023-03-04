<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreGrade extends Model
{
    use SoftDeletes;

    protected $dates = [ 'deleted_at' ];

    protected $guarded = array ( 'id' );

    public function mark_system()
    {
        return $this->belongsTo(MarkSystem::class, 'mark_system_id');
    }

    public function getGrade($mark_system_id, $grade)
    {
        $markSystem = ScoreGrade::where('mark_system_id', $mark_system_id)
            ->where(function ($q) use ($grade){
                $q->where('min_score', '<=', $grade);
                $q->where('max_score', '>=', $grade);
            })->first();
        return $markSystem;
    }


    public function gradeC($grade, $company_id)
    {
        $markSystem = Employee::where('company_id', '=', $company_id)
            ->get()
            ->filter(function($item) use ($grade) {
                return $item->PerformanceScoreGrade === $grade;
            });
        return $markSystem;
    }

}
