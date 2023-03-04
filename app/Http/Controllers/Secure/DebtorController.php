<?php

namespace App\Http\Controllers\Secure;

use App\Events\MessageCreated;
use App\Http\Requests\Secure\DebtorRequest;
use App\Models\Message;
use App\Models\School;
use App\Models\SmsMessage;
use App\Models\User;
use App\Repositories\GraduationYearRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SessionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\IntakePeriodRepository;
use App\Repositories\EntryModeRepository;
use App\Repositories\CampusRepository;
use App\Repositories\CountryRepository;
use App\Repositories\MaritalStatusRepository;
use App\Repositories\ReligionRepository;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SemesterRepository;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Settings;

class DebtorController extends SecureController
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

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


    /**
     * InvoiceController constructor.
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
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
        SectionRepository $sectionRepository
    ) {

        parent::__construct();

        $this->invoiceRepository = $invoiceRepository;
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

        $this->middleware('authorized:debtor.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:debtor.create', ['only' => ['create', 'store']]);

        view()->share('type', 'debtor');

        $columns = ['sID','name', 'section', 'amount'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('debtor.debtor');

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $schoolyears = $this->schoolYearRepository
            ->getAll()
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

        $countries2 = $this->countryRepository
            ->getCountryStudents()
            ->get();

        $graduationyears = $this->graduationYearRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get();

        $religions = $this->religionRepository
            ->getAll()
            ->get();


        return view('debtor.index', compact('title', 'sections', 'sessions', 'schoolyears', 'graduationyears', 'semesters', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'countries2', 'maritalStatus', 'religions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('debtor.new');
        $one_school = (Settings::get('account_one_school')=='yes')?true:false;
        if ($one_school &&  $this->user->inRole('accountant')) {
            $debtors = $this->invoiceRepository->getAllDebtorStudentsForSchool(session('current_company'));
        } else {
            $debtors = $this->invoiceRepository->getAllDebtor();
        }
        $debtors = $debtors->with('user')
            ->get()
            ->map(function ($debtor) {
                return [
                    "id" => $debtor->user_id,
                    "name" => isset($debtor->user) ? $debtor->user->full_name : "",
                ];
            })->pluck('name', 'id');

        return view('layouts.create', compact('title', 'debtors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(DebtorRequest $request)
    {
        foreach ($request['user_id'] as $item) {
            $user = User::find($item);

            if ($request->sms_email == 1) {
                $school = School::find(session('current_company'))->first();
                if ($school->limit_sms_messages == 0 ||
                   $school->limit_sms_messages > $school->sms_messages_year) {
                    $smsMessage                 = new SmsMessage();
                    $smsMessage->text           = $request->message;
                    $smsMessage->number         = $user->mobile;
                    $smsMessage->user_id        = $user->id;
                    $smsMessage->user_id_sender = $this->user->id;
                    $smsMessage->company_id      = session('current_company');
                    $smsMessage->save();
                }
            } else {
                $email = new Message();
                $email->to = $item;
                $email->from = $this->user->id;
                $email->message = $request->message;
                $email->subject = trans('debtor.debtor_message');
                $email->save();

                event(new MessageCreated($email));
            }
        }
        return redirect('/debtor');
    }

    public function data()
    {
        /*$one_school = (Settings::get('account_one_school')=='yes')?true:false;
	    if($one_school &&  $this->user->inRole('accountant')){*/
            $debtors = $this->invoiceRepository->getAllDebtorStudentsForSchool(session('current_company'));
        /*}else{
		    $debtors = $this->invoiceRepository->getAllDebtor();
	    }*/
        $debtors = $debtors->with('user')
            ->orderBy('invoices.total_fees', 'DESC')
            ->get()
            ->map(function ($debtor) {
                return [
                    "id" => $debtor->id,
                    'sID' => isset($debtor->student) ? $debtor->student->sID : "",
                    "name" => isset($debtor->user) ? $debtor->user->full_name : "",
                    'section' => isset($debtor->student) ? $debtor->student->section->title : "",
                    "amount" => $debtor->amount,
                ];
            });
        return Datatables::make($debtors)
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
