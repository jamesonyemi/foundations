<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkValue extends Model
{
    use SoftDeletes;

    protected $dates = [ 'deleted_at' ];

    protected $guarded = array ( 'id' );


    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('sector', function (Builder $builder) {

            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->where('mark_values.company_id', session('current_company'));;
                }
                else {
                    $builder->whereHas('school.sector.group', function ($q) use($school) {
                        $q->where('sectors.group_id', $school->sector->group_id);
                    });

                }

            }

        });


    }

    public function school()
    {
        return $this->belongsTo(Company::class);
    }



    public function mark_system()
    {
        return $this->belongsTo(MarkSystem::class, 'mark_system_id');
    }

    public function getGrade($mark_system_id, $grade)
    {
        $markSystem = MarkValue::where('mark_system_id', $mark_system_id)
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
                return $item->CompetencyScoreGrade === $grade;
            });
        return $markSystem;
    }

}
