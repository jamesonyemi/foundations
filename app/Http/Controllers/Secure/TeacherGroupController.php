<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\DeleteRequest;
use App\Http\Requests\Secure\RegistrationRequest;
use App\Http\Requests\Secure\TimetableRequest;
use App\Models\Direction;
use App\Models\MarkSystem;
use App\Models\MarkValue;
use App\Models\Registration;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\StudentStatus;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\Timetable;
use App\Repositories\ActivityLogRepository;
use App\Repositories\StudentRepository;
use App\Repositories\RegistrationRepository;
use App\Repositories\SessionRepository;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\TimetablePeriodRepository;
use App\Repositories\TimetableRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\OptionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\Flash;
use Illuminate\Http\Request;
use App\Repositories\ExamRepository;
use App\Repositories\MarkSystemRepository;
use App\Repositories\MarkTypeRepository;
use App\Repositories\MarkValueRepository;
use Carbon\Carbon;
use App\Helpers\Settings;
use App\Repositories\MarkRepository;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Traits\TimeTableTrait;

class TeacherGroupController extends SecureController
{
    use TimeTableTrait;
    /**
     * @var TimetableRepository
     */
    private $timetableRepository;
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;/**
 * @var StudentRepository
 */
    private $registrationRepository;
    /**
     * @var TimetablePeriodRepository
     */
    private $timetablePeriodRepository;
    /**
     * @var AttendanceRepository
     */
    private $attendanceRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
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
    private $sessionRepository;
    protected $activity;
    protected $module = 'Attendance';


