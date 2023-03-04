<?php

namespace App\Http\Controllers\Traits;

use App\Models\Mark;
use DB;

trait MarksTrait
{
    public function listMarksGroup($subject_id, $start_date, $end_date, $student_group_id)
    {
        return $marks = Mark::join('mark_types', 'mark_types.id', '=', 'marks.mark_type_id')
            ->join('mark_values', 'mark_values.id', '=', 'marks.mark_value_id')
            ->join('students', 'students.id', '=', 'marks.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_student_group', 'student_student_group.student_id', '=', 'students.id')
            ->where('marks.subject_id', $subject_id)
            ->whereBetween('marks.date', [$start_date, $end_date])
            ->where('student_student_group.student_group_id', $student_group_id)
            ->orderBy('marks.date')
            ->select(
                'marks.id',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as student_name'),
                'marks.student_id',
                'marks.mark_type_id',
                'marks.mark_value_id',
                'marks.exam_id',
                DB::raw('(SELECT exams.title FROM exams where exams.id=marks.exam_id) as exam'),
                'mark_types.title as mark_type',
                'mark_values.grade AS mark_value',
                'marks.date'
            )
            ->get()->toArray();
    }

    public function listMarksGroupDate($teacher_id, $date, $student_group_id)
    {
        return $marks = Mark::join('mark_types', 'mark_types.id', '=', 'marks.mark_type_id')
            ->join('mark_values', 'mark_values.id', '=', 'marks.mark_value_id')
            ->join('students', 'students.id', '=', 'marks.student_id')
            ->join('subjects', 'subjects.id', '=', 'marks.subject_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_student_group', 'student_student_group.student_id', '=', 'students.id')
            ->where('marks.teacher_id', $teacher_id)
            ->where('marks.date', $date)
            ->where('student_student_group.student_group_id', $student_group_id)
            ->orderBy('marks.date')
            ->select(
                'marks.id',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as student_name'),
                'marks.student_id',
                'marks.mark_type_id',
                'marks.mark_value_id',
                'marks.exam_id',
                'subjects.title as subject',
                DB::raw('(SELECT exams.title FROM exams where exams.id=marks.exam_id) as exam'),
                'marks.subject_id',
                'mark_types.title as mark_type',
                'mark_values.grade AS mark_value',
                'marks.date'
            )
            ->get()->toArray();
    }

    public function listMarksStudent($date, $student_id)
    {
        return $marks = Mark::join('mark_types', 'mark_types.id', '=', 'marks.mark_type_id')
            ->join('mark_values', 'mark_values.id', '=', 'marks.mark_value_id')
            ->join('subjects', 'subjects.id', '=', 'marks.subject_id')
            ->where('marks.date', $date)
            ->where('marks.student_id', $student_id)
            ->orderBy('marks.date')
            ->select(
                'marks.id',
                'subjects.title as subject',
                DB::raw('(SELECT exams.title FROM exams where exams.id=marks.exam_id) as exam'),
                'mark_types.title as mark_type',
                'mark_values.grade AS mark_value',
                'marks.date'
            )
            ->get()->toArray();
    }
}
