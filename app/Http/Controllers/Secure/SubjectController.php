<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ImportRequest;
use App\Imports\CourseImport;
use App\Imports\UccResultsImport;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\CourseCategory;
use App\Repositories\SectionRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\CourseCategoryRepository;
use App\Repositories\LevelRepository;
use App\Repositories\MarkSystemRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\SemesterRepository;
use App\Http\Requests\Secure\SubjectRequest;
use App\Helpers\Flash;
use Maatwebsite\Excel\Facades\Excel;
use Sentinel;

class SubjectController extends SecureController
{
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;
    /**
     * @var DirectionRepository
     */
    private $directionRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var MarkSystemRepository
     */
    private $markSystemRepository;

    private $levelRepository;
    private $semesterRepository;
    private $courseCategoryRepository;

    /**
     * SubjectController constructor.
     * @param SubjectRepository $subjectRepository
     * @param DirectionRepository $directionRepository
     * @param MarkSystemRepository $markSystemRepository
     */
    public function __construct(
        SubjectRepository $subjectRepository,
        CourseCategoryRepository $courseCategoryRepository,
        DirectionRepository $directionRepository,
        MarkSystemRepository $markSystemRepository,
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository,
        SemesterRepository $semesterRepository
    ) {

        parent::__construct();

        $this->subjectRepository = $subjectRepository;
        $this->directionRepository = $directionRepository;
        $this->markSystemRepository = $markSystemRepository;
        $this->levelRepository = $levelRepository;
        $this->semesterRepository = $semesterRepository;
        $this->courseCategoryRepository = $courseCategoryRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'subject');

    }

    /**
     *
     * Display a listing of the resource.
     *
     */
    public function index()
    {
       /* if (!Sentinel::hasAccess('courses.list')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $title = trans('subject.subjects');
        $subjects = $this->subjectRepository->getAllForSchool(session('current_company'))
            ->with('students', 'CourseCategory')
            ->get();
        return view('subject.index', compact('title', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Sentinel::hasAccess('courses.create')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $title = trans('subject.new');

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        $directions = $this->directionRepository->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $courseCategories = $this->courseCategoryRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend(trans('student.select_semester'), '')
            ->toArray();


        $subjects = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();


        $mark_systems = $this->markSystemRepository->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')->toArray();
        return view('layouts.create', compact('title', 'directions', 'mark_systems', 'levels', 'semesters', 'subjects', 'courseCategories', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|SubjectRequest $request
     * @return Response
     */
    public function store(SubjectRequest $request)
    {
        if (!Sentinel::hasAccess('courses.create')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $this->subjectRepository->create($request->all());
        return redirect('/subject');
    }

    /**
     * Display the specified resource.
     *
     * @param Subject $subject
     * @return Response
     * @internal param int $id
     */
    public function show(Subject $subject)
    {
        if (!Sentinel::hasAccess('courses.show')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $title = $subject->title;
        $students = $subject->students;
        $teacherSubject = TeacherSubject::whereSubjectId($subject->id)
            ->whereSchoolId(session('current_company'))
            ->whereSemesterId(session('current_company_semester'))
            ->first();
        $action = 'show';
        return view('layouts.show', compact('subject', 'title', 'action', 'students', 'teacherSubject'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Subject $subject
     * @return Response
     * @internal param int $id
     */
    public function edit(Subject $subject)
    {
        if (!Sentinel::hasAccess('courses.edit')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $title = trans('subject.edit');

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        $directions = $this->directionRepository->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();


        $courseCategories = $this->courseCategoryRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $semesters = $this->semesterRepository
            ->getAll()
            ->get()
            ->pluck('full_title', 'id')
            ->prepend(trans('student.select_semester'), '')
            ->toArray();


        $subjects = $this->subjectRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        $mark_systems = $this->markSystemRepository->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')->toArray();

        return view('layouts.edit', compact('title', 'subject', 'directions', 'mark_systems', 'levels', 'semesters', 'subjects', 'courseCategories', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|SubjectRequest $request
     * @param Subject $subject
     * @return Response
     * @internal param int $id
     */
    public function update(SubjectRequest $request, Subject $subject)
    {
        if (!Sentinel::hasAccess('courses.edit')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $subject->update($request->all());
        $subject->subject_show = isset($request['subject_show']) ? $request['subject_show'] : "";
        $subject->shortname = $request['fullname'];
        $subject->save();
        return redirect('/subject');
    }

    public function delete(Subject $subject)
    {
        if (!Sentinel::hasAccess('courses.delete')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $title = trans('subject.delete');
        return view('/subject/delete', compact('subject', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Subject $subject
     * @return Response
     * @throws \Exception
     * @internal param int $id
     */
    public function destroy(Subject $subject)
    {
        if (!Sentinel::hasAccess('courses.delete')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
        $subject->delete();
        return redirect('/subject');
    }

    public function create_invoices(Subject $subject)
    {
        $last_school_year = SchoolYear::orderBy('id', 'DESC')->first();
        if (isset($last_school_year->id) && $subject->fee > 0) {
            $student_users = $this->subjectRepository->getAllStudentsSubjectAndDirection()
                ->where('subjects.id', $subject->id)
                ->where('students.school_year_id', $last_school_year->id)
                ->distinct('students.user_id')->select('students.user_id')->get();
            foreach ($student_users as $user) {
                $invoice = new Invoice();
                $invoice->title = trans("subject.fee");
                $invoice->description = trans("subject.subject_fee") . $subject->title;
                $invoice->amount = $subject->fee;
                $invoice->user_id = $user->user_id;
                $invoice->save();
            }
        }
        return redirect('/subject');
    }



    public function findSectionSubjects(Request $request)
    {
        $subjects = $this->subjectRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->title. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->toArray();
        return $subjects;
    }




    public function toggleStudentShow(Request $request)
    {
        $teacherSubject = TeacherSubject::find($request->subject_id);
        if ($teacherSubject->student_show == 'on')

        {
            $teacherSubject->student_show = '';
            $teacherSubject->save();
        }
        else
        {

            $teacherSubject->student_show = 'on';
            $teacherSubject->save();
        }
        return $teacherSubject->student_show;
    }









    public function getImport()
    {
        $title = trans('subject.import_subject');

        return view('subject.import', compact('title'));
    }

    public function postImport(Request $request)
    {
        $upload = Excel::import(new CourseImport(), $request->file('file'));

        Flash::success('Course Data Uploaded Successfully');
        return redirect('/subject');
    }


    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'title'=>$request->title[$item],
                'direction_id'=>$request->direction_id[$item],
                'order'=>$request->order[$item],
                'class'=>$request->class[$item],
                'mark_system_id'=>$request->mark_system_id[$item],
                'fee'=>$request->fee[$item],
                'highest_mark'=>$request->highest_mark[$item],
                'lowest_mark'=>$request->lowest_mark[$item]
            ];
            $this->subjectRepository->create($import_data);
        }

        return redirect('/subject');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/subjects.xlsx'));
    }

    public function bulkDelete()
    {
        Subject::getQuery()->delete();
        return redirect("/subject");
    }
}
