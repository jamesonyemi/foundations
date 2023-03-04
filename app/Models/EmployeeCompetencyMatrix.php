<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeCompetencyMatrix extends Model
{
    //
    /*use SoftDeletes;*/

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'employee_competency_matrix';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


    public function competencyLevel()
    {
        return $this->belongsTo(CompetencyMatrix::class, 'competency_matrix_id');
    }

    public function competencyMatrix()
    {
        return $this->belongsTo(CompetencyMatrix::class, 'competency_matrix_id');
    }
    public function competency()
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }

}
