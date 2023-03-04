<?php

namespace App\Http\Controllers\Secure;

use App\Exports\ApplicantsExport;
use App\Exports\StudentsExport;
use App\Http\Requests\Secure\PinRequest;
use App\Http\Requests\Secure\SmsMessageRequest;
use App\Helpers\CustomFormUserFields;
use App\Helpers\Thumbnail;
use App\Mail\OrderShipped;
use App\Models\Activation;
use App\Models\AdmissionWaecExam;
use App\Models\ApplicantNote;
use App\Models\Persistence;
use App\Models\RoleUser;
use App\Models\Semester;
use App\Models\UserDocument;
use App\Http\Requests\Secure\ApplicantRequest;
use App\Http\Requests\Secure\ApplicantNoteRequest;
use App\Http\Requests\Secure\ApplicantWaecRequest;
use App\Http\Requests\Secure\EnrollRequest;
use App\Models\User;
use App\Models\Applicant;
use App\Models\School;
use App\Models\SmsMessage;
use App\Repositories\ApplicationTypeRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\ApplicantRepository;
use App\Repositories\UserRepository;
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
use App\Helpers\Settings;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Flash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Sentinel;
use App\Notifications\SendSMS;
use Illuminate\Support\Facades\DB;

class ApplicantController extends SecureController
{
    /**
     * @var UserRepository
     */
    /**
     * @var StudentRepository
     */
    private $studentRepository;

    private $applicationTypeRepository;

    private $applicantRepository;
    /**
     * @var SectionRepository
     */
    private $userRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var DirectionRepository
     */
    private $directionRepository;
    /**
     * @var ProgrammeRepository
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
    private $religionRepository;
    private $schoolYearRepository;
    private $graduationYearRepository;
    private $semesterRepository;
    private $sessionRepository;
    protected $activity;
    protected $module = 'Applicant';

    /**
     * TeacherController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository,
        LevelRepository $levelRepository,
        ApplicantRepository $applicantRepository,
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
        StudentRepository $studentRepository,
        SessionRepository $sessionRepository,
        SectionRepository $sectionRepository,
        ActivityLogRepository $activity,
        ApplicationTypeRepository $applicationTypeRepository
    ) {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->applicantRepository = $applicantRepository;
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
        $this->studentRepository = $studentRepository;
        $this->sessionRepository = $sessionRepository;
        $this->activity = $activity;
        $this->applicationTypeRepository = $applicationTypeRepository;

        $this->middleware('authorized:applicant.show', [ 'only' => [ 'index', 'data' ] ]);
        $this->middleware('authorized:applicant.create', [ 'only' => [ 'create', 'store' ] ]);
        $this->middleware('authorized:applicant.edit', [ 'only' => [ 'update', 'edit' ] ]);
        $this->middleware('authorized:applicant.delete', [ 'only' => [ 'delete', 'destroy' ] ]);

        view()->share('type', 'applicant');

        $columns = ['id', 'full_name', 'program', 'nationality', 'session', 'email', 'phone', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
       /* if (!Sentinel::hasAccess('applicant.list')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }*/
        $title = trans('applicant.applicants');

        $this->filterParams();
        $maleStudents = $this->applicantRepository
            ->getAllMale()
            ->get();

        $femaleStudents = $this->applicantRepository
            ->getAllFemale()
            ->get();

        $count = 1;

        $applicants = $this->applicantRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'), session('current_semester'), )
            ->with('user', 'applicationType', 'programme1')
            ->get();


