<?php

namespace App\Http\Controllers\Secure;

use App\Models\Exam;
use App\Http\Requests\Secure\ExamRequest;
use App\Repositories\ExamRepository;
use App\Repositories\OptionRepository;
use App\Repositories\TeacherSubjectRepository;
use Yajra\DataTables\Facades\DataTables;

class ExamController extends SecureController
{
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;
    /**
     * @var ExamRepository
     */
    private $examRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * ExamController constructor.
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param ExamRepository $examRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        TeacherSubjectRepository $teacherSubjectRepository,
        ExamRepository $examRepository,
        OptionRepository $optionRepository
    ) {

        parent::__construct();

        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->examRepository = $examRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'exam');

        $columns = ['title', 'subject', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('exam.exam');
        return view('exam.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('exam.new');
        list($subjects, $exam_types) = $this->generateParams();
        return view('layouts.create', compact('title', 'subjects', 'exam_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(ExamRequest $request)
    {
        $exam = new Exam($request->all());
        $exam->user_id = $this->user->id;
        $exam->student_group_id = session('current_student_group');
        $exam->save();

        return redirect('/exam');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Exam $exam)
    {
        $title = trans('exam.details');
        $action = 'show';
        return view('layouts.show', compact('exam', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Exam $exam)
    {
        $title = trans('exam.edit');
        list($subjects, $exam_types) = $this->generateParams();
        return view('layouts.edit', compact('title', 'exam', 'subjects', 'exam_types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(ExamRequest $request, Exam $exam)
    {
        $exam->update($request->all());
        return redirect('/exam');
    }

    /**
     *
     *
     * @param $website
     * @return Response
     */
    public function delete(Exam $exam)
    {
        $title = trans('exam.delete');
        return view('/exam/delete', compact('exam', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect('/exam');
    }

    public function data()
    {
        $exams = $this->examRepository->getAllForGroup(session('current_student_group'))
            ->with('subject')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'subject' => isset($exam->subject->title) ? $exam->subject->title : "",
                    'date' => $exam->date,
                ];
            });
        return Datatables::make($exams)
            ->addColumn('actions', '<a href="{{ url(\'/exam/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/exam/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/exam_attendance/\' . $id ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-bars"></i>  {{ trans("exam.attendances") }}</a>
                                    <a href="{{ url(\'/exam/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }

    /**
     * @return mixed
     */
    private function generateParams()
    {
        $subjects = $this->teacherSubjectRepository
                ->getAllForSchoolYearAndGroupAndTeacher(session('current_company_year'), session('current_student_group'), $this->user->id)
                ->with('subject')
                ->get()
                ->filter(function ($subject) {
                    return (isset($subject->subject->title));
                })
                ->map(function ($subject) {
                    return [
                        'id' => $subject->subject_id,
                        'title' => $subject->subject->title
                    ];
                })->pluck('title', 'id')->prepend(trans('mark.select_subject'), 0)->toArray();
        $exam_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'exam_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            })->pluck('title', 'value')->toArray();
        return [$subjects, $exam_types];
    }
}
