<?php
namespace App\Http\Controllers\Secure;

use App\Exports\StudentsExport;
use App\Helpers\CustomFormUserFields;
use App\Helpers\Flash;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\StudentImportRequest;
use App\Helpers\Thumbnail;
use App\Mail\Discount;
use App\Models\FeeCategory;
use App\Models\GeneralLedger;
use App\Models\Student;
use App\Models\UserDocument;
use App\Repositories\OptionRepository;
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
use App\Repositories\GraduationYearRepository;
use App\Repositories\SemesterRepository;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Sentinel;
use App\Http\Requests\Secure\StudentRequest;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class StudentAdmissionController extends SecureController
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



    /**
     * StudentController constructor.
     * @param StudentRepository $studentRepository
     * @param OptionRepository $optionRepository
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
        SectionRepository $sectionRepository
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

        $this->middleware('authorized:student.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'admission');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('student.admitted_students');

        $maleStudents = $this->studentRepository
            ->getAllAdmittedForSchoolMale(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->get();

        $femaleStudents = $this->studentRepository
            ->getAllAdmittedForSchoolFemale(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->get();

        $students = $this->studentRepository->getAllAdmittedForSchool(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'programme')
            ->get();

        $this->filterParams();


        return view('admission.index', compact('title', 'maleStudents', 'femaleStudents', 'students'));
    }




    public function latestAdmissions()
    {
        /*if (!Sentinel::hasAccess('applicant.list')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }*/
        $title = trans('applicant.applicants');

        $count = 1;

        $admittedStudents = $this->studentRepository->getAllAdmittedForSchoolLatest(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'programme')
            ->take(10)
            ->get();


        return view('admission.recent_admissions', compact('title','admittedStudents'));
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

    /**
     * Display the specified resource.
     *
     * @param Student $student
     * @return Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param Student $student
     * @return Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param StudentRequest $request
     * @param Student $student
     * @return Response
     */

    /**
     * @param Student $student
     * @return Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param Student $student
     * @return Response
     */




    public function findDirectionName(Request $request)
    {
        $directions = $this->directionRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();
        return $directions;
    }




    public function findSectionCurrencyStudents(Request $request)
    {
        $students = $this->studentRepository->getAllForSectionCurrency($request->section_id, $request->currency_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->user_id,
                    "sid"   => $item->sID,
                    "name" => isset($item->user) ? $item->user->full_name. ' ' .$item->sID : "",
                ];
            })->pluck("name", 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        return $students;
    }



    public function findSectionStudents(Request $request)
    {
        $students = $this->studentRepository->getAllForSection2($request->section_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->user_id,
                    "sid"   => $item->sID,
                    "name" => isset($item->user) ? $item->user->full_name. ' ' .$item->sID : "",
                ];
            })->pluck("name", 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        return $students;
    }





    public function findDirectionStudents(Request $request)
    {
        $students = $this->studentRepository->getAllForDirection($request->direction_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->user_id,
                    "sid"   => $item->sID,
                    "name" => isset($item->user) ? $item->user->full_name. ' ' .$item->sID : "",
                ];
            })->pluck("name", 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        return $students;
    }


    public function findDirectionCurrencyStudents(Request $request)
    {
        $students = $this->studentRepository->getAllForDirectionCurrency($request->direction_id, $request->currency_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->user_id,
                    "sid"   => $item->sID,
                    "name" => isset($item->user) ? $item->user->full_name. ' ' .$item->sID : "",
                ];
            })->pluck("name", 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        return $students;
    }





    private function filterParams()
    {

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->with('admission')
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
        $students = $this->studentRepository->getAllAdmittedForSchoolFilter($request)->get();
        return view('student.allFilteredList', ['students' => $students], ['count' => '1']);//
    }



    public function webService(Request $request)
    {
        /*$body['title'] = "Body Title";
        $body['content'] = "Body Description";


        $client = new Client();
        $url = "http://wp.dev/index.php/wp-json/wp/v2/posts";


        $response = $client->createRequest("POST", $url, ['auth' => ['root','root'],'body'=>$body]);


        $response = $client->send($response);


        dd($response);*/


        $client = new Client();
        $res = $client->request('GET', 'https://www.thecocktaildb.com/api/json/v1/1/search.php?s=margarita', [
            'auth' => ['user', 'pass']
        ]);

        return $res;
    }


    public function generalExport(Request $request)
    {
        $students = $this->studentRepository->getAllAdmittedForSchoolExport($request);
        return Excel::download(new StudentsExport($students), 'students.xlsx');

    }

    public function bulkEmail()
    {
        $students = $this->studentRepository->getAllAdmittedForAward(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user')
            ->get();

        $count = 0;

        foreach ($students as $student)
        {

            /*Mail::to($student->user->email)->queue(new Discount($student));*/

            $data = [
                'email' => $student->user->email,
                'email2' => $student->user->email2,
                'name' => $student->user->full_name,
                'id' => $student->sID
            ];
            @Mail::send('emails.discount', $data, function (\Illuminate\Mail\Message $message) use ($data)
            {
                /* "Required" (It's self explaining ;)) */
                @$message->to($data['email'], $data['name']);

                /* Optional */
                @$message->from('finance@duc.edu.gh', 'DOMINION UNIVERSITY COLLEGE.');
                @$message->sender('finance@duc.edu.gh', 'DOMINION UNIVERSITY COLLEGE.');
                if (!empty($data['email2']))
                {
                @$message->cc($data['email2'], $data['name']);
                }
                @$message->replyTo('finance@duc.edu.gh');
                @$message->subject('DUC TUITION FEE DISCOUNT AWARD');
            });

            $count  ++;

        }
    dd($count);
    }


    public function discountAward(Request $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
        $student = Student::find($request->student_id);
        $fees = FeeCategory::where('section_id', $student->section_id)
            ->where('company_id', '=', session('current_company'))->first();
        $discountAmount = number_format(( 20 / 100 ) * $fees->local_amount,2);

        $generalLedger = new GeneralLedger();
        $generalLedger->student_id = $student->id;
        $generalLedger->user_id = $student->user_id;
        $generalLedger->company_id = session('current_company');
        $generalLedger->school_year_id = session('current_company_year');
        $generalLedger->semester_id = session('current_company_semester');
        $generalLedger->narration = '20% Discount';
        $generalLedger->account_id = $fees->discount_account_id;
        $generalLedger->debit = $discountAmount;
        $generalLedger->fee_category_id = $fees->id;
        $generalLedger->transaction_date = now();
        $generalLedger->transaction_type = 'debit';
        $generalLedger->save();

        $student->discount = 1;
        $student->save();

            });
        }


        catch (\Exception $e) {
        return $e;
        }

        return '20%  Operation Successful';


    }

    public function discountAwardAll(Request $request)
    {

        $students = $this->studentRepository->getAllAdmittedForAward(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->get();

        $count = 0;
        try
        {
            DB::transaction(function() use ($students, $count) {
        foreach ($students as $student)
        {

            $fees = FeeCategory::where('section_id', $student->section_id)
                ->where('company_id', '=', session('current_company'))->first();
            $discountAmount = number_format(( 20 / 100 ) * $fees->local_amount,2);

            $generalLedger = new GeneralLedger();
            $generalLedger->student_id = $student->id;
            $generalLedger->user_id = $student->user_id;
            $generalLedger->company_id = session('current_company');
            $generalLedger->school_year_id = session('current_company_year');
            $generalLedger->semester_id = session('current_company_semester');
            $generalLedger->narration = '20% Discount';
            $generalLedger->account_id = $fees->discount_account_id;
            $generalLedger->debit = $discountAmount;
            $generalLedger->fee_category_id = $fees->id;
            $generalLedger->transaction_date = now();
            $generalLedger->transaction_type = 'debit';
            $generalLedger->save();

            $student->discount = 1;
            $student->save();


            $count++;
            }

            });
        }


        catch (\Exception $e) {
            return $e;
        }

        return '20%  Discount Applied to '.$students->count().' Students';

    }


}
