<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\AddStudentFinalMarkRequest;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentFinalMark;
use App\Models\StudentGroup;
use App\Models\Subject;
use App\Repositories\MarkRepository;
use App\Repositories\MarkValueRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentFinalMarkRepository;
use App\Repositories\StudentGroupRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubjectRepository;
use Carbon\Carbon;
use App\Helpers\Settings;

class StudentFinalMarkController extends SecureController
{
    /**
     * @var StudentFinalMarkRepository
     */
    private $studentFinalMarkRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SchoolRepository
     */
    private $schoolRepository;
    /**
     * @var SchoolYearRepository
     */
    private $schoolYearRepository;
    /**
     * @var MarkValueRepository
     */
    private $markValueRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;
    /**
     * @var StudentGroupRepository
     */
    private $studentGroupRepository;
    /**
     * @var MarkRepository
     */
    private $markRepository;

    /**
     * StudentFinalMarkController constructor.
     *
     * @param StudentFinalMarkRepository $studentFinalMarkRepository
     * @param StudentRepository $studentRepository
     * @param SchoolRepository $schoolRepository
     * @param SchoolYearRepository $schoolYearRepository
     * @param MarkValueRepository $markValueRepository
     * @param SectionRepository $sectionRepository
     * @param SubjectRepository $subjectRepository
     * @param StudentGroupRepository $studentGroupRepository
     * @param MarkRepository $markRepository
     */
    public function __construct(
        StudentFinalMarkRepository $studentFinalMarkRepository,
        StudentRepository $studentRepository,
        SchoolRepository $schoolRepository,
        SchoolYearRepository $schoolYearRepository,
        MarkValueRepository $markValueRepository,
        SectionRepository $sectionRepository,
        SubjectRepository $subjectRepository,
        StudentGroupRepository $studentGroupRepository,
        MarkRepository $markRepository
    ) {

        parent::__construct();

        $this->studentFinalMarkRepository = $studentFinalMarkRepository;
        $this->studentRepository = $studentRepository;
        $this->schoolRepository = $schoolRepository;
        $this->schoolYearRepository = $schoolYearRepository;
        $this->markValueRepository = $markValueRepository;
        $this->sectionRepository = $sectionRepository;
        $this->subjectRepository = $subjectRepository;
        $this->studentGroupRepository = $studentGroupRepository;
        $this->markRepository = $markRepository;

        $this->middleware('authorized:student_final_mark.show', ['only' => ['index', 'getGroups','getSubjects', 'addFinalMark','getStudents']]);

        view()->share('type', 'student_final_mark');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $sections = $this->sectionRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
                ->get()
                ->pluck('title', 'id')->prepend(trans('student_final_mark.select_section'), 0)->toArray();
        $title = trans('student_final_mark.student_final_mark');
        return view('student_final_mark.index', compact('title', 'sections'));
    }

    public function getGroups(Section $section)
    {
        return $this->studentGroupRepository->getAllForSection($section->id)
            ->get()
            ->pluck('title', 'id')->toArray();
    }

    public function getStudents(StudentGroup $studentGroup, Subject $subject)
    {
        $students = $this->studentRepository->getAllForStudentGroup($studentGroup->id)
            ->pluck('user.full_name', 'id')->toArray();
        $student_final_marks = [];
        $student_marks = [];
        foreach ($students as $id => $name) {
            $final_marks = StudentFinalMark::where('student_id', $id)
                ->where('subject_id', $subject->id)
                ->where('company_id', session('current_company'))
                ->where('school_year_id', session('current_company_year'))
                ->first();
            $user_id = Student::find($id)->user_id;
            $student_marks[$id] = $this->markRepository
                ->getAllForSchoolYearStudentsSubject(session('current_company_year'), [$user_id=>$user_id], $subject->id)
                ->map(function ($mark) {
                    return [
                        'date' => Carbon::createFromFormat(Settings::get('date_format'), $mark->date)->toDateString(),
                        'mark_type' => isset($mark->mark_type) ? $mark->mark_type->title : '',
                        'mark_value' => isset($mark->mark_value) ? $mark->mark_value->grade : '',
                        'mark_percent' => isset($mark->mark_value) ? $mark->mark_percent : '',
                    ];
                })->toArray();

            $student_final_marks[$id] = ['student_id' => $id,
                                     'student_name' => $name,
                                     'mark_value' => isset($final_marks) ? $final_marks->mark_value_id : 0];
        }

        $mark_values = $this->markValueRepository->getAllForSubject($subject->id)
                ->get()
                ->pluck('grade', 'id')->prepend(trans('student_final_mark.select_mark_value'), 0)->toArray();

        return ['student_final_marks' => $student_final_marks,
                'student_marks' => $student_marks,
                'mark_values' => $mark_values];
    }

    public function getSubjects(StudentGroup $studentGroup)
    {
        return $subjects = $this->subjectRepository->getAllForDirectionAndClass($studentGroup->direction_id, $studentGroup->class)
            ->get()
            ->pluck('title', 'id')->toArray();
    }

    public function addFinalMark(AddStudentFinalMarkRequest $request)
    {
        $mark_exists = StudentFinalMark::where('student_id', $request->student_id)
            ->where('subject_id', $request->subject_id)->first();
        if (is_null($mark_exists) && $request->mark_value_id > 0) {
            $mark = new StudentFinalMark($request->only('student_id', 'subject_id', 'mark_value_id'));
            $mark->company_id = session('current_company');
            $mark->school_year_id = session('current_company_year');
            $mark->save();
        } else {
            if ($request->mark_value_id > 0) {
                $mark_exists->update($request->only('mark_value_id'));
            } else {
                $mark_exists->delete();
            }
        }
    }
}
