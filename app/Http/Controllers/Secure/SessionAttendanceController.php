<?php

namespace App\Http\Controllers\Secure;

use App\Models\ConferenceDay;
use App\Models\ConferenceSession;
use App\Models\Employee;
use App\Models\ProcurementCategory;
use App\Models\Section;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Repositories\ConferenceDayRepository;
use App\Repositories\ConferenceSessionRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SessionAttendanceRepository;
use DB;
use Illuminate\Http\Request;
use PDF;
use Sentinel;

class SessionAttendanceController extends SecureController
{
    /**
     * @var SessionAttendanceRepository
     */
    private $sessionAttendanceRepository;

    /**
     * @var SectionRepository
     */
    private $conferenceDayRepository;

    /**
     * @var SectionRepository
     */
    private $conferenceSessionRepository;

    /**
     * @var SectionRepository
     */
    private $employeeRepository;

    private $sectionRepository;

    /**
     * SectionController constructor.
     * @param SectionRepository $sectionRepository
     * @param EmployeeRepository $employeeRepository
     */
    public function __construct(SectionRepository $sectionRepository,
                                SessionAttendanceRepository $sessionAttendanceRepository,
                                ConferenceSessionRepository $conferenceSessionRepository,
                                ConferenceDayRepository $conferenceDayRepository,
                                EmployeeRepository $employeeRepository)
    {
        parent::__construct();

        $this->sectionRepository = $sectionRepository;
        $this->sessionAttendanceRepository = $sessionAttendanceRepository;
        $this->conferenceSessionRepository = $conferenceSessionRepository;
        $this->conferenceDayRepository = $conferenceDayRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'session_attendance');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = trans('registration.attendance');

        $activeSession = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first()->title;

        $did = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first();

        $activeDay = ConferenceDay::where('id', $did->conference_day_id)->first()->title;