    /**
     * TeacherGroupController constructor.
     *
     * @param TimetableRepository $timetableRepository
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param StudentRepository $studentRepository
     * @param TimetablePeriodRepository $timetablePeriodRepository
     */
    public function __construct(
        TimetableRepository $timetableRepository,
        TeacherSubjectRepository $teacherSubjectRepository,
        StudentRepository $studentRepository,
        RegistrationRepository $registrationRepository,
        TimetablePeriodRepository $timetablePeriodRepository,
        AttendanceRepository $attendanceRepository,
        OptionRepository $optionRepository,
        ExamRepository $examRepository,
        MarkValueRepository $markValueRepository,
        MarkTypeRepository $markTypeRepository,
        MarkSystemRepository $markSystemRepository,
        SessionRepository $sessionRepository,
        ActivityLogRepository $activity
    ) {

        parent::__construct();

        $this->timetableRepository = $timetableRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->studentRepository = $studentRepository;
        $this->registrationRepository = $registrationRepository;
        $this->timetablePeriodRepository = $timetablePeriodRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->optionRepository     = $optionRepository;
        $this->examRepository           = $examRepository;
        $this->markValueRepository      = $markValueRepository;
        $this->markTypeRepository       = $markTypeRepository;
        $this->markSystemRepository     = $markSystemRepository;
        $this->sessionRepository = $sessionRepository;
        $this->activity = $activity;

        view()->share('type', 'teachergroup');

        $columns = ['title', 'level', 'direction', 'registrations', 'markSystem', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display the specified resource.
     *
     * @param  StudentGroup $studentGroup
     * @return Response
     */
    public function show(StudentGroup $studentGroup)
    {
        $title = trans('teachergroup.details');
        $action = 'show';
        return view('teachergroup.show', compact('studentGroup', 'title', 'action'));
    }

    public function index()
    {
        $title = trans('teachergroup.mygroups');
        $studentGroups = $this->teacherSubjectRepository->getAllForSchoolYearAndTeacher(session('current_company_year'), session('current_company'), session('current_company_semester'), $this->user->id)
            ->with('subject')
            ->get();
        return view('teachergroup.mygroup', compact('title', 'studentGroups'));
    }





    public function store(Request $request)
    {
        $userInstance = 'registrations';
        $ids = $request['id'];
        $section_id = $request['section_id'];
        $mid_sems = $request['mid_sem'];
        $mid_sem1 = $request['mid_sem1'];
        $mid_sem2 = $request['mid_sem2'];
        $mid_sem3 = $request['mid_sem3'];
        $exams = $request['exams'];
        $otherOptions = $request['otherOptions'];
        $markS = new MarkValue();
        foreach ($ids as $index => $id) {
            if (empty($otherOptions[$index]) && !empty($mid_sems[$index]) OR !empty($mid_sem1[$index]) /*&& $mid_sems[$index] < 40.0001*/ && !empty($exams[$index]) && $exams[$index] < 60.0001)
            {
                $registration = Registration::find($id);
                $registration->exams = $exams[$index];
                if($section_id == 9)
                {
                    $registration->mid_sem1 = $mid_sem1[$index];
                    $registration->mid_sem2 = $mid_sem2[$index];
                    $registration->mid_sem3 = $mid_sem3[$index];
                    @$registration->mid_sem = number_format($mid_sem1[$index]+$mid_sem2[$index]+$mid_sem3[$index], 2);
                    if (!empty($otherOptions[$index]))
                    {
                        $registration = Registration::find($id);
                        $registration->mid_sem = null;
                        $registration->exams = null;
                        @$registration->exam_score = null;
                        @$registration->grade = $otherOptions[$index];
                    }

                }
                else
                {
                    $registration->mid_sem = $mid_sems[$index];
                }


                @$registration->exam_score = number_format($registration->exams+$registration->mid_sem, 2);

                @$registration->grade = $markS->getGrade($request->mark_system_id, $registration->exam_score)->grade;
                @$registration->credit = $markS->getGrade($request->mark_system_id, $registration->exam_score)->gpa;
                $registration->save();
            }

            elseif (!empty($otherOptions[$index]))
            {
                $registration = Registration::find($id);
                $registration->mid_sem = null;
                $registration->exams = null;
                @$registration->exam_score = null;
                @$registration->grade = $otherOptions[$index];
                $registration->save();
            }

        }
        Flash::success('Marks entered Successfully!');
        return redirect('/teachergroup/'.$request['subject_id'].'/'.$request->semester_id.'/mark');


    }


    public function generateCsvStudentsGroup(Subject $subject)
    {

        $students = $this->studentRepository->getAllRegistrationForSubject(session('current_company_year'), session('current_company_semester'), $subject->id)
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

        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }


    public function attendance(Subject $subject)
    {
        $title = trans('teachergroup.students');
        $id = $subject->id;
        $students = $this->studentRepository->getAllRegistrationForSubject(session('current_company_year'), session('current_company_semester'), $subject->id)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'sID' => isset($student->user) ? $student->sID : "",
                    'name' => isset($student->user) ? $student->user->full_name : "",
                ];
            })
            ->pluck('name', 'id')->toArray();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($session) {
                return [
                    'id'    => $session->id,
                    'title' => $session->name
                ];
            })->pluck('title', 'id')->prepend(trans('student.select_session'), 0)->toArray();;

        $attendance_type = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'attendance_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            })->pluck('title', 'value')->toArray();

        $hour_list = $this->timetableRepository->getAll()
            ->with('teacher_subject')
            ->get()
            ->map(function ($timetable) {
                return [
                    'id'   => $timetable->hour,
                    'hour' => $timetable->hour,
                ];
            })->pluck('hour', 'id')->toArray();

        return view('attendance.index', compact('title', 'id', 'students', 'sessions', 'attendance_type', 'hour_list', 'subject'));
    }

    public function mark(Request $request, Subject $subject)
    {
        $title    = trans('mark.marks');

        $semester    = $request->semester;

        $registrations = $this->registrationRepository->getAllStudentsForSemesterSubject(session('current_company'), session('current_company_year'), $semester, $subject->id)
            ->get();


        $markSystems = $this->markValueRepository->getAllForSubject( $subject->id)
            ->get();

        $otherOptions = $this->markValueRepository->getAllMarkValueOptions( $subject->id)
            ->get()
            ->map(function ($otherOption) {
                return [
                    'id'    => $otherOption->grade,
                    'title' => $otherOption->xdesc
                ];
            })->pluck('title', 'id')->prepend('Select', '')->toArray();



        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($session) {
                return [
                    'id'    => $session->id,
                    'title' => $session->name
                ];
            })->pluck('title', 'id')->prepend(trans('student.select_session'), 0)->toArray();

        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->user_id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')->toArray();


        return view('mark.index', compact('title', 'registrations',  'sessions', 'semester', 'subject', 'markSystems', 'otherOptions', 'students'));
    }


    public function addStudentToCourse(RegistrationRequest $request)
    {
        $semester = Semester::find($request->semester_id);

        foreach ($request['user_id'] as $user_id) {
            $student = Student::where('user_id', $user_id)->first();

            $user_exists = Registration::where('user_id', $user_id)
                ->where('semester_id', $request->semester_id)
                ->where('subject_id', '=', $request->subject_id)
                ->first();
            if (!isset($user_exists->id)) {

                $registration                   = new Registration();
                $registration->user_id          = $user_id;
                $registration->student_id       = $student->id;
                $registration->level_id         = $student->level_id;
                $registration->subject_id       = $request->subject_id;
                $registration->company_id        = session('current_company');
                $registration->school_year_id   = session('current_company_year');
                $registration->semester_id      = $request->semester_id;
                $registration->save();

            }
            StudentStatus::firstOrCreate(['company_id' => session('current_company'), 'school_year_id' => session('current_company_year'), 'semester_id' => $request->semester_id, 'student_id' => $student->id]);
        }



        /*$this->activity->record([
            'module'    => $this->module,
            'module_id' => $registration->id,
            'activity'  => 'created'
        ]);*/
        return redirect('/teachergroup/'.$request->subject_id.'/'.$request->semester_id.'/mark')->with('status', 'Registration Successfully!');
    }




    public function students(Subject $subject)
    {
        $title = trans('teachergroup.students');
        $id = $subject->id;
        $students = $this->studentRepository->getAllRegistrationForSubject(session('current_company_year'), session('current_company_semester'), $subject->id)
            ->get();
        return view('teachergroup.students', compact('title', 'subject', 'id', 'students'));
    }

    public function students_data(Subject $subject)
    {
        $students = $this->studentRepository->getAllRegistrationForSubject(session('current_company_year'), session('current_company_semester'), $subject->id)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'sID' => isset($student->user) ? $student->sID : "",
                    'full_name' => isset($student->user) ? $student->user->full_name : "",
                    'programme' => isset($student->programme) ? $student->programme->title : "",
                    'level' => isset($student->level) ? $student->level->name : "",
                    'session' => isset($student->session) ? $student->session->name : "",
                ];
            });


        return Datatables::make($students)
            ->addColumn('actions', '<!--<a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>-->
                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <!--<a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->')
            ->removeColumn('id')
            ->rawColumns([ 'actions' ])->make();
    }













    public function addstudents(StudentGroup $studentGroup, Request $request)
    {
        $studentGroup->students()->sync($request['students_select']);
        return redirect('/teachergroup');
    }

    public function grouptimetable(Direction $direction)
    {
        $title = trans('teachergroup.timetable');

        $school_year_id = session('current_company_year');

        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndDirection($school_year_id, $direction->id)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) &&
                    $teacherSubject->teacher_id == $this->user->id &&
                    isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => $teacherSubject->subject->title,
                    'name' => $teacherSubject->teacher->full_name,
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);

        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();
        return view('teachergroup.timetable', compact('studentGroup', 'timetablePeriods', 'title', 'section', 'subject_list', 'timetable'));
    }

    public function addtimetable(TimetableRequest $request)
    {
        $timetable = new Timetable($request->all());
        $timetable->save();

        return $timetable->id;
    }

    public function deletetimetable(DeleteRequest $request)
    {
        $timetable = Timetable::find($request['id']);
        if (!is_null($timetable)) {
            $timetable->delete();
        }
    }

    public function timetable()
    {
        $title = trans('teachergroup.timetable');

        $school_year_id = session('current_company_year');

        $studentgroups = new Collection([]);
        $studentGroupsList = $this->teacherSubjectRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->with('subject', 'subject.direction')
            ->get()
            ->each(function ($teacherSubject) {
                if ($teacherSubject->teacher_id == $this->user->id && $teacherSubject->subject->direction) {
                    return true;
                }
            })
            ->map(function ($studentGroup) {
                return [
                    'id' => $studentGroup->subject->direction->id,
                    'title' => $studentGroup->subject->direction->title,
                    'direction' => isset($studentGroup->subject->direction->title) ? $studentGroup->subject->direction->title : "",
                    "class" => $studentGroup->subject->class,
                ];
            })->toBase()->unique();
        foreach ($studentGroupsList as $items) {
            $studentgroups->push($items['id']);
        }
        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndDirections($school_year_id, $studentgroups)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) &&
                    $teacherSubject->teacher_id == $this->user->id &&
                    isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => $teacherSubject->subject->title,
                    'name' => $teacherSubject->teacher->full_name,
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);
        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();

        return view('teachergroup.timetable', compact(
            'title',
            'action',
            'subject_list',
            'timetable',
            'timetablePeriods'
        ));
    }

    public function print_timetable()
    {
        $title = trans('teachergroup.timetable');

        $school_year_id = session('current_company_year');

        $studentgroups = new Collection([]);
        $studentGroupsList = $this->teacherSubjectRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->with('subject', 'subject.direction')
            ->get()
            ->each(function ($teacherSubject) {
                if ($teacherSubject->teacher_id == $this->user->id && $teacherSubject->subject->direction) {
                    return true;
                }
            })
            ->map(function ($studentGroup) {
                return [
                    'id' => $studentGroup->subject->direction->id,
                    'title' => $studentGroup->subject->direction->title,
                    'direction' => isset($studentGroup->subject->direction->title) ? $studentGroup->subject->direction->title : "",
                    "class" => $studentGroup->subject->class,
                ];
            })->toBase()->unique();
        foreach ($studentGroupsList as $items) {
            $studentgroups->push($items['id']);
        }
        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndGroups($school_year_id, $studentgroups)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) &&
                    $teacherSubject->teacher_id == $this->user->id &&
                    isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => $teacherSubject->subject->title,
                    'name' => $teacherSubject->teacher->full_name,
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);

        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();

        $data = '<h1>' . $title . '</h1>
				<table style="border: double" class="table-bordered">
					<tbody>
					<tr>
						<th>#</th>
						<th width="14%">' . trans('teachergroup.monday') . '</th>
						<th width="14%">' . trans('teachergroup.tuesday') . '</th>
						<th width="14%">' . trans('teachergroup.wednesday') . '</th>
						<th width="14%">' . trans('teachergroup.thursday') . '</th>
						<th width="14%">' . trans('teachergroup.friday') . '</th>
                        <th width="14%">' . trans('teachergroup.saturday') . '</th>
                        <th width="14%">' . trans('teachergroup.sunday') . '</th>
					</tr>';
        if ($timetablePeriods->count() >0) {
            for ($i=0; $i<$timetablePeriods->count(); $i++) {
                $data .= '<tr>
            <td>' . $timetablePeriods[$i]['start_at'].' - '. $timetablePeriods[$i]['end_at'] . '</td>';
                for ($j = 1; $j < 8; $j ++) {
                    $data .= '<td>';
                    if ($timetablePeriods[$i]['title']=="") {
                        foreach ($timetable as $item) {
                            if ($item['week_day'] == $j && $item['hour'] == $i) {
                                $data .= '<div>
                            <span>' . $item['title'] . '</span>
                            <br>
                            <span>' . $item['name'] . '</span></div>';
                            }
                        }
                    } else {
                        $data .=$timetablePeriods[$i]['title'];
                    }
                    $data .= '</td>';
                }
                $data .= '</tr>';
            }
        } else {
            for ($i = 1; $i < 8; $i ++) {
                $data .= '<tr>
            <td>' . $i . '</td>';
                for ($j = 1; $j < 8; $j ++) {
                    $data .= '<td>';
                    foreach ($timetable as $item) {
                        if ($item['week_day'] == $j && $item['hour'] == $i) {
                            $data .= '<div>
                            <span>' . $item['title'] . '</span>
                            <br>
                            <span>' . $item['name'] . '</span></div>';
                        }
                    }
                    $data .= '</td>';
                }
                $data .= '</tr>';
            }
        }
        $data .= '</tbody>
				</table>';
        $pdf = PDF::loadView('report.timetable', compact('data'));
        return $pdf->stream();
    }
}
