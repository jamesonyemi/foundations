<?php

namespace App\Http\Controllers\Secure;

use App\Models\AdmissionNonWaecExam;
use App\Models\AdmissionWaecExamSubject;
use App\Models\Direction;
use App\Models\ShsSchool;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AdmissionWaecExam;
use App\Models\Applicant;
use App\Models\Applicant_school;
use App\Http\Requests\Secure\ApplicantWaecRequest;
use App\Http\Requests\Secure\ApplicantWaecExamSubject;
use App\Http\Requests\Secure\ApplicantNonWaecRequest;
use App\Repositories\ApplicantSchoolRepository;
use App\Repositories\QualificationRepository;
use App\Repositories\WaecExamRepository;
use App\Repositories\WaecSubjectRepository;
use App\Repositories\WaecSubjectGradeRepository;
use App\Helpers\CustomFormUserFields;
use App\Helpers\Thumbnail;
use App\Models\ApplicantNote;
use App\Models\UserDocument;
use App\Http\Requests\Secure\ApplicantRequest;
use App\Http\Requests\Secure\ApplicantNoteRequest;
use App\Http\Requests\Secure\EnrollRequest;
use App\Models\User;
use App\Models\Applicant_work;
use App\Models\Applicant_doc;
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
use App\Helpers\Settings;
use Yajra\DataTables\Facades\DataTables;
use Sentinel;

class ApplicantInformationController extends SecureController
{
    /**
     * @var UserRepository
     */
    /**
     * @var StudentRepository
     */
    private $studentRepository;

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
    private $sessionRepository;
    /**
     * @var ApplicantSchoolRepository
     */
    private $applicantSchoolRepository;
    /**
     * @var ApplicantSchoolRepository
     */
    private $qualificationRepository;
    /**
     * @var WaecExamRepository
     */
    private $waecExamRepository;
    private $waecSubjectRepository;
    private $waecSubjectGradeRepository;

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
        StudentRepository $studentRepository,
        SessionRepository $sessionRepository,
        ApplicantSchoolRepository $applicantSchoolRepository,
        QualificationRepository $qualificationRepository,
        WaecExamRepository $waecExamRepository,
        WaecSubjectRepository $waecSubjectRepository,
        WaecSubjectGradeRepository $waecSubjectGradeRepository,
        SectionRepository $sectionRepository
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
        $this->studentRepository = $studentRepository;
        $this->sessionRepository = $sessionRepository;
        $this->applicantSchoolRepository = $applicantSchoolRepository;
        $this->qualificationRepository = $qualificationRepository;
        $this->waecExamRepository = $waecExamRepository;
        $this->waecSubjectRepository = $waecSubjectRepository;
        $this->waecSubjectGradeRepository = $waecSubjectGradeRepository;

        $this->middleware('authorized:applicant.show', [ 'only' => [ 'index', 'data' ] ]);
        $this->middleware('authorized:applicant.create', [ 'only' => [ 'create', 'store' ] ]);
        $this->middleware('authorized:applicant.delete', [ 'only' => [ 'delete', 'destroy' ] ]);

        view()->share('type', 'applicant_personal');