        $students2 = $this->employeeRepository->getAllForSchoolYearAndSchool(session('current_company_year'), $this->currentEmployee->center_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => (isset($item->user) ? $item->user->full_name : '').'-'.$item->company->title.'-'.str_pad($item->sID, 4, '0', STR_PAD_LEFT),
                ];
            })->pluck('name', 'id')
            ->prepend('Select Participant', 0)
            ->toArray();

        return view('session_attendance.index', compact('title', 'activeDay', 'activeSession', 'students2'));
    }

    public function indexList()
    {
        $title = trans('registration.attendance');

        $activeSession = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first()->title;

        $did = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first();

        $activeDay = ConferenceDay::where('id', $did->conference_day_id)->first()->title;

        $registrations = $this->employeeRepository->getAllSessionAttendance(session('current_company_year'), $this->currentEmployee->center_id, $did->id);

        $data = $registrations->with('user', 'company')->get();

        return view('session_attendance.index_list', compact('title', 'activeDay', 'activeSession', 'data', 'did'));
    }

    public function indexListAll()
    {
        $title = trans('registration.attendance');

        $students = $this->employeeRepository->getAllForSchoolYearAndSchool(session('current_company_year'), $this->currentEmployee->center_id)
            ->get();

        $activeSession = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first()->title;

        $did = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first();

        $activeDay = ConferenceDay::where('id', $did->conference_day_id)->first()->title;

        $registrations = $this->employeeRepository->getAllSessionAttendanceAll(session('current_company_year'), $did->id);

        $data = $registrations->with('user', 'company')->get();

        return view('session_attendance.index_list_all', compact('title', 'students', 'activeDay', 'activeSession', 'data', 'did'));
    }

    public function show($no)
    {
        try {
            $employee = Employee::where('sID', '=', $no)->first();

            if ($employee->active->first()->center_id != $this->currentEmployee->center_id) {
                return response()->json(['exception'=>'Participant was not registered for this center']);
            }

            StudentStatus::firstOrCreate(
            [
                'company_year_id' => session('current_company_year'),
                'employee_id' => $employee->id,
            ],
            [
                'attended' => 1,
                'attended_date' => now(),
            ]
        );

            $activeSession = ConferenceSession::where('company_year_id', session('current_company_year'))->where('active', 'Yes')->first();

            SessionAttendance::firstOrCreate(
            [
                'company_year_id' => session('current_company_year'),
                'conference_day_id' => @$activeSession->conference_day_id,
                'conference_session_id' => @$activeSession->id,
                'employee_id' => $employee->id,
                'center_id' => $this->currentEmployee->center_id,
            ]
        );
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return view('session_attendance.register', compact('employee'));
    }

    public function show2(Employee $employee)
    {
        $title = trans('student.details');

        $activeSession = ConferenceSession::where('company_year_id',
            session('current_company_year'))
            ->where('active', 'Yes')->first();

        /*StudentStatus::updateOrCreate(
            [
                'company_year_id' => session('current_company_year'),
                'employee_id' => $employee->id,
                'center_id' => $this->currentEmployee->center_id
            ],
            [
                'attended' => 1,
                'attended_date' => now()
            ]
        );*/

        SessionAttendance::firstOrCreate(
            [
                'company_year_id' => session('current_company_year'),
                'conference_day_id' => @$activeSession->conference_day_id,
                'conference_session_id' => @$activeSession->id,
                'employee_id' => $employee->id,
                'center_id' => $this->currentEmployee->center_id,
            ]
        );

        return view('session_attendance.register_attendance', compact('employee', 'title'));
    }

    public function outside(Request $request)
    {
        $title = trans('student.details');
        $action = 'show';

        $student = Student::where('student_no', '=', $request->data)->first();

        if ($student->attended != '1') {
            $student->attended = '1';
            $student->arrival_date = now();
            $student->save();
        }

        $activeSession = ConferenceSession::where('school_year_id', session('current_company_year'))->where('active', 'Yes')->first();

        SessionAttendance::firstOrCreate(['company_id' => session('current_company'), 'school_year_id' => session('current_company_year'), 'conference_day_id' => @$activeSession->conference_day_id, 'conference_session_id' => @$activeSession->id, 'student_id' => $student->id]);

        return ''.$student->full_name.'';
    }

    public function attend(Student $student)
    {
        $activeSession = ConferenceSession::where('school_year_id',
            session('current_company_year'))
            ->where('active', 'Yes')->first();
        $studentStatus = StudentStatus::where('student_id', $student->id)
            ->where('school_year_id', session('current_company_year'))->first();
        if (is_null($studentStatus)) {
            $studentStatus->attended = '1';
            $studentStatus->attended_date = now();
            $studentStatus->save();

            SessionAttendance::firstOrCreate(['company_id' => session('current_company'),
                'school_year_id' => session('current_company_year'),
                'conference_day_id' => @$activeSession->conference_day_id,
                'conference_session_id' => @$activeSession->id,
                'student_id' => $student->id, ]);
        } else {
            SessionAttendance::firstOrCreate(['company_id' => session('current_company'),
                'school_year_id' => session('current_company_year'),
                'conference_day_id' => @$activeSession->conference_day_id,
                'conference_session_id' => @$activeSession->id,
                'student_id' => $student->id, ]);
        }

//        SessionAttendance::firstOrCreate(['company_id' => session('current_company'), 'school_year_id' => session('current_company_year'), 'conference_day_id' => @$activeSession->conference_day_id, 'conference_session_id' => @$activeSession->id, 'student_id' => $student->id]);
        return view('attended.index', compact('title', 'sectionsChart', 'maleStudents', 'femaleStudents'))
            ->with('status', 'Attendance Recorded Successfully!');
    }

    public function getAttendanceChart()
    {
        SessionAttendance::with('student', 'conference_session', 'conference_day')
            ->get()->take();
    }

    public function delete(SessionAttendance $sessionAttendance)
    {
        try {
            $sessionAttendance->delete();
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('Attendance Registration Deleted Successfully');
    }

    public function printBarcodePage()
    {
        //return "Hahahahahha";
        $title = trans('section.section');

        $sectionsChart = $this->sectionRepository
            ->getAllForSchoolYearSchoolChart(session('current_company_year'), session('current_company'))
            ->select('title', 'id')
            ->get();

        $data = $this->sectionRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->with('teacher')
            ->get();
        $sections = Section::where('company_id', session('current_company'))
//            ->where('school_year_id',2)
//            ->where('school_year_id',session('current_company_year'))
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->title,
                ];
            })->pluck('name', 'id')
//            ->prepend( trans( 's.select_participants' ), 0 )
            ->toArray();

        return view('schools.print_codes', compact('title', 'sectionsChart', 'data', 'sections'));
    }

    // new updates
    public function processBarcodePrinting(Request $request)
    {
        return $section = Student::with(['user', 'section', 'active:id,confirm'])
            ->whereHas('active', function ($query) {
                $query->where('confirm', 1)
                    ->where('company_id', session('current_company'))
                    ->where('school_year_id', session('current_company_year'));
            })
            ->where('section_id', $request->input('section'))
            ->get();

        return view('schools.generateSheet');
    }

    public function printIndividualPage($id)
    {
        $section = Employee::with('user')->whereHas('active', function ($query) use ($id) {
            $query->where('student_statuses.center_id', $id);
        })->get();

        return view('printer.generateSheet', compact('section'));
    }

    public function printParticipantBarcode($id)
    {
        $student = Student::with('user')
            ->where('student_no', $id)->get();

        return view('printer.printParticipantDetails', compact('student'));
    }
}
