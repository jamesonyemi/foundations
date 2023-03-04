<?php

namespace App\Http\Controllers\Secure;

use App\Exports\StudentsExport;
use App\Helpers\CustomFormUserFields;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\EnrollRequest;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\PaymentRequest;
use App\Http\Requests\Secure\SmsMessageRequest;
use App\Http\Requests\Secure\StudentImportRequest;
use App\Http\Requests\Secure\StudentNoteRequest;
use App\Http\Requests\Secure\StudentRequest;
use App\Http\Requests\Secure\UpgradeRequest;
use App\Models\Applicant;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FeeCategory;
use App\Models\FeesStatus;
use App\Models\GeneralLedger;
use App\Models\Invoice;
use App\Models\Letter;
use App\Models\RoleUser;
use App\Models\SmsMessage;
use App\Models\StudentAdmission;
use App\Models\StudentNote;
use App\Models\StudentStatus;
use App\Models\StudentUpgrade;
use App\Models\User;
use App\Models\UserDocument;
use App\Notifications\SendSMS;
use App\Repositories\ActivityLogRepository;
use App\Repositories\CampusRepository;
use App\Repositories\CountryRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\EntryModeRepository;
use App\Repositories\GraduationYearRepository;
use App\Repositories\IntakePeriodRepository;
use App\Repositories\LevelRepository;
use App\Repositories\MaritalStatusRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ReligionRepository;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\SessionRepository;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var DepartmentRepository
     */
    /**
     * @var DirectionRepository
     */
    private $directionRepository;

    /**
     * @var ProgrammeRepository
     */
    private $programmeRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var IntakePeriodRepository
     */
    private $intakePeriodRepository;

    /**
     * @var EntryModeRepository
     */
    private $entryModeRepository;

    /**
     * @var CampusRepository
     */
    private $campusRepository;

    /**
     * @var CampusRepository
     */
    private $countryRepository;

    /**
     * @var CampusRepository
     */
    private $maritalStatusRepository;

    /**
     * @var CampusRepository
     */
    private $semesterRepository;

    private $religionRepository;

    private $schoolYearRepository;

    private $graduationYearRepository;

    private $sessionRepository;

    protected $activity;

    protected $module = 'Student';

    /**
     * StudentController constructor.
     * @param StudentRepository $studentRepository
     * @param OptionRepository $optionRepository
     * @param ExcelRepository $excelRepository
     * @param SectionRepository $sectionRepository
     * @param ProgrammeRepository $programmeRepository
     * @param LevelRepository $levelRepository
     * @param EntryModeRepository $EntryModeRepository
     * @param ProgrammeRepository $ProgrammeRepository
     * @param CampusRepository $CampusRepository
     * @param CountryRepository $CountryRepository
     */
    public function __construct(
        StudentRepository $studentRepository,
        OptionRepository $optionRepository,
        LevelRepository $levelRepository,
        EntryModeRepository $entryModeRepository,
        IntakePeriodRepository $intakePeriodRepository,
        CampusRepository $campusRepository,
        CountryRepository $countryRepository,
        MaritalStatusRepository $maritalStatusRepository,
        ReligionRepository $religionRepository,
        DirectionRepository $directionRepository,
        SchoolYearRepository $schoolYearRepository,
        GraduationYearRepository $graduationYearRepository,
        SemesterRepository $semesterRepository,
        SessionRepository $sessionRepository,
        SectionRepository $sectionRepository,
        ActivityLogRepository $activity
    ) {
        parent::__construct();
        $this->studentRepository = $studentRepository;
        $this->optionRepository = $optionRepository;
        $this->sectionRepository = $sectionRepository;
        $this->levelRepository = $levelRepository;
        $this->entryModeRepository = $entryModeRepository;
        $this->intakePeriodRepository = $intakePeriodRepository;
        $this->campusRepository = $campusRepository;
        $this->countryRepository = $countryRepository;
        $this->maritalStatusRepository = $maritalStatusRepository;
        $this->religionRepository = $religionRepository;
        $this->directionRepository = $directionRepository;
        $this->schoolYearRepository = $schoolYearRepository;
        $this->graduationYearRepository = $graduationYearRepository;
        $this->semesterRepository = $semesterRepository;
        $this->sessionRepository = $sessionRepository;
        $this->activity = $activity;

        $this->middleware('authorized:student.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'invoice');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Invoice';

        $general_ledger = GeneralLedger::where('credit', '>', 0)->with('student.user', 'student.programme')->get();

        $students = $this->studentRepository->getAllForSchool(session('current_company'))
            ->with('user', 'programme')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.' '.$item->sID : '',
                ];
            })->pluck('name', 'id')
            ->prepend(trans('account.select_student'), '')
            ->toArray();

        $maleStudents = $this->studentRepository
            ->getAllMale()
            ->get();

        $femaleStudents = $this->studentRepository
            ->getAllFemale()
            ->get();
        $this->filterParams();
        $schoolType = session('current_company_type');

        $sections2 = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        $directions2 = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), '')
            ->toArray();

        $levels2 = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $sessions2 = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_session'), '')
            ->toArray();

        $graduationyears2 = $this->graduationYearRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_graduation_year'), '')
            ->toArray();

        $schoolYears2 = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school_year'), '')
            ->toArray();

        $semesters2 = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'title' => isset($item) ? $item->title.' '.$item->school_year->title : '',
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('student.select_semester'), '')
            ->toArray();

        $count = 1;

        return view('invoice.index', compact('title', 'maleStudents', 'femaleStudents', 'sections2', 'sessions2', 'semesters2', 'directions2', 'levels2', 'students', 'general_ledger', 'schoolYears2', 'count'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param StudentRequest $request
     * @return Response
     */
    public function store(PaymentRequest $request)
    {
        DB::beginTransaction();

        foreach ($request['student_id'] as $student_id) {
            $student = Employee::find($student_id);

            $generalLedger = new GeneralLedger();
            $generalLedger->student_id = $student->id;
            $generalLedger->user_id = Sentinel::getUser()->id;
            $generalLedger->company_id = session('current_company');
            $generalLedger->company_year_id = session('current_company_year');
            $generalLedger->semester_id = session('current_company_semester');
            $generalLedger->narration = $request->narration;
            @$generalLedger->account_id = $student->section->debit_account_id;
            $generalLedger->credit = $request->amount;
            $generalLedger->transaction_date = now();
            $generalLedger->save();
        }

        DB::commit();
        if ($generalLedger->save()) {
            /*MAKE STUDENT ACTIVE*/
            /*StudentStatus::firstOrCreate(['company_id' => session('current_company'),
                'company_year_id' => session('current_company_year'),
                'semester_id' => session('current_company_semester'),
                'student_id' => $student->id]);*/

            return response('<div class="alert alert-success">
                 Selected Students Billed Successfully
                 </div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $student
     * @return Response
     */
    private function filterParams()
    {
        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $graduationyears = $this->graduationYearRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $entrymodes = $this->entryModeRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $intakeperiods = $this->intakePeriodRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $campus = $this->campusRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $countries = $this->countryRepository
            ->getAll()
            ->get();

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get();

        $religions = $this->religionRepository
            ->getAll()
            ->get();

        view()->share('schoolyears', $schoolyears);
        view()->share('graduationyears', $graduationyears);
        view()->share('semesters', $semesters);
        view()->share('directions', $directions);
        view()->share('sections', $sections);
        view()->share('sessions', $sessions);
        view()->share('levels', $levels);
        view()->share('entrymodes', $entrymodes);
        view()->share('campus', $campus);
        view()->share('intakeperiods', $intakeperiods);
        view()->share('countries', $countries);
        view()->share('countries2', $countries);
        view()->share('maritalStatus', $maritalStatus);
        view()->share('religions', $religions);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Employee $student
     * @return Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param StudentRequest $request
     * @param Employee $student
     * @return Response
     */

    /**
     * @param Employee $student
     * @return Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param Employee $student
     * @return Response
     */
    public function getImport()
    {
        $title = trans('student.import_student');

        return view('student.import', compact('title'));
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/students.xlsx'));
    }

    public function studentFilter(Request $request)
    {
        $students = $this->studentRepository->getAllInvoiceFilter($request)->get();

        return view('invoice.allFilteredList', ['students' => $students], ['count' => '1']); //
    }

    public function ajaxSMS(SmsMessageRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $school = Company::find(session('current_company'))->first();
                if ($school->limit_sms_messages == 0 ||
                    $school->limit_sms_messages > $school->sms_messages_year) {
                    $user = User::find($request->muser_id);
                    if (! is_null($user) && $user->mobile != '') {
                        $user->notify(new SendSMS($user, $request));

                        $smsMessage = new SmsMessage();
                        $smsMessage->text = $request->text;
                        $smsMessage->number = $user->mobile;
                        $smsMessage->user_id = $request->muser_id;
                        $smsMessage->user_id_sender = $this->user->id;
                        $smsMessage->company_id = session('current_company');
                        $smsMessage->save();
                    }
                }
            });
        } catch (\Exception $e) {
            return $e;
        }

        $applicant = Applicant::where('user_id', $request->muser_id)->first();
        $count = 1;

        return view('applicant.messages', compact('count', 'applicant'));
    }

    public function generalExport(Request $request)
    {
        $students = $this->studentRepository->getAllActiveFilter($request);

        return Excel::download(new StudentsExport($students), 'students.xlsx');
    }
}
