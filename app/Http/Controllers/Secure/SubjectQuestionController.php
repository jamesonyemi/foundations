<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\SubjectAnswerRequest;
use App\Http\Requests\Secure\SubjectQuestionRequest;
use App\Models\Subject;
use App\Models\SubjectAnswer;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherSubjectRepository;
use Illuminate\Http\Request;
use App\Models\SubjectQuestion;
use App\Repositories\SubjectQuestionRepository;
use Yajra\DataTables\Facades\DataTables;

class SubjectQuestionController extends SecureController
{
    /**
     * @var SubjectQuestionRepository
     */
    private $subjectQuestionRepository;
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * SubjectQuestionController constructor.
     *
     * @param SubjectQuestionRepository $subjectQuestionRepository
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param SubjectRepository $subjectRepository
     */
    public function __construct(
        SubjectQuestionRepository $subjectQuestionRepository,
        TeacherSubjectRepository $teacherSubjectRepository,
        SubjectRepository $subjectRepository
    ) {
        parent::__construct();

        $this->subjectQuestionRepository = $subjectQuestionRepository;
        $this->teacherSubjectRepository  = $teacherSubjectRepository;
        $this->subjectRepository         = $subjectRepository;

        view()->share('type', 'subject_question');
    }

    /**
     *
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $title = trans('subject_question.subject_questions');
        $this->generateData();

        return view('subject_question.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('subject.new');
        $this->generateData();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|SubjectQuestionRequest $request
     *
     * @return Response
     */
    public function store(SubjectQuestionRequest $request)
    {

        $subjectQuestion                 = new SubjectQuestion($request->all());
        $subjectQuestion->user_id        = $this->user->id;
        $subjectQuestion->company_id      = session('current_company');
        $subjectQuestion->school_year_id = session('current_company_year');
        $subjectQuestion->save();

        return redirect('/subject_question');
    }

    /**
     * Display the specified resource.
     *
     * @param SubjectQuestion $subjectQuestion
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(SubjectQuestion $subjectQuestion)
    {
        $title  = trans('subject_question.details');
        $action = 'show';

        return view('layouts.show', compact('subjectQuestion', 'title', 'action'));
    }

    /**
     * Store a replay on question
     *
     * @param SubjectAnswerRequest|Request $request
     *
     * @param SubjectQuestion $subjectQuestion
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function replay(SubjectAnswerRequest $request, SubjectQuestion $subjectQuestion)
    {
        $questionAnswer                      = new SubjectAnswer($request->all());
        $questionAnswer->user_id             = $this->user->id;
        $questionAnswer->subject_question_id = $subjectQuestion->id;
        $questionAnswer->save();

        return redirect('/subject_question');
    }

    public function data(Subject $subject, Datatables $datatables)
    {

        $subjectsQuestions = $this->subjectQuestionRepository
            ->getAllForSubjectAndSchool(isset($subject->id) ? $subject->id : 0, session('current_company'))
            ->get()
            ->map(function ($subjectsQuestion) {
                return [
                    'id'      => $subjectsQuestion->id,
                    'subject' => isset($subjectsQuestion->subject->id) ? $subjectsQuestion->subject->title : "",
                    'title'   => $subjectsQuestion->title,
                    'student' => isset($subjectsQuestion->user) ? $subjectsQuestion->user->full_name : "",
                ];
            });

        return Datatables::make($subjectsQuestions)
                          ->addColumn('actions', '<a href="{{ url(\'/subject_question/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                          ->removeColumn('id')
                          ->rawColumns([ 'actions' ])->make(false);
    }

    private function generateData()
    {

        if ($this->user->inRole('student')) {
            $subjects = $this->getSubjectsForStudent();
        } else {
            $subjects = $this->getSubjectForTeacher();
        }

        view()->share('subjects', $subjects);
    }

    /**
     * @return mixed
     */
    private function getSubjectsForStudent()
    {
        $subjects = $this->subjectRepository->getAllStudentsSubjectAndDirection()
                                            ->where('students.user_id', $this->user->id)
                                            ->where('students.school_year_id', session('current_company_year'))
                                            ->where('students.company_id', session('current_company'))
                                            ->orderBy('subjects.order')
                                            ->select('subjects.id', 'subjects.title')
                                            ->pluck('subjects.title', 'subjects.id')
                                            ->prepend(trans('mark.select_subject'), 0)->toArray();

        return $subjects;
    }

    /**
     * @return mixed
     */
    private function getSubjectForTeacher()
    {
        $subjects = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndGroupAndTeacher(
                session('current_company_year'),
                session('current_student_group'),
                $this->user->id
            )
            ->with('subject')
            ->get()
            ->filter(function ($subject) {
                return ( isset($subject->subject->title) );
            })
            ->map(function ($subject) {
                return [
                    'id'    => $subject->subject_id,
                    'title' => $subject->subject->title
                ];
            })->pluck('title', 'id')->prepend(trans('mark.select_subject'), 0)->toArray();

        return $subjects;
    }
}
