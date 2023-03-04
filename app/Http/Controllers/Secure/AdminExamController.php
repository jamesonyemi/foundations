<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ExamGroupRequest;
use App\Http\Requests\Secure\ExamSubjectRequest;
use App\Models\Exam;
use App\Models\StudentGroup;
use App\Repositories\ExamRepository;
use App\Repositories\OptionRepository;
use App\Repositories\StudentGroupRepository;
use App\Repositories\SubjectRepository;
use Yajra\DataTables\Facades\DataTables;

class AdminExamController extends SecureController
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var StudentGroupRepository
     */
    private $studentGroupRepository;

    /**
     * @var SubjectRepository
     */
    private $subjectRepository;

    /**
     * @var ExamRepository
     */
    private $examRepository;

    /**
     * ExamController constructor
     *
     * @param OptionRepository $optionRepository
     * @param StudentGroupRepository $studentGroupRepository
     * @param SubjectRepository $subjectRepository
     * @param ExamRepository $examRepository
     */
    public function __construct(
        OptionRepository $optionRepository,
        StudentGroupRepository $studentGroupRepository,
        SubjectRepository $subjectRepository,
        ExamRepository $examRepository
    ) {
        parent::__construct();

        view()->share('type', 'admin_exam');

        $columns = ['title', 'date', 'actions'];
        view()->share('columns', $columns);

        $this->optionRepository = $optionRepository;
        $this->studentGroupRepository = $studentGroupRepository;
        $this->subjectRepository = $subjectRepository;
        $this->examRepository = $examRepository;
    }

    public function index()
    {
        $title = trans('admin_exam.admin_exam');

        return view('admin_exam.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create_by_group()
    {
        $title = trans('admin_exam.new_by_group');
        list($exam_types, $groups) = $this->generateParams();

        return view('admin_exam.create_by_group', compact('title', 'groups', 'exam_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create_by_subject()
    {
        $title = trans('admin_exam.new_by_subject');
        list($exam_types, $groups) = $this->generateParams();

        return view('admin_exam.create_by_subject', compact('title', 'groups', 'exam_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExamGroupRequest $request
     *
     * @return Response
     */
    public function store_by_group(ExamGroupRequest $request)
    {
        $parent_exam = Exam::create(['user_id'=>$this->user->id,
            'title'=>$request->get('title'),
            'description'=>$request->get('description'),
            'option_id'=>$request->get('option_id'),
            'date'=>$request->get('date'), ]);
        foreach ($request->get('group_id', []) as $group_id) {
            $subjects = $this->subjectRepository
                ->getAllForStudentGroup($group_id)
                ->get();
            if (! empty($subjects)) {
                foreach ($subjects as $subject) {
                    $exam = new Exam($request->only('option_id', 'title', 'description', 'date'));
                    $exam->user_id = $this->user->id;
                    $exam->student_group_id = $group_id;
                    $exam->subject_id = $subject->id;
                    $exam->parent_id = $parent_exam->id;
                    $exam->save();
                }
            }
        }

        return redirect('/admin_exam');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExamSubjectRequest $request
     *
     * @return Response
     */
    public function store_by_subject(ExamSubjectRequest $request)
    {
        $parent_exam = Exam::create(['user_id'=>$this->user->id,
            'title'=>$request->get('title'),
            'description'=>$request->get('description'),
            'option_id'=>$request->get('option_id'),
            'date'=>$request->get('date'), ]);
        foreach ($request->get('subject_id', []) as $subject_id) {
            $exam = new Exam($request->only('option_id', 'title', 'description', 'date'));
            $exam->user_id = $this->user->id;
            $exam->student_group_id = $request->get('group_id', null);
            $exam->subject_id = $subject_id;
            $exam->parent_id = $parent_exam->id;
            $exam->save();
        }

        return redirect('/admin_exam');
    }

    /**
     * @return mixed
     */
    private function generateParams()
    {
        $exam_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'exam_type')->get()
            ->map(function ($option) {
                return [
                    'title' => $option->title,
                    'value' => $option->id,
                ];
            })->pluck('title', 'value')->toArray();

        $groups = $this->studentGroupRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
                                     ->get()
                                     ->map(function ($group) {
                                         return [
                                             'id'=>$group->id,
                                             'title'=>$group->title,
                                         ];
                                     })->pluck('title', 'id')->prepend(trans('attendances_by_subject.select_group'), 0)->toArray();

        return [$exam_types, $groups];
    }

    public function subjects(StudentGroup $studentGroup)
    {
        $subjects = $this->subjectRepository
            ->getAllForStudentGroup($studentGroup->id)
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        return $subjects;
    }

    /**
     * Display the specified resource.
     *
     * @param Exam $exam
     * @return Response
     */
    public function show(Exam $exam)
    {
        $title = trans('exam.details');
        $action = 'show';

        return view('layouts.show', compact('exam', 'title', 'action'));
    }

    public function data()
    {
        $exams = $this->examRepository->getAllForAdmin($this->user->id)
                                            ->get()
                                            ->map(function ($exam) {
                                                return [
                                                    'id' => $exam->id,
                                                    'title' => $exam->title,
                                                    'date' => $exam->date,
                                                ];
                                            });

        return Datatables::make($exams)
                          ->addColumn('actions', '<a href="{{ url(\'/admin_exam/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                          ->removeColumn('id')
                          ->rawColumns(['actions'])
                          ->make();
    }
}