        return view('applicant.index', compact('title', 'maleStudents', 'femaleStudents', 'applicants'));
    }

    public function latestApplicants()
    {
        $title = trans('applicant.applicants');

        $count = 1;

        $applicants = $this->applicantRepository->getAllForSchoolYearAndSchool(session('current_company'))
            ->with('user')
            ->take(5)
            ->get();


        return view('applicant.latest_applicants', compact('title','applicants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Sentinel::hasAccess('applicant.create')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title         = trans('applicant.new');

        $this->generateParams();

        $custom_fields = CustomFormUserFields::getCustomUserFields('applicant');

        return view('layouts.create', compact('title',  'custom_fields'));
    }


    public function createModal()
    {
        if (!Sentinel::hasAccess('applicant.create')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title         = trans('applicant.new');

        $this->generateParams();

        $custom_fields = CustomFormUserFields::getCustomUserFields('applicant');

        return view('applicant.modal_form', compact('title',  'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorRequest $request
     *
     * @return Response
     */
    public function store(ApplicantRequest $request)
    {
        if (!Sentinel::hasAccess('applicant.create')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }

        try
        {
            DB::transaction(function() use ($request) {
        $user = Sentinel::registerAndActivate($request->all());

        $role = Sentinel::findRoleBySlug('applicant');
        $role->users()->attach($user);

        $user = User::find($user->id);
        if ($request->hasFile('image_file') != "") {
            $file      = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture   = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $user->picture = $picture;
            $user->save();
        }
        $user->update($request->except('password', 'image_file'));

        $applicant          = new Applicant();
        $applicant->user_id = $user->id;
        $applicant->company_id = session('current_company');
        $applicant->school_year_id = session('current_company_year');
        $applicant->graduation_year_id = $request->graduation_year_id;
        $applicant->semester_id = session('current_company_semester');
        $applicant->section_id = $request->section_id;
        $applicant->country_id = $request->country_id;
        $applicant->first_choice_prog_id = $request->first_choice_prog_id;
        $applicant->second_choice_prog_id = $request->second_choice_prog_id;
        $applicant->third_choice_prog_id = $request->third_choice_prog_id;
        $applicant->marital_status_id = $request->marital_status_id;
        $applicant->residencial_address = $request->residencial_address;
        $applicant->disability = $request->disability;
        $applicant->level_of_adm = $request->level_of_adm;
        $applicant->entry_mode_id = $request->entry_mode_id;
        $applicant->intake_period_id = $request->intake_period_id;
        $applicant->campus_id = $request->campus_id;
        $applicant->session_id = $request->session_id;
        $applicant->religion_id = $request->religion_id;
        $applicant->application_type_id = $request['application_type_id'];
        $applicant->contact_relation = $request['contact_relation'];
        $applicant->contact_name = $request['contact_name'];
        $applicant->contact_address = $request['contact_address'];
        $applicant->contact_phone = $request['contact_phone'];
        $applicant->contact_email = $request['contact_email'];
        $applicant->pin = rand(1000, 9000);
        $applicant->save();

        //$applicant->visitor_no = Settings::get( 'visitor_card_prefix' ) . $visitor->id;
        //$applicant->save();

        CustomFormUserFields::storeCustomUserField('applicant', $user->id, $request);

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $applicant->id,
            'activity'  => 'created'
        ]);

        /*return redirect('/applicant')->with('status', 'Applicant Created Successfully!');;*/
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        return response('Applicant Created Successfully!') ;
    }

    /**
     * Display the specified resource.
     *
     * @param  User $applicant
     *
     * @return Response
     */
    public function show(Applicant $applicant)
    {
        if (!Sentinel::hasAccess('applicant.show')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title = $applicant->user->full_name;
        $action = 'show';

        $this->generateParams();
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('applicant', $applicant->user_id);
        return view('layouts.show', compact('applicant', 'count', 'thisUser', 'custom_fields', 'title', 'action'));
    }


    public function showModal(Applicant $applicant)
    {
        if (!Sentinel::hasAccess('applicant.show')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title = $applicant->user->full_name;
        $action = 'show';

        $this->generateParams();
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('applicant', $applicant->user_id);
        return view('applicant.showModal', compact('applicant', 'count', 'thisUser', 'custom_fields', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $applicant
     *
     * @return Response
     */
    public function edit(Applicant $applicant)
    {
        if (!Sentinel::hasAccess('applicant.edit')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title         = 'Edit '. $applicant->user->full_name.'';
        $this->generateParams();
        $custom_fields = CustomFormUserFields::fetchCustomValues('applicant', $applicant->id);

        return view('applicant.modal_form', compact('title', 'applicant', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ApplicantRequest $request
     * @param User $applicant
     *
     * @return Response
     */


    public function update(ApplicantRequest $request, Applicant $applicant)
    {
        if (!Sentinel::hasAccess('applicant.edit')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        try
        {
            DB::transaction(function() use ($request, $applicant) {
        $applicant->update($request->only('section_id', 'section_id', 'first_choice_prog_id', 'second_choice_prog_id', 'third_choice_prog_id', 'level_of_adm', 'entry_mode_id',  'graduation_year_id', 'intake_period_id', 'campus_id', 'country_id', 'marital_status_id', 'no_of_children', 'religion_id', 'denomination', 'disability', 'contact_relation', 'contact_name', 'contact_address', 'contact_phone', 'contact_email', 'session_id', 'application_type_id'));
        $applicant->save();
        if ($request->password != "") {
            $applicant->user->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $applicant->user->picture = $picture;
            $applicant->user->save();
        }

        $applicant->user->update($request->except('section_id', 'order', 'password', 'document', 'document_id', 'image_file'));

        if ($request->hasFile('document') != "") {
            $file = $request->file('document');
            $user = $applicant->user;
            $extension = $file->getClientOriginalExtension();
            $document = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/documents/';
            $file->move($destinationPath, $document);

            UserDocument::where('user_id', $user->id)->delete();

            $userDocument = new UserDocument;
            $userDocument->user_id = $user->id;
            $userDocument->document = $document;
            $userDocument->option_id = $request->document_id;
            $userDocument->save();
        }
        CustomFormUserFields::updateCustomUserField('applicant', $applicant->user->id, $request);

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $applicant->id,
            'activity'  => 'Updated'
        ]);

        /*return redirect('/applicant/'. $applicant->id .'/show')
            ->with('status', 'Applicant Information Updated Successfully!');*/
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        return response('Applicant info Updated Successfully') ;
    }




    /*public function update( ApplicantRequest $request, Applicant $applicant ) {
        if ( $request->password != "" ) {
            $applicant->password = bcrypt( $request->password );
        }
        if ( $request->hasFile( 'image_file' ) != "" ) {
            $file      = $request->file( 'image_file' );
            $extension = $file->getClientOriginalExtension();
            $picture   = str_random( 10 ) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move( $destinationPath, $picture );
            Thumbnail::generate_image_thumbnail( $destinationPath . $picture, $destinationPath . 'thumb_' . $picture );
            $applicant->picture = $picture;
            $applicant->save();
        }
        $applicant->update( $request->except( 'password', 'image_file' ) );
        CustomFormUserFields::updateCustomUserField( 'applicant', $applicant->id, $request );

        return redirect( '/applicant' );
    }*/


    /**
     *
     *
     * @param User $applicant
     *
     * @return Response
     */
    public function delete(Applicant $applicant)
    {
        if (!Sentinel::hasAccess('applicant.delete')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title = trans('applicant.delete'). ' '. $applicant->user->full_name;

        return view('/applicant/delete', compact('applicant', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $applicant
     *
     * @return Response
     */
    public function destroy(Applicant $applicant)
    {
        if (!Sentinel::hasAccess('applicant.delete')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }

        $applicant->delete();

        User::destroy($applicant->user_id);
        RoleUser::destroy($applicant->user_id);
        Persistence::destroy($applicant->user_id);
        Activation::destroy($applicant->user_id);

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $applicant->id,
            'activity'  => 'Deleted'
        ]);


        return redirect('/applicant')->with('status', 'Applicant Deleted Successfully!');;
    }


    public function makeValidate(PinRequest $request, Applicant $applicant)
    {
        if ($request->pin == $applicant->pin){

                $applicant->validated = 1;
                $applicant->save();
            Flash::success('Pin Validated Successfully!');
            return redirect('/');


            }
        else
            Flash::error('Wrong pin entered!.');
       return redirect('/');


    }



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




        view()->share('religions', $religions);
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
    }



    private function generateParams()
    {

        $applicationTypes = $this->applicationTypeRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('applicant.select_application_type'), 0)
            ->toArray();

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_session'), 0)
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

        $entrymodes = $this->entryModeRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_entry_mode'), 0)
            ->toArray();

        $intakeperiods = $this->intakePeriodRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_intake_period'), 0)
            ->toArray();

        $campus = $this->campusRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_campus'), 0)
            ->toArray();

        $countries = $this->countryRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_country'), '')
            ->toArray();

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_marital_status'), 0)
            ->toArray();

        $religion = $this->religionRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_religion'), 0)
            ->toArray();

        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school_year'), '')
            ->toArray();



        $graduationyears = $this->graduationYearRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school_year'), 0)
            ->toArray();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->title. ' ' .$item->school_year->title : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('student.select_semester'), '')
            ->toArray();


        $schoolType = session('current_company_type');

        $school = School::find(session('current_company'));




        view()->share('schoolType', $schoolType);
        view()->share('applicationTypes', $applicationTypes);
        view()->share('schoolyears', $schoolyears);
        view()->share('graduationyears', $graduationyears);
        view()->share('school', $school);
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
        view()->share('religion', $religion);
    }






    /**
     * FOR applicants
     *
     * @return Response
     */


    public function personal()
    {
        if (!Sentinel::check()) {
            return redirect("/");
        }
   $this->generateParams();

        $custom_fields =  CustomFormUserFields::getCustomUserFields('applicant');
        $user = User::find(Sentinel::getUser()->id);
        $applicant = Applicant::where('user_id', (Sentinel::getUser()->id))->get();
        return view('applicant.applicant_info', compact('title', 'user', 'applicant', 'custom_fields'));
    }








    public function docs()
    {
        $title = trans('applicant.applicant');

        return view('applicant.index', compact('title'));
    }



    public function programs()
    {
        $title = trans('applicant.applicant');

        if (!Sentinel::check()) {
            return redirect("/");
        }
        $title = trans('auth.edit_profile');

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_session'), '')
            ->toArray();


        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), '')
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $entrymodes = $this->entryModeRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_entry_mode'), '')
            ->toArray();

        $intakeperiods = $this->intakePeriodRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_intake_period'), '')
            ->toArray();

        $campus = $this->campusRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_campus'), '')
            ->toArray();



        $user = User::find(Sentinel::getUser()->id);
        $applicant = Applicant::where('user_id', (Sentinel::getUser()->id))->get();
        return view('applicant.applicant_program_selection', compact('title', 'user', 'applicant', 'sessions', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus'));
    }

    public function ajaxNote(ApplicantNoteRequest $request)
    {
        $applicantNote = new ApplicantNote;
        $applicantNote->user_id = Sentinel::getUser()->id;
        $applicantNote->note = $request->note;
        $applicantNote->applicant_id = $request->applicant_id;
        $applicantNote->status_id = 0;
        $applicantNote->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $applicant = Applicant::find($request->applicant_id);
        return view('applicant.notes', compact('title', 'action', 'count', 'applicant', 'thisUser'));
    }


    public function ajaxAddWaec(ApplicantWaecRequest $request)
    {
        $admissionWaecExam = new AdmissionWaecExam();
        $admissionWaecExam->index_number = $request->waecIndexNumber;
        $admissionWaecExam->waec_exam_id = $request->WaecExamType_id;
        $admissionWaecExam->applicant_id = $request->applicant_id;
        $admissionWaecExam->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $applicant = Applicant::find($request->applicant_id);
        return view('applicant_school.waec_exams', compact('title', 'action', 'count', 'applicant', 'thisUser'));
    }



    public function ajaxSMS(SmsMessageRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
        $school = School::find(session('current_company'))->first();
        if ($school->limit_sms_messages == 0 ||
            $school->limit_sms_messages > $school->sms_messages_year) {
            $user = User::find($request->muser_id);
            if (! is_null($user) && $user->mobile != "") {
                $user->notify(new SendSMS($user, $request));

                $smsMessage                 = new SmsMessage();
                $smsMessage->text           = $request->text;
                $smsMessage->number         = $user->mobile;
                $smsMessage->user_id        = $request->muser_id;
                $smsMessage->user_id_sender = $this->user->id;
                $smsMessage->company_id      = session('current_company');
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



    public function enroll(EnrollRequest $request)
    {

        $student = $this->studentRepository->enroll($request->All());

        $this->applicantRepository->deactivate($request->applicant_id);

        return redirect('/student/'. @$student->id .'/show')
            ->with('status', 'Applicant Enrolled As Student Successfully!');
    }


   /* public function generalExport(Request $request)
    {
        $students = $this->applicantRepository->getAllActiveExport($request)
            ->get()
            ->map(function ($student) {
                return [
                    'First Name' => isset($student->user) ? $student->user->first_name : "",
                    'Middle Name' => isset($student->user) ? $student->user->middle_name : "",
                    'Surname' => isset($student->user) ? $student->user->last_name : "",
                    'Email' => isset($student->user) ? $student->user->email : "",
                    'Phone' => isset($student->user) ? $student->user->mobile : "",
                    'Department' => isset($student->section) ? $student->section->title : "",
                    'First Choice Program' => isset($student->programme1) ? $student->programme1->title : "",
                    'Second Choice Program' => isset($student->programme2) ? $student->programme2->title : "",
                    'level of admission ' => isset($student->admissionlevel) ? $student->admissionlevel->name : "",
                    'Year' => isset($student->academicyear) ? $student->academicyear->title : "",
                    'Semester' => isset($student->semester) ? $student->semester->title : "NOT DEFINED",
                    'Entry Mode' => isset($student->entrymode) ? $student->entrymode->name : "",
                    'Session' => isset($student->session) ? $student->session->name : "",
                    'Gender'  => (@$student->user->gender=='1') ? trans('student.male'):trans('student.female'),
                    'Religion' => isset($student->religion) ? $student->religion->name : "",
                    'Nationality' => isset($student->country) ? $student->country->nationality : "",
                    'Status'  => ($student->status == 0) ? "ENROLLED":"NOT ENROLLED",
                ];
            })->toArray();


        Excel::create(trans('applicant.applicants'), function ($excel) use ($students) {
            $excel->sheet(trans('applicant.applicants'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }*/


    public function studentFilter(Request $request)
    {
        $students = $this->applicantRepository->getAllActiveFilter($request)->get();

        return view('applicant.allFilteredList', ['students' => $students], ['count' => '1']);//
    }



    public function generalExport(Request $request)
    {
        $applicants = $this->applicantRepository->getAllActiveExport($request);
        return Excel::download(new ApplicantsExport($applicants), 'applicants.xlsx');

    }


    public function sendEmail()
    {

        try {
        Mail::to('mathew.akoto@makslinesolutions.com')->send(new OrderShipped());
    } catch (Exception $e) {
echo $e->getMessage();
}



    }



}
