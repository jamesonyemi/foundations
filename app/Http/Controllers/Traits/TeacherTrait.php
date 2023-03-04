<?php

namespace App\Http\Controllers\Traits;

use App\Models\StudentGroup;
use App\Models\TeacherSubject;

trait TeacherTrait
{
    public function teacherGroups($company_year_id, $user_id)
    {
        return ['student_groups' => StudentGroup::join('directions', 'directions.id', '=', 'student_groups.direction_id')
                ->whereIn(
                    'student_groups.id',
                    TeacherSubject::where('teacher_id', $user_id)
                        ->where('company_year_id', $company_year_id)
                    ->distinct()->pluck('student_group_id')->toArray()
                )
                ->select(['student_groups.id', 'student_groups.title',
                    'directions.title as direction', 'student_groups.class', ])->get()->toArray(),
        ];
    }
}
