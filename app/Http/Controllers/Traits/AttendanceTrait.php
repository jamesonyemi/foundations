<?php

namespace App\Http\Controllers\Traits;

use App\Models\Attendance;
use App\Models\Timetable;
use DB;

trait AttendanceTrait
{
    public function listAttendanceGroup($student_group_id, $start_date, $end_date)
    {
        return Attendance::join('students', 'students.id', '=', 'attendances.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->join('student_student_group', 'student_student_group.student_id', '=', 'students.id')
            ->join('options', 'options.id', '=', 'attendances.option_id')
            ->whereBetween('attendances.date', [$start_date, $end_date])
            ->where('student_student_group.student_group_id', $student_group_id)
            ->orderBy('attendances.hour')
            ->orderBy('students.order')
            ->select(
                'attendances.id',
                'users.first_name',
                'users.last_name',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as student_name'),
                'attendances.student_id',
                'attendances.hour',
                'attendances.date',
                'options.title as option'
            )
            ->get()->toArray();
    }

    public function listAttendanceGroupDate($student_group_id, $date)
    {
        return Attendance::join('students', 'students.id', '=', 'attendances.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->join('student_student_group', 'student_student_group.student_id', '=', 'students.id')
            ->join('options', 'options.id', '=', 'attendances.option_id')
            ->where('attendances.date', $date)
            ->where('student_student_group.student_group_id', $student_group_id)
            ->orderBy('attendances.hour')
            ->orderBy('students.order')
            ->select(
                'attendances.id',
                'users.first_name',
                'users.last_name',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as student_name'),
                'attendances.student_id',
                'attendances.hour',
                'attendances.date',
                'attendances.option_id',
                'options.title as option'
            )
            ->get()->toArray();
    }

    public function listAttendanceStudent($student_id, $start_date, $end_date)
    {
        return Attendance::join('students', 'students.id', '=', 'attendances.student_id')
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->join('student_student_group', 'student_student_group.student_id', '=', 'students.id')
            ->join('options', 'options.id', '=', 'attendances.option_id')
            ->whereBetween('attendances.date', [$start_date, $end_date])
            ->where('students.id', $student_id)
            ->orderBy('attendances.hour')
            ->orderBy('students.order')
            ->select(
                'attendances.id',
                'subjects.title as subject',
                'attendances.hour',
                'attendances.date',
                'options.title as option'
            )
            ->get()->toArray();
    }

    public function listAttendanceStudentAPI($student_id, $start_date, $end_date)
    {
        $i = 1;
        $current = strtotime($start_date);
        $last = strtotime($end_date);
        $attendance = [];

        while ($current <= $last) {
            $attendance[$i]['date'] = date('Y-m-d', $current);
            $timetables = Timetable::join('teacher_subjects', 'teacher_subjects.id', '=', 'timetables.teacher_subject_id')
                ->join('student_student_group', 'student_student_group.student_group_id', '=', 'teacher_subjects.student_group_id')
                ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
                ->join('options', 'options.id', '=', 'attendances.option_id')
                ->where('student_student_group.student_id', $student_id)
                ->where('timetables.week_day', date('w', $current))
                ->select(
                    'timetables.hour',
                    DB::raw(date('Y-m-d', $current).' as date')
                )->get()->toArray();
            $attendances = [];
            if (! empty($timetables)) {
                foreach ($timetables as $timetable) {
                    $justified = Attendance::join('options', 'options.id', '=', 'attendances.option_id')
                        ->where('attendances.hour', $timetable['hour'])
                        ->where('attendances.date', $timetable['date'])
                        ->select('options.title as option')
                        ->first();
                    $attendances[$timetable['hour']] = $justified->option;
                }
            } else {
                $attendances = ['' => ''];
            }
            $attendance[$i]['attendances'] = $attendances;
            $current = strtotime($current);
            $i++;
        }

        return $attendance;
    }

    public function listAttendanceStudentDateAPI($student_id, $date)
    {
        return Attendance::join('students', 'students.id', '=', 'attendances.student_id')
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->join('options', 'options.id', '=', 'attendances.option_id')
            ->where('attendances.date', $date)
            ->where('students.id', $student_id)
            ->orderBy('attendances.hour')
            ->orderBy('students.order')
            ->select(
                'attendances.id',
                'subjects.title as subject',
                'attendances.hour',
                'attendances.date',
                'attendances.option_id',
                'options.title as option'
            )
            ->get()->toArray();
    }
}
