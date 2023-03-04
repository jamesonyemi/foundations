<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests;
use App\Http\Requests\Secure\BehaviorRequest;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Repositories\BehaviorRepository;
use App\Repositories\StudentRepository;
use Yajra\DataTables\Facades\DataTables;

class TeacherStudentController extends SecureController
{
    /**
     * @var BehaviorRepository
     */
    private $behaviorRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * TeacherStudentController constructor.
     * @param BehaviorRepository $behaviorRepository
     * @param StudentRepository $studentRepository
     */
    public function __construct(
        BehaviorRepository $behaviorRepository,
        StudentRepository $studentRepository
    ) {

        parent::__construct();
        $this->behaviorRepository = $behaviorRepository;
        $this->studentRepository = $studentRepository;

        view()->share('type', 'teacherstudent');

        $columns = ['sID','full_name','programme', 'level', 'actions'];
        view()->share('columns', $columns);
    }

    public function index()
    {
        $title = trans('teacherstudent.students');
        $subject_ids = TeacherSubject::where('school_year_id', session('current_company_year'))
            ->where('company_id', session('current_company'))
            ->where('teacher_id', $this->user->id)
            ->distinct('subject_id')
            ->get()
            ->pluck('subject_id')
            ->toArray();


        $students = $this->studentRepository->getAllForSubject($subject_ids)
            ->with('user')
            ->get();

        return view('teacherstudent.index', compact('title', 'students'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Student $student)
    {
        $title = trans('teacherstudent.details');

        return view('teacherstudent.show', compact('student', 'title'));
    }

    public function behavior(Student $student)
    {
        $user = User::find($student->user_id);
        $title = trans('teacherstudent.behavior_title') . $user->first_name . ' ' . $user->last_name;
        $behaviors = $this->behaviorRepository->getAll()->get()->pluck('title', 'id')->toArray();
        return view('teacherstudent.behavior', compact('student', 'title', 'behaviors'));
    }

    public function change_behavior(BehaviorRequest $request, Student $student)
    {
        $student->behavior()->attach($request['behavior_id']);

        return redirect('/teacherstudent');
    }


    public function data()
    {
        $current_student_group = StudentGroup::find(session('current_student_group'));


        $subject_ids = TeacherSubject::where('school_year_id', session('current_company_year'))
            ->where('company_id', session('current_company'))
            ->where('teacher_id', $this->user->id)
            ->distinct('subject_id')
            ->get()
            ->pluck('subject_id')
            ->toArray();


        $section_teacher = isset($current_student_group->section->section_teacher_id) ? $current_student_group->section->section_teacher_id : "";
        $is_head_teacher = (!is_null($section_teacher) && $section_teacher == $this->user->id) ? 1 : 0;

        $studentsGroup = $this->studentRepository->getAllForSubject($subject_ids)
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'sID' => isset($student->user) ? $student->sID : "",
                    'full_name' => isset($student->user) ? $student->user->full_name : "",
                    'programme' => isset($student->programme) ? $student->programme->title : "",
                    'level' => isset($student->level) ? $student->level->name : "",
                ];
            });
        if ($is_head_teacher > 0) {
            return Datatables::make($studentsGroup)
                ->addColumn('actions', '<a href="{{ url(\'/teacherstudent/\' . $id . \'/behavior\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-edit"></i>  {{ trans("teacherstudent.behavior") }}</a>
                                     <a href="{{ url(\'/teacherstudent/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                ->removeColumn('id')
                 ->rawColumns([ 'actions' ])->make();
        } else {
            return Datatables::make($studentsGroup)
                ->addColumn('actions', '<a href="{{ url(\'/teacherstudent/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                ->removeColumn('id')
                 ->rawColumns([ 'actions' ])->make();
        }
    }
}