        $columns = ['id', 'full_name', 'program', 'nationality', 'session', 'email', 'phone', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexx()
    {
        $title = trans('applicant.applicants');

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $maleStudents = $this->studentRepository
            ->getAllMale()
            ->get();

        $femaleStudents = $this->studentRepository
            ->getAllFemale()
            ->get();

        $countries = $this->countryRepository
            ->getAll()
            ->get();

        $countries2 = $this->countryRepository
            ->getCountryStudents()
            ->get();

        return view('applicant.index', compact('title', 'sections', 'maleStudents', 'femaleStudents', 'countries', 'countries2'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title         = trans('applicant.new');

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
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
            ->prepend(trans('student.select_country'), 0)
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


        $custom_fields = CustomFormUserFields::getCustomUserFields('applicant');

        return view('layouts.create', compact('title', 'sections', 'sessions', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'maritalStatus', 'religion', 'document_types', 'custom_fields'));
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
        $applicant->save();

        //$applicant->visitor_no = Settings::get( 'visitor_card_prefix' ) . $visitor->id;
        //$applicant->save();

        CustomFormUserFields::storeCustomUserField('applicant', $user->id, $request);

        return redirect('/applicant');
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

        $title = trans('student.details');
        $action = 'show';


        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
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
            ->prepend(trans('student.select_country'), 0)
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

        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('applicant', $applicant->user_id);
        //$notes = ApplicantNote::where('applicant_id', $applicant->id)->get();
        return view('layouts.show', compact('applicant', 'sections', 'sessions', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'maritalStatus', 'religion', 'title', 'action', 'count', 'custom_fields', 'thisUser'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $applicant
     *
     * @return Response
     */
    public function edit(Applicant $applicant_personal)
    {

        if (session('current_applicant')!= $applicant_personal->id) {
            return redirect("/");
        }

        $title         = 'Dominion University Application';

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

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
            ->prepend(trans('student.select_level'), 0)
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
            ->prepend(trans('student.select_intake_period'), 0)
            ->toArray();

        $campus = $this->campusRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_campus'), 1)
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

        $waecExams = $this->waecExamRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $waecSubjects = $applicant_personal->applicationType->subjects
            ->pluck('title', 'id')
            ->prepend('select subject', '')
            ->toArray();

        $waecSubjectGrades = $applicant_personal->applicationType->subjectGrades
            ->pluck('title', 'id')
            ->prepend('Select Grade', '')
            ->toArray();

        $qualifications = $this->qualificationRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_qualification'), '')
            ->toArray();

        $schools = ShsSchool::orderBy('title', 'asc')->get()
            ->pluck('title', 'id')
            ->prepend(trans('Select School'), '')
            ->toArray();


        $custom_fields = CustomFormUserFields::fetchCustomValues('applicant', $applicant_personal->id);

        return view('applicant_personal.wizard', compact('title', 'applicant_personal', 'sections', 'sessions', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'maritalStatus', 'religion', 'custom_fields', 'waecExams', 'qualifications', 'waecSubjectGrades', 'waecSubjects', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ApplicantRequest $request
     * @param User $applicant
     *
     * @return Response
     */


    public function update(ApplicantRequest $request, Applicant $applicant_personal)
    {

        try
        {
        $applicant_personal->update($request->only('first_choice_prog_id', 'second_choice_prog_id', 'third_choice_prog_id', 'level_of_adm', 'entry_mode_id', 'intake_period_id', 'campus_id', 'country_id', 'marital_status_id', 'no_of_children', 'religion_id', 'denomination', 'disability', 'contact_relation', 'contact_name', 'contact_address', 'contact_phone', 'contact_email', 'session_id'));
            if ($applicant_personal->application_type_id != 8) {
                $applicant_personal->section_id = Direction::find($request->first_choice_prog_id)->section_id;
            }

        $applicant_personal->applied = 1;

        $applicant_personal->save();
        if ($request->password != "") {
            $applicant_personal->user->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $applicant_personal->user->picture = $picture;
            $applicant_personal->user->save();
        }

        $applicant_personal->user->update($request->except('section_id', 'order',
            'password', 'document', 'document_id',
            'image_file', 'title', 'start_date',
            'end_date', 'qualification_id', 'user_id', 'applicant_id'));

        if ($request->hasFile('document') != "") {
            $file = $request->file('document');
            $user = $applicant_personal->user;
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


        if ($applicant_personal->application_type_id != 8) {
        $titles = $request['title'];
        $shs_company_ids = $request['shs_company_id'];
        $start_dates = $request['start_date'];
        $end_dates = $request['end_date'];
        $qualification_ids = $request['qualification_id'];

        Applicant_school::where('applicant_id', $request->applicant_id)->delete();

        if (isset($request['shs_company_id'])){
        foreach ($shs_company_ids as $index => $shs_company_id)
            {
                if (!empty($shs_company_id) )
                {
                $applicant_school = new Applicant_school();
               /* $applicant_school->title = $title;*/
                $applicant_school->shs_company_id = $shs_company_id;
                $applicant_school->start_date = $start_dates[$index];
                $applicant_school->end_date = $end_dates[$index];
                $applicant_school->qualification_id = $qualification_ids[$index];
                $applicant_school->applicant_id = $request->applicant_id;
                $applicant_school->user_id = $request->user_id;
                $applicant_school->save();
                }
            }

        }


            if (isset($request['waec_exam_id'])){
            $waec_exam_ids = $request['waec_exam_id'];
            $waec_years = $request['waec_year'];
            $index_numbers = $request['index_number'];
            $grades = $request['grade'];

            foreach ($waec_exam_ids as $index => $waec_exam_id)
            {
                if (!empty($waec_exam_id) )
                {
                    $applicant_waec = new AdmissionWaecExam();
                    $applicant_waec->applicant_id = $request->applicant_id;
                    $applicant_waec->waec_exam_id = $waec_exam_id;
                    $applicant_waec->waec_year = $waec_years[$index];
                    $applicant_waec->index_number = $index_numbers[$index];
                    $applicant_waec->grade = $grades[$index];
                    $applicant_waec->save();
                }
            }
            }

            if (isset($request['non_waec_exam_id'])){

            $non_waec_exams = $request['non_waec_exam_id'];
            $non_waec_years = $request['non_waec_year'];
            $non_index_numbers = $request['non_index_number'];
            $non_grades = $request['non_grade'];
            $non_programs = $request['program'];

            AdmissionNonWaecExam::where('applicant_id', $request->applicant_id)->delete();

            foreach ($non_waec_exams as $index => $non_waec_exam)
            {
                if (!empty($non_waec_exam) )
                {
                    $applicant_waec = new AdmissionNonWaecExam();
                    $applicant_waec->applicant_id = $request->applicant_id;
                    $applicant_waec->title = $non_waec_exam;
                    $applicant_waec->year = $non_waec_years[$index];
                    $applicant_waec->program = $non_programs[$index];
                    $applicant_waec->index_number = $non_index_numbers[$index];
                    $applicant_waec->grade = $non_grades[$index];
                    $applicant_waec->save();
                }
            }

            }

            if (isset($request['waec_subject_id'])){
            $subjects = $request['waec_subject_id'];
            $subject_grades = $request['grade_id'];

            AdmissionWaecExamSubject::where('applicant_id', $request->applicant_id)->delete();

            foreach ($subjects as $index => $subject)
            {
                if (!empty($subject) )
                {
                    $applicant_subject = new AdmissionWaecExamSubject();
                    $applicant_subject->applicant_id = $request->applicant_id;
                    $applicant_subject->waec_subject_id = $subject;
                    $applicant_subject->waec_subject_grade_id = $subject_grades[$index];
                    $applicant_subject->save();
                }
            }
            }
        }

            if ($applicant_personal->application_type_id == 8)
            {
                $applicant_personal->update($request->only(
                    'school_country_id',
                    'postgraduate_school',
                    'course_title',
                    'currently_enrolled',
                    'completed',
                    'completion_date',
                    'degree_title',
                    'thesis_title',
                    'length',
                    'any_research',
                    'research_length_scope',
                    'employer',
                    'employment_dates',
                    'position',
                    'research_topic',
                    'identified_supervisor',
                    'supervisor_name',
                    'english_official_language',
                    'english_proficiency',
                    'additional_information',
                    'referee_1',
                    'referee_1_contact',
                    'referee_2',
                    'referee_2_contact'
                ));

            }



        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }


        if ($applicant_personal->save())
        {

            return response('<div class="alert alert-success">Congratulations !!!</div>') ;
        }
        else
        {
            return response('<div class="alert alert-danger">Operation Not Successful !!!</div>');
        }
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


    public function update2($request, Applicant $applicant)
    {
        $applicant->update($request->all());

        return redirect('/applicant_program');
    }


    /**
     *
     *
     * @param User $applicant
     *
     * @return Response
     */
    public function delete(Applicant $applicant)
    {
        $title = trans('applicant.delete');

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
        Applicant::where('id', '=', $applicant->id)->delete();

        $applicant->delete();

        return redirect('/applicant');
    }








    /**
     * FOR applicants
     *
     * @return Response
     */


    public function index()
    {
        $user = User::find(Sentinel::getUser()->id);
        $applicant = Applicant::where('user_id', (Sentinel::getUser()->id))->first();
        return redirect('/applicant_personal/'.$applicant->id.'/edit');
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
        $applicantNote->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $applicant = Applicant::find($request->applicant_id);
        return view('applicant.notes', compact('title', 'action', 'count', 'applicant', 'thisUser'));
    }



    public function enroll(EnrollRequest $request)
    {
        $student = $this->studentRepository->enroll($request->All());


        $this->applicantRepository->deactivate($request->applicant_id);


        return redirect('/student/'. $student->id .'/show');
    }
}
