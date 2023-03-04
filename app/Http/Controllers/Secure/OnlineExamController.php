<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\ExcelfileValidator;
use App\Helpers\Settings;
use App\Http\Requests\Secure\OnlineExamRequest;
use App\Models\OnlineExam;
use App\Models\OnlineExamAnswer;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamUserAnswer;
use App\Models\User;
use App\Repositories\ExcelRepository;
use App\Repositories\OnlineExamRepository;
use App\Repositories\StudentGroupRepository;
use App\Repositories\TeacherSubjectRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class OnlineExamController extends SecureController
{
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * @var StudentGroupRepository
     */
    private $studentGroupRepository;

    /**
     * @var OnlineExamRepository
     */
    private $onlineExamRepository;

    const ANSWERS_TYPE_TEXT = 1;

    const ANSWERS_TYPE_ONE = 2;

    const ANSWERS_TYPE_MULTI = 3;

    public static $answers_types = [
        self::ANSWERS_TYPE_TEXT  => 'Text answer',
        self::ANSWERS_TYPE_ONE   => 'One answer',
        self::ANSWERS_TYPE_MULTI => 'Multiple answers',
    ];

    /**
     * @var ExcelRepository
     */
    private $excelRepository;

    /**
     * BookController constructor.
     *
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param StudentGroupRepository $studentGroupRepository
     * @param OnlineExamRepository $onlineExamRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        TeacherSubjectRepository $teacherSubjectRepository,
        StudentGroupRepository $studentGroupRepository,
        OnlineExamRepository $onlineExamRepository,
        ExcelRepository $excelRepository
    ) {
        parent::__construct();

        view()->share('type', 'online_exam');

        $columns = ['title', 'subject', 'start_date', 'end_date', 'actions'];
        view()->share('columns', $columns);

        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->studentGroupRepository = $studentGroupRepository;
        $this->onlineExamRepository = $onlineExamRepository;
        $this->excelRepository = $excelRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('online_exam.online_exams');

        return view('online_exam.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('online_exam.new');
        $this->generateParam();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OnlineExamRequest|Request $request
     *
     * @return Response
     */
    public function store(OnlineExamRequest $request)
    {
        $onlineExam = new OnlineExam($request->only('access_code', 'min_pass', 'exam_time', 'date_end', 'date_start', 'subject_id', 'description', 'title'));
        $onlineExam->student_group_id = session('current_student_group');
        $onlineExam->user_id = $this->user->id;
        $onlineExam->save();

        if ($request->hasFile('import_file')) {
            $this->import_questions_and_answers($request, $onlineExam);
        } else {
            $this->save_questions_and_answers($request, $onlineExam);
        }

        return redirect('/online_exam');
    }

    /**
     * Display the specified resource.
     *
     * @param OnlineExam $onlineExam
     *
     * @return Response
     */
    public function show(OnlineExam $onlineExam)
    {
        $title = trans('online_exam.details');
        $action = 'show';
        $this->generateParam();

        return view('layouts.show', compact('title', 'onlineExam', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param OnlineExam $onlineExam
     *
     * @return Response
     * @internal param int $id
     */
    public function edit(OnlineExam $onlineExam)
    {
        $title = trans('online_exam.edit');
        $this->generateParam();

        return view('layouts.edit', compact('title', 'onlineExam'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OnlineExamRequest|Request $request
     * @param OnlineExam $onlineExam
     *
     * @return Response
     */
    public function update(OnlineExamRequest $request, OnlineExam $onlineExam)
    {
        $onlineExam->update($request->only('access_code', 'min_pass', 'exam_time', 'date_end', 'date_start', 'subject_id', 'description', 'title'));
        $onlineExam->save();

        OnlineExamQuestion::where('online_exam_id', $onlineExam->id)->delete();

        $this->save_questions_and_answers($request, $onlineExam);

        return redirect('/online_exam');
    }

    public function delete(OnlineExam $onlineExam)
    {
        $title = trans('online_exam.delete');

        return view('online_exam.delete', compact('title', 'onlineExam'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param OnlineExam $onlineExam
     *
     * @return Response
     */
    public function destroy(OnlineExam $onlineExam)
    {
        $onlineExam->delete();

        return redirect('/online_exam');
    }

    public function data()
    {
        $onlineExams = $this->onlineExamRepository->getAllForGroup(session('current_student_group'))
                                                  ->with('subject')
                                                  ->get()
                                                  ->map(function ($onlineExam) {
                                                      return [
                                                          'id'         => $onlineExam->id,
                                                          'title'      => $onlineExam->title,
                                                          'subject'    => isset($onlineExam->subject->title) ? $onlineExam->subject->title : '',
                                                          'start_date' => $onlineExam->date_start,
                                                          'end_date'   => $onlineExam->date_end,
                                                      ];
                                                  });

        return Datatables::make($onlineExams)
                          ->addColumn('actions', '<a href="{{ url(\'/online_exam/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/online_exam/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/online_exam/\' . $id . \'/show_results\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-check-square"></i>  {{ trans("online_exam.show_results") }}</a>
                                     <a href="{{ url(\'/online_exam/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
                          ->removeColumn('id')
                          ->rawColumns(['actions'])->make();
    }

    private function generateParam()
    {
        $subjects = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndGroupAndTeacher(session('current_company_year'), session('current_student_group'), $this->user->id)
            ->with('subject')
            ->get()
            ->filter(function ($subject) {
                return  isset($subject->subject->title);
            })
            ->map(function ($subject) {
                return [
                    'id'    => $subject->subject_id,
                    'title' => $subject->subject->title,
                ];
            })->pluck('title', 'id')->prepend(trans('mark.select_subject'), 0)->toArray();
        view()->share('subjects', $subjects);

        view()->share('answers_types', self::$answers_types);

        view()->share('answers_type_text', self::ANSWERS_TYPE_TEXT);
        view()->share('answers_type_one', self::ANSWERS_TYPE_ONE);
        view()->share('answers_type_multi', self::ANSWERS_TYPE_MULTI);
    }

    public function showResults(OnlineExam $onlineExam)
    {
        $title = trans('online_exam.show_results');
        $answered_array = [];

        if (! empty($onlineExam->answered)) {
            foreach ($onlineExam->answered as $answered) {
                $answered_array[] = [
                    'student' => $answered->user->full_name,
                    'user_id' => $answered->user_id,
                    'result'  => $answered->sum_points,
                ];
            }
        }

        return view('online_exam.results', compact('title', 'onlineExam', 'answered_array'));
    }

    public function showResultDetails(OnlineExam $onlineExam, User $user)
    {
        $title = trans('online_exam.show_results_for_student').$user->full_name;
        $answeredUser = OnlineExamUserAnswer::where('user_id', $user->id)->where('online_exam_id', $onlineExam->id)->get();

        return view('online_exam.result_details', compact('title', 'onlineExam', 'answeredUser'));
    }

    public function startExam(OnlineExam $onlineExam)
    {
        $this->generateParam();
        $answeredUser = OnlineExamUserAnswer::where('user_id', $this->user->id)->where('online_exam_id', $onlineExam->id)->first();

        if (! (($onlineExam->date_start <=
                   Carbon::now()->format(Settings::get('date_format'))) &&
                 ($onlineExam->date_end >=
                   Carbon::now()->format(Settings::get('date_format'))))
        ) {
            Flash::error(trans('online_exam.exam_was_expired'));

            return back();
        } elseif (isset($answeredUser->id)) {
            Flash::error(trans('online_exam.exam_finished'));

            return back();
        }
        $title = trans('online_exam.start_online_exam');

        return view('online_exam.start_exam', compact('title', 'onlineExam'));
    }

    public function submitAccessCode(OnlineExam $onlineExam, Request $request)
    {
        if ($onlineExam->access_code == $request->access_code) {
            return 1;
        }

        return 0;
    }

    public function submitAnswers(OnlineExam $onlineExam, Request $request)
    {
        if (! empty($request->answers)) {
            foreach ($request->answers as $question => $answers) {
                $onlineQuestion = OnlineExamQuestion::find($question);
                foreach ($answers as $key => $answer) {
                    $questionAnswer = new OnlineExamUserAnswer();
                    $questionAnswer->user_id = $this->user->id;
                    $questionAnswer->online_exam_id = $onlineExam->id;
                    $questionAnswer->online_exam_question_id = $question;

                    $onlineAnswer = OnlineExamAnswer::find($key);
                    $onlineCorrectAnswers = OnlineExamAnswer::where('online_exam_question_id', $question)->where('correct_answer', 1)->count();
                    switch ($onlineQuestion->answers_type) {
                        case self::ANSWERS_TYPE_TEXT:
                            $questionAnswer->answer_text = $answer[0];
                            if ($answer[0] == $onlineAnswer->title) {
                                $questionAnswer->points = $onlineQuestion->points;
                            }
                            break;
                        case self::ANSWERS_TYPE_ONE:
                            $questionAnswer->online_exam_answer_id = $key;
                            if ($key == $onlineAnswer->id && $onlineAnswer->correct_answer == 1) {
                                $questionAnswer->points = $onlineQuestion->points / $onlineCorrectAnswers;
                            }
                            break;
                        case self::ANSWERS_TYPE_MULTI:
                            $questionAnswer->online_exam_answer_id = $key;
                            if ($key == $onlineAnswer->id && $onlineAnswer->correct_answer == 1) {
                                $questionAnswer->points = $onlineQuestion->points / $onlineCorrectAnswers;
                            }
                            break;
                    }
                    $questionAnswer->save();
                }
            }
        }

        return redirect('report/'.$this->user->id.'/online_exams');
    }

    /**
     * @param OnlineExamRequest $request
     * @param OnlineExam $onlineExam
     */
    private function save_questions_and_answers(OnlineExamRequest $request, OnlineExam $onlineExam)
    {
        if (! empty($request->question)) {
            foreach ($request->question as $key => $question) {
                if ($question != '') {
                    $onlineExamQuestion = new OnlineExamQuestion();
                    $onlineExamQuestion->online_exam_id = $onlineExam->id;
                    $onlineExamQuestion->title = $question;
                    $onlineExamQuestion->answers_type = $request->answers_type[$key];
                    $onlineExamQuestion->points = $request->points[$key];
                    $onlineExamQuestion->save();

                    if (! empty($request->answers)) {
                        foreach ($request->answers[$key] as $key2 => $answer) {
                            if ($answer != '') {
                                $onlineExamAnswer = new OnlineExamAnswer();
                                $onlineExamAnswer->online_exam_question_id = $onlineExamQuestion->id;
                                $onlineExamAnswer->title = $answer;
                                $onlineExamAnswer->correct_answer = isset($request->correct_answer[$key][$key2]) ? $request->correct_answer[$key][$key2] :
                                    ((count($request->answers[$key]) == 1) ? 1 : 0);
                                $onlineExamAnswer->save();
                            }
                        }
                    }
                }
            }
        }
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/online_exams.xlsx'));
    }

    public function exportQuestions(OnlineExam $onlineExam)
    {
        $onlineQuestions = OnlineExamQuestion::where('online_exam_id', $onlineExam->id)->get();
        $questions = [];
        $answers = [];
        foreach ($onlineQuestions as $key => $item) {
            $type = ($item->answers_type == 1) ? 'text' : ($item->answers_type == 2) ? 'one' : 'multiple';
            $questions[] = [
                'Question ID'     => $key + 1,
                'Type'            => $type,
                'Question'        => $item->title,
                'Question points' => $item->points,
            ];

            $onlineQuestionsAnswers = OnlineExamAnswer::where('online_exam_question_id', $item->id)->get();
            foreach ($onlineQuestionsAnswers as $item2) {
                $answers[] = [
                    'Question ID'    => $key + 1,
                    'Answer'         => $item2->title,
                    'Correct answer' => ($item2->correct_answer == 1) ? 'true' : 'false',
                ];
            }
        }
        Excel::create(trans('online_exam.online_exams'), function ($excel) use ($questions, $answers) {
            $excel->sheet(trans('online_exam.questions'), function ($sheet) use ($questions) {
                $sheet->fromArray($questions, null, 'A1', true);
            })->sheet(trans('online_exam.answers'), function ($sheet) use ($answers) {
                $sheet->fromArray($answers, null, 'A1', true);
            });
        })->export('xlsx');
    }

    private function import_questions_and_answers($request, $onlineExam)
    {
        $path = $request->file('import_file')->getRealPath();

        $questions_reader = Excel::selectSheets('Questions')->load($path, function ($reader) {
        })->get();
        $answers_reader = Excel::selectSheets('Answer(s)')->load($path, function ($reader) {
        })->get();

        if (! empty($questions_reader) && $questions_reader->count()) {
            foreach ($questions_reader->toArray() as $row) {
                $type = (trim($row['type']) == 'text') ? 1 : ((trim($row['type']) == 'one') ? 2 : 3);
                $question = OnlineExamQuestion::create([
                    'online_exam_id'    => $onlineExam->id,
                    'answers_type'      => $type,
                    'title'             => trim($row['question']),
                    'points'            => trim($row['question_points']),
                ]);
                if (! empty($answers_reader) && $answers_reader->count()) {
                    foreach ($answers_reader->toArray() as $row_answer) {
                        if (trim($row['question_id']) == trim($row_answer['question_id'])) {
                            OnlineExamAnswer::create([
                                'online_exam_question_id' => $question->id,
                                'title'                   => trim($row_answer['answer']),
                                'correct_answer'          => trim($row_answer['correct_answer']) == 'true' ? 1 : 0,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
