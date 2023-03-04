<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\TeacherSubjectRequest;
use App\Models\Level;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Repositories\SemesterRepository;
use App\Models\SchoolDirection;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\TeacherSchoolRepository;
use App\Repositories\SubjectRepository;
use App\Helpers\Settings;
use Illuminate\Http\Request;

class TeacherSubjectController extends SecureController
{
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;
    /**
     *
    /**
     * @var TeacherSchoolRepository
     */
    private $teacherSchoolRepository;
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;
    /**
     * @var SemesterRepository
     */
    private $semesterRepository;

    /**
     * DirectionController constructor.
     *
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param TeacherSchoolRepository $teacherSchoolRepository
     * @param SemesterRepository $semesterRepository
     * @param SubjectRepository $subjectRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        TeacherSubjectRepository $teacherSubjectRepository,
        TeacherSchoolRepository $teacherSchoolRepository,
        SubjectRepository $subjectRepository,
        SemesterRepository $semesterRepository
    ) {

        parent::__construct();

        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->subjectRepository = $subjectRepository;
        $this->semesterRepository = $semesterRepository;
        $this->teacherSchoolRepository = $teacherSchoolRepository;

        view()->share('type', 'teacher_subject');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('level.levels');
        $teacher_subjects = $this->teacherSubjectRepository->getAllForSchool(session('current_company'))
            ->with('teacher','subject', 'semester')
            ->get();
        return view('teacher_subject.index', compact('title', 'teacher_subjects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Teacher Course Allocation';
        $lecturers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->full_name : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend('Select Lecturer', '')
            ->toArray();

        $subjects  = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->fullname. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend('Select Course', '')
            ->toArray();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->full_title : "",
                ];
            })->pluck("title", 'id')
            ->prepend(trans('student.select_semester'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'lecturers', 'subjects','semesters'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(TeacherSubjectRequest $request)
    {
        foreach ($request['subject_id'] as $subject_id)
        {
            TeacherSubject::firstOrCreate
            (
                [
                    'company_id' => session('current_company'),
                    'school_year_id' => Semester::find($request->semester_id)->school_year_id,
                    'semester_id' => $request->semester_id,
                    'teacher_id' => $request->teacher_id,
                    'subject_id' => $subject_id
                ]
            );
        }


        return redirect('/teacher_subject');
    }

    /**
     * Display the specified resource.
     *
     * @param TeacherSubject $teacher_subject
     * @return Response
     */
    public function show(TeacherSubject $teacher_subject)
    {
        $title = trans('level.details');
        $action = 'show';
        return view('layouts.show', compact('teacher_subject', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TeacherSubject $teacher_subject
     * @return Response
     */
    public function edit(TeacherSubject $teacher_subject)
    {
        $title = 'Edit Teacher Course Allocation';
        $lecturers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->full_name : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend('Select Lecturer', '')
            ->toArray();
        $subjects  = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->fullname. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend('Select Course', '')
            ->toArray();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->full_title : "",
                ];
            })->pluck("title", 'id')
            ->prepend(trans('student.select_semester'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'teacher_subject', 'lecturers', 'subjects','semesters'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param TeacherSubject $teacher_subject
     * @return Response
     */
    public function update(TeacherSubjectRequest $request, TeacherSubject $teacher_subject)
    {
        $teacher_subject->update($request->all());
        return redirect('/teacher_subject');
    }

    public function delete(TeacherSubject $teacher_subject)
    {
        $title = trans('level.delete');
        return view('levels.delete', compact('teacher_subject', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TeacherSubject $teacher_subject
     * @return Response
     */
    public function destroy(TeacherSubject $teacher_subject)
    {
        $teacher_subject->delete();
        return redirect('/teacher_subject');
    }


}
