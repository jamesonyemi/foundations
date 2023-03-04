<?php

namespace App\Repositories;

use App\Helpers\Settings;
use App\Models\Exam;
use App\Models\Mark;
use Carbon\Carbon;

class MarkRepositoryEloquent implements MarkRepository
{
    /**
     * @var Mark
     */
    private $model;

    /**
     * MarkRepositoryEloquent constructor.
     *
     * @param Mark $model
     */
    public function __construct(Mark $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchoolYearAndBetweenDate($company_year_id, $date_start, $date_end)
    {
        return $this->model->with('student', 'student.user', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->orderBy('student_id')
                           ->get()
                           ->filter(function ($marksItem) use ($company_year_id, $date_start, $date_end) {
                               return  $marksItem->company_year_id == $company_year_id &&
                                        (Carbon::createFromFormat(Settings::get('date_format'), $marksItem->date) >=
                                          Carbon::createFromFormat(Settings::get('date_format'), $date_start) &&
                                          (Carbon::createFromFormat(Settings::get('date_format'), $marksItem->date) <=
                                            Carbon::createFromFormat(Settings::get('date_format'), $date_end)));
                           });
    }

    public function getAllForSchoolYearAndExam($company_year_id, $exam_id)
    {
        return $this->model->with('student', 'student.user', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->get()
                           ->filter(function ($marksItem) use ($company_year_id, $exam_id) {
                               return  $marksItem->company_year_id == $company_year_id &&
                                        $marksItem->exam_id == $exam_id;
                           });
    }

    public function getAllForSchoolYearSubjectUserAndSemester($company_year_id, $subject_id, $user_id, $semester_id)
    {
        return $this->model->with('student', 'student.user', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->get()
                           ->filter(function ($marksItem) use ($company_year_id, $subject_id, $user_id) {
                               return  $marksItem->company_year_id == $company_year_id &&
                                        $marksItem->subject_id == $subject_id &&
                                        isset($marksItem->student->user) && $marksItem->student->user_id == $user_id &&
                                        ((isset($semester_id)) ? $marksItem->semester_id == $semester_id : true);
                           });
    }

    public function getAllForSchoolYearSubjectAndSemester($company_year_id, $subject_id, $semester_id)
    {
        return $this->model->with('student', 'student.user', 'mark_type', 'mark_value', 'subject')
            ->orderBy('date')
            ->get()
            ->filter(function ($marksItem) use ($company_year_id, $subject_id) {
                return  $marksItem->company_year_id == $company_year_id &&
                    $marksItem->subject_id == $subject_id &&
                    ((isset($semester_id)) ? $marksItem->semester_id == $semester_id : true);
            });
    }

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids)
    {
        return $this->model->with('student', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->orderBy('student_id')
                           ->get()
                           ->filter(function ($marksItem) use ($company_year_id, $student_user_ids) {
                               return  isset($marksItem->student) &&
                                        $marksItem->student->company_year_id == $company_year_id &&
                                        in_array($marksItem->student->user_id, $student_user_ids);
                           });
    }

    public function getAllForExam($exam_id)
    {
        $exam = Exam::find($exam_id);
        if ($exam->parent_id != 0) {
            $exams = Exam::where('parent_id', $exam_id)->pluck('id', 'id')->toArray();
        } else {
            $exams[$exam_id] = $exam_id;
        }

        return $this->model->with('student', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->orderBy('subject_id')
                           ->orderBy('student_id')
                           ->get()
                           ->filter(function ($marksItem) use ($exams) {
                               return  isset($marksItem->student) &&
                                        in_array($marksItem->exam_id, $exams);
                           });
    }

    public function getAllForSchoolYearStudentsAndBetweenDate($company_year_id, $student_user_ids, $start_date, $end_date)
    {
        return $this->model->with('student', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->orderBy('student_id')
                            ->whereBetween('date', [
                                Carbon::createFromFormat(Settings::get('date_format'), $start_date),
                                Carbon::createFromFormat(Settings::get('date_format'), $end_date),
                            ])
                           ->get()
                           ->filter(function ($marksItem) use ($company_year_id, $student_user_ids) {
                               return  isset($marksItem->student) &&
                                        $marksItem->student->company_year_id == $company_year_id &&
                                        in_array($marksItem->student->user_id, $student_user_ids);
                           });
    }

    public function getAllForSchoolYearStudentsSubject($company_year_id, $student_user_ids, $subject_id)
    {
        return $this->model->with('student', 'mark_type', 'mark_value', 'subject')
                           ->orderBy('date')
                           ->orderBy('student_id')
                           ->get()
                           ->filter(function ($marksItem) use ($company_year_id, $student_user_ids, $subject_id) {
                               return  isset($marksItem->student) &&
                                        $marksItem->student->company_year_id == $company_year_id &&
                                        $marksItem->subject_id == $subject_id &&
                                        in_array($marksItem->student->user_id, $student_user_ids);
                           });
    }
}
