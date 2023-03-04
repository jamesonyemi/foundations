<?php

namespace App\Http\Controllers\Secure;

use App\Models\Registration;
use App\Models\Student;
use App\Models\Semester;
use App\Models\FeesStatus;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use App\Repositories\FeeCategoryRepository;
use App\Repositories\RegistrationRepository;
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
use App\Repositories\SubjectRepository;
use App\Repositories\OptionRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests\Secure\RegistrationRequest;
use PDF;

class BroadSheetController extends SecureController
{
    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var SectionRepository
     */
    private $subjectRepository;
    /**
     * @var FeeCategoryRepository
     */
    private $feeCategoryRepository;
    /**
     * @var OptionRepository
     */

    private $optionRepository;

    /**
     * @var DirectionRepository
     */
    private $directionRepository;
    /**
     * @var DirectionRepository
     */
    private $levelRepository;

    /**
     * @var ProgrammeRepository
     */
    private $programmeRepository;

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
    private $sessionRepository;
    protected $activity;
    protected $module = 'Registration';


    /**
     * RegistrationController constructor.
     *
     * @param RegistrationRepository $registrationRepository
     * @param StudentRepository $studentRepository
     * @param FeeCategoryRepository $feeCategoryRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        StudentRepository $studentRepository,
        RegistrationRepository $registrationRepository,
        SubjectRepository $subjectRepository,
        FeeCategoryRepository $feeCategoryRepository,
        DirectionRepository $directionRepository,
        OptionRepository $optionRepository,
        LevelRepository $levelRepository,
        EntryModeRepository $entryModeRepository,
        IntakePeriodRepository $intakePeriodRepository,
        CampusRepository $campusRepository,
        CountryRepository $countryRepository,
        MaritalStatusRepository $maritalStatusRepository,
        ReligionRepository $religionRepository,
        SchoolYearRepository $schoolYearRepository,
        SemesterRepository $semesterRepository,
        SessionRepository $sessionRepository,
        SectionRepository $sectionRepository,
        ActivityLogRepository $activity

    ) {
        parent::__construct();

        $this->registrationRepository     = $registrationRepository;
        $this->studentRepository          = $studentRepository;
        $this->subjectRepository          = $subjectRepository;
        $this->feeCategoryRepository      = $feeCategoryRepository;
        $this->optionRepository           = $optionRepository;
        $this->sectionRepository          = $sectionRepository;
        $this->levelRepository            = $levelRepository;
        $this->directionRepository        = $directionRepository;
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
        $this->semesterRepository = $semesterRepository;
        $this->sessionRepository = $sessionRepository;
        $this->activity = $activity;

        $this->middleware('authorized:registration.show', [ 'only' => [ 'index', 'data' ] ]);
        $this->middleware('authorized:registration.create', [ 'only' => [ 'create', 'store' ] ]);
        $this->middleware('authorized:registration.edit', [ 'only' => [ 'update', 'edit' ] ]);
        $this->middleware('authorized:registration.delete', [ 'only' => [ 'delete', 'destroy' ] ]);

        view()->share('type', 'registration');


    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $title = trans('registration.registration');

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $subjects = $this->subjectRepository
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

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get();

        $religions = $this->religionRepository
            ->getAll()
            ->get();

        $schoolType = session('current_company_type');

        $count = 1;

        $students = $this->studentRepository->getAllRegistration(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user')
            ->get();


        return view('registration.index', compact('title',  'sections', 'sessions', 'schoolyears', 'semesters', 'directions','subjects', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'countries2', 'maritalStatus', 'religions',  'schoolType', 'count', 'students'));
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $title = trans('registration.new');
        $this->generateParams();

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();


        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();


        $subjects = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->title. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'sections', 'directions', 'levels', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RegistrationRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(RegistrationRequest $request)
    {

        foreach ($request['user_id'] as $user_id) {
            $student = Student::where('user_id', $user_id)
                ->first();

            $semester = Semester::where('active', 'Yes')
                ->where('company_id', '=', session('current_company'))->first();

            $user_exists = Registration::where('user_id', $user_id)
                         ->where('school_year_id', session('current_company_year'))
                         ->where('semester_id', session('current_company_semester'))
                         ->where('company_id', '=', session('current_company'))
                         ->where('subject_id', '=', $request->subject_id)
                         ->first();
            if (!isset($user_exists->id)) {
                foreach ($request['subject_id'] as $subject_id) {
                    $registration                   = new Registration($request->except('section_id', 'direction_id'));
                    $registration->user_id          = $user_id;
                    $registration->student_id       = $student->id;
                    $registration->subject_id       = $subject_id;
                    $registration->company_id        = session('current_company');
                    $registration->school_year_id = session('current_company_year');
                    $registration->semester_id      = session('current_company_semester');
                    $registration->save();
                }
            }
        }

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $registration->id,
            'activity'  => 'created'
        ]);
        return redirect('/registration')->with('status', 'Registration Successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function show(Registration $registration)
    {
        $pdf = PDF::loadView('report.invoice', compact('registration'));
        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function edit(Registration $registration)
    {
        $title = trans('registration.edit');
        $this->generateParams();

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();


        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();

        $subjects = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->title. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'registration', 'sections', 'directions', 'levels', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RegistrationRequest $request
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function update(RegistrationRequest $request, Registration $registration)
    {


        $registration->update($request->except('id', 'section_id', 'direction_id'));
        $id = $registration->student_id;

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $registration->id,
            'activity'  => 'Updated'
        ]);


        return redirect(url('/registration/'.$id.'/courses'))->with('status', 'Registration Updated Successfully!');;
    }

    /**
     *
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function delete(Registration $registration)
    {
        $title = trans('registration.delete');

        return view('/registration/delete', compact('registration', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function destroy(Registration $registration)
    {
        $registration->delete();
        $id = $registration->user_id;

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $registration->id,
            'activity'  => 'Deleted'
        ]);

        return redirect(url('/registration/'.$id.'/courses'))->with('status', 'Registration Course Deleted Successfully!');;
    }






    public function data()
    {
        $students = $this->studentRepository->getAllRegistration(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user')
                             ->get()
                             ->map(function ($student) {
                                 return [
                                     "user_id" => $student->id,
                                     "id" => $student->registration[0]->id,
                                     "sID" => isset($student->user) ? $student->sID : "",
                                     "full_name" => isset($student->user) ? $student->user->full_name : "",
                                     "level" => @$student->registration[0]->level->name,
                                     "date" => $student->registration[0]->created_at->format(Settings::get('date_format')),

                                 ];
                             });

        return Datatables::make($students)
                          ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'invoice.edit\', Sentinel::getUser()->permissions)))

                                     <a href="{{ url(\'/student/\' . $user_id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>

                                    <a href="{{ url(\'/registration/\' .$user_id. \'/courses\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("registration.courses") }}</a>
                                    @endif
                                    <!--<a target="_blank" href="{{ url(\'/registration/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>-->
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'invoice.delete\', Sentinel::getUser()->permissions)))
                                     <!--<a href="{{ url(\'/invoice/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->
                                     @endif')
                          ->removeColumn('user_id')
                          ->rawColumns([ 'actions' ])->make();
    }

    public function courses(Student $registration)
    {

        $id = $registration->id;
        $title = $registration->user->full_name. '  '. ' | ' .@$registration->programme->title. '  '. ' | ' .@$registration->level->name;


        return view('registration.courses', compact('title', 'id'));
    }


    public function studentFilter(Request $request)
    {
        $students = $this->studentRepository->getAllRegistrationFilter($request)->get();


        return view('registration.allFilteredList', ['students' => $students], ['count' => '1']);//
    }


    public function courses_data(Student $registration)
    {
        $studentCourses = $this->registrationRepository->getAllForStudent($registration->user_id)
            ->get()
            ->map(function ($studentCourse) {
                return [
                    "cid" => $studentCourse->subject->id,
                    "id" => $studentCourse->id,
                    "subject" => @$studentCourse->subject->title. '  '. ' | ' .@$studentCourse->subject->code ,
                    "date" => @$studentCourse->created_at->formatLocalized('%A %d %B %Y'),
                ];
            });


        return Datatables::make($studentCourses)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/registration/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif


                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/registration/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif

                                    <a href="{{ url(\'/subject/\' . $cid . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    ')
            ->removeColumn('cid')
            ->rawColumns([ 'actions' ])->make();
    }



    public function get_courses(Student $registration)
    {
        return $this->registrationRepository->getAllForStudent($registration->user_id)
            ->get()
            ->map(function ($studentCourse) {
                return [
                    "id" => $studentCourse->id,
                    "subject" => @$studentCourse->subject->title. '  '. ' | ' .@$studentCourse->subject->code ,
                    "date" => @$studentCourse->created_at->formatLocalized('%A %d %B %Y'),
                ];
            });
    }


    /**
     * @return mixed
     */
    private function generateParams()
    {
        $one_school = ( Settings::get('account_one_school') == 'yes' ) ? true : false;
        if ($one_school && $this->user->inRole('accountant')) {
            $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                                                ->with('user')
                                                ->get()
                                                ->map(function ($item) {
                                                    return [
                                                        "id"   => $item->user_id,
                                                        "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->user->student[0]->sID . '| ' : "",
                                                    ];
                                                })->pluck("name", 'id')->toArray();
        } else {
            $students = $this->studentRepository->getAllForSchoolYear(session('current_company_year'))
                                                ->with('user')
                                                ->get()
                                                ->map(function ($item) {
                                                    return [
                                                        "id"   => $item->user_id,
                                                        "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->user->student[0]->sID . '| ' : "",
                                                    ];
                                                })->pluck("name", 'id')->toArray();
        }
        view()->share('students', $students);
    }


}
