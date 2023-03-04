<?php

namespace App\Http\Controllers\Secure;

use App\Events\Mark\MarkCreated;
use App\Helpers\Settings;
use App\Http\Requests\Secure\AddMarkRequest;
use App\Http\Requests\Secure\DeleteRequest;
use App\Http\Requests\Secure\ExamGetRequest;
use App\Http\Requests\Secure\MarkGetRequest;
use App\Http\Requests\Secure\MarkSystemGetRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Mark;
use App\Models\MarkType;
use App\Models\MarkValue;
use App\Models\ParentStudent;
use App\Models\Semester;
use App\Models\SmsMessage;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Repositories\ExamRepository;
use App\Repositories\MarkRepository;
use App\Repositories\MarkSystemRepository;
use App\Repositories\MarkTypeRepository;
use App\Repositories\MarkValueRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TeacherSubjectRepository;
use Carbon\Carbon;
use SMS;

class MarkController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * @var MarkRepository
     */
    private $markRepository;

    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * @var ExamRepository
     */
    private $examRepository;

    /**
     * @var MarkValueRepository
     */
    private $markValueRepository;

    /**
     * @var MarkTypeRepository
     */
    private $markTypeRepository;

    /**
     * @var MarkSystemRepository
     */
    private $markSystemRepository;

    /**
     * MarkController constructor.
     *
     * @param StudentRepository $studentRepository
     * @param MarkRepository $markRepository
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param ExamRepository $examRepository
     * @param MarkValueRepository $markValueRepository
     * @param MarkTypeRepository $markTypeRepository
     * @param MarkSystemRepository $markSystemRepository
     */
    public function __construct(
        StudentRepository $studentRepository,
        MarkRepository $markRepository,
        TeacherSubjectRepository $teacherSubjectRepository,
        ExamRepository $examRepository,
        MarkValueRepository $markValueRepository,
        MarkTypeRepository $markTypeRepository,
        MarkSystemRepository $markSystemRepository
    ) {
        parent::__construct();

        $this->studentRepository = $studentRepository;
        $this->markRepository = $markRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->examRepository = $examRepository;
        $this->markValueRepository = $markValueRepository;
        $this->markTypeRepository = $markTypeRepository;
        $this->markSystemRepository = $markSystemRepository;

        view()->share('type', 'mark');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('mark.marks');

        $subject_ids = TeacherSubject::where('company_year_id', session('current_company_year'))
            ->where('company_id', session('current_company'))
            ->where('teacher_id', $this->user->id)
            ->distinct('subject_id')
            ->get()
            ->pluck('subject_id')
            ->toArray();

        $students = $this->studentRepository->getAllForSubject($subject_ids)
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'id'   => $student->id,
                    'name' => $student->user->full_name.'-'.$student->sID,
                ];
            })->pluck('name', 'id')->toArray();
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
        $marktype = $this->markTypeRepository->getAll()->get()->pluck('title', 'id')->toArray();

        return view('mark.index', compact('title', 'students', 'subjects', 'marktype'));
    }

    public function marksForSubjectAndDate(MarkGetRequest $request)
    {
        $marks = $this->markRepository->getAll()
                                      ->with('student', 'student.user', 'mark_type', 'mark_value', 'subject')
                                      ->get()/*
                                      ->filter(function ($marksItem) use ($request) {
                                          return ( $marksItem->company_year_id == session('current_company_year') &&
                                                   $marksItem->subject_id == $request->get('subject_id') &&
                                                   Carbon::createFromFormat(Settings::get('date_format'), $marksItem->date) ==
                                                   Carbon::createFromFormat(Settings::get('date_format'), $request->get('date')) );
                                      })*/
                                      ->map(function ($mark) {
                                          return [
                                              'id'         => $mark->id,
                                              'name'       => isset($mark->student->user->full_name) ? $mark->student->user->full_name.'-'.$mark->student->sID : '',
                                              'mark_type'  => isset($mark->mark_type) ? $mark->mark_type->title : '',
                                              'mid_sem' => isset($mark->mid_sem) ? $mark->mid_sem : '',
                                              'exams' => isset($mark->exams) ? $mark->exams : '',
                                              'grade' => isset($mark->grade) ? $mark->grade : '',
                                          ];
                                      });

        return json_encode($marks);
    }

    public function examsForSubject(ExamGetRequest $request)
    {
        return $this->examRepository->getAllForGroupAndSubject(session('current_student_group'), $request['subject_id'])
                                    ->get()
                                    ->map(function ($exam) {
                                        return [
                                            'id'    => $exam->id,
                                            'title' => $exam->title,
                                        ];
                                    })->pluck('title', 'id')->toArray();
    }

    public function markValuesForSubject(MarkSystemGetRequest $request)
    {
        return $this->markValueRepository->getAllForSubject($request['subject_id'])
                                         ->get()
                                         ->map(function ($mark_value) {
                                             return [
                                                 'id'    => $mark_value->id,
                                                 'title' => $mark_value->grade,
                                             ];
                                         })->pluck('title', 'id')->prepend(trans('mark.select_mark_value'), 0)->toArray();
    }

    public function deleteMark(DeleteRequest $request)
    {
        $mark = Mark::find($request['id']);
        $mark->delete();
    }

    public function addmark(AddMarkRequest $request)
    {
        /*$date     = date_format(date_create_from_format(Settings::get('date_format'), $request->date), 'd-m-Y');
        $semestar = Semester::where(function ($query) use ($date) {
            $query->where('start', '>=', $date)
                  ->where('company_year_id', '=', session('current_company_year'));
        })->orWhere(function ($query) use ($date) {
            $query->where('end', '<=', $date)
                  ->where('company_year_id', '=', session('current_company_year'));
        })->first();*/
        foreach ($request['students'] as $student_id) {
            $mark = new Mark($request->except('students', '_token'));
            $mark->teacher_id = $this->user->id;
            $mark->student_id = $student_id;
            $mark->company_year_id = session('current_company_year');
            $mark->semester_id = isset($request->semester_id) ? $request->semester_id : session('current_company_semester');

            $subject = Subject::find($request->get('subject_id'));
            /*if ($request->get('mark_percent') != "") {
                if ($subject->highest_mark > 0) {
                    //if subject have highest mark
                    $mark_percent = round(( $request->get('mark_percent') * $subject->highest_mark ) / 100, 2);
                } else {
                    //if subject didn't have highest mark
                    $mark_percent = round($request->get('mark_percent'), 2);
                }
                //find mark value for that percent
                $markValue = MarkValue::where('max_score', '>=', $mark_percent)
                                      ->where('min_score', '<=', $mark_percent)
                                      ->where('mark_system_id', $subject->mark_system_id)->first();
                if (! is_null($markValue)) {
                    $mark->mark_value_id = $markValue->id;
                } else {
                    $mark->mark_value_id = $request->get('mark_value_id');
                }
            } else {
                $markValue = MarkValue::find($request->get('mark_value_id'));
                if ($subject->highest_mark > 0) {
                    //if subject have highest mark
                    $mark_percent = round(( $markValue->max_score * $subject->highest_mark ) / 100, 2);
                } else {
                    //if subject didn't have highest mark
                    $mark_percent = round($markValue->max_score, 2);
                }
                $mark->mark_percent  = $mark_percent;
                $mark->mark_value_id = $request->get('mark_value_id');
            }*/
            $mark->save();

            //event(new MarkCreated($mark));

            /*if (Settings::get('automatic_sms_mark') == 1
                 && Settings::get('sms_driver') != ""
                 && Settings::get('sms_driver') != 'none' ) {
                $parents_sms = ParentStudent::join('students', 'students.user_id', '=', 'parent_students.user_id_student')
                                            ->join('users', 'users.id', '=', 'parent_students.user_id_parent')
                                            ->where('students.id', $student_id)
                                            ->where(function ($q) {
                                                $q->where('users.get_sms', 1);
                                                $q->orWhereNull('users.get_sms');
                                            })
                                            ->select('users.*')->get();
                foreach ($parents_sms as $item) {
                    $school = Company::find(session('current_company'))->first();
                    if ($school->limit_sms_messages == 0 ||
                       $school->limit_sms_messages > $school->sms_messages_year) {
                        $student    = User::find(Student::find($student_id)->user_id);
                        $subject    = Subject::find($request->subject_id);
                        $mark_type  = MarkType::find($request->mark_type_id);
                        $mark_value = MarkValue::find($request->mark_value_id);

                        $sms_text = trans('mark.student') . ": " . $student->full_name . ', ' .
                                    trans('mark.date') . ': ' . $date . ', ' .
                                    trans('mark.subject') . ': ' . $subject->title . ', ' .
                                    trans('mark.mark_type') . ': ' . $mark_type->title . ', ' .
                                    trans('mark.mark_value') . ': ' . $mark_value->title;

                        $smsMessage                 = new SmsMessage();
                        $smsMessage->text           = $sms_text;
                        $smsMessage->number         = $item->mobile;
                        $smsMessage->user_id        = $item->id;
                        $smsMessage->user_id_sender = $this->user->id;
                        $smsMessage->company_id      = session('current_company');
                        $smsMessage->save();
                    }
                }
            }*/
        }
    }
}
