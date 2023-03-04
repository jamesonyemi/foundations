<?php

namespace App\Http\Controllers\Secure;

use App\Models\Level;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Semester;
use App\Models\FeesStatus;
use App\Models\StudentStatus;
use App\Models\User;
use App\Repositories\FeeCategoryRepository;
use App\Repositories\RegistrationRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\OptionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\Facades\Redirect;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests\Secure\StudentRegistrationRequest;
use PDF;
use Sentinel;

class StudentRegistrationController extends SecureController
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
     * RegistrationController constructor.
     *
     * @param RegistrationRepository $registrationRepository
     * @param StudentRepository $studentRepository
     * @param FeeCategoryRepository $feeCategoryRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        RegistrationRepository $registrationRepository,
        StudentRepository $studentRepository,
        SubjectRepository $subjectRepository,
        LevelRepository $levelRepository,
        FeeCategoryRepository $feeCategoryRepository,
        DirectionRepository $directionRepository,
        SectionRepository $sectionRepository,
        OptionRepository $optionRepository
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

        /*$this->middleware( 'authorized:registration.show', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:registration.create', [ 'only' => [ 'create', 'store' ] ] );
		$this->middleware( 'authorized:registration.edit', [ 'only' => [ 'update', 'edit' ] ] );
		$this->middleware( 'authorized:registration.delete', [ 'only' => [ 'delete', 'destroy' ] ] );*/

        view()->share('type', 'student_registration');

    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $title = trans('registration.registration');
        $student = Student::where('user_id', Sentinel::getUser()->id)->first();
        $registrations = $this->registrationRepository->getAllForStudent(Sentinel::getUser()->id)
            ->get();


        return view('student_registration.index', compact('title', 'registrations', 'student'));
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function results()
    {
        $title = trans('registration.registration');
        $student = Student::where('user_id', Sentinel::getUser()->id)->first();
        $levels = Level::whereHas('registrations', function ($query) use ($student) {
            $query->where('student_id', '=',$student->id);
        })->orderBy('id','ASC')->get();

        return view('student_registration.results', compact('title', 'levels', 'student'));
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function eLearning()
    {
        $title = trans('registration.registration');
        $user = User::find(Sentinel::getUser()->id);

        $url = "https://elearning.duc.edu.gh/login/index.php?username=$user->email";

        return Redirect::away($url);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $title = trans('registration.new');
        /*$this->generateParams();*/



        $subjects = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->fullname. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        return view('student_registration._form', compact('title', 'subjects', 'levels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RegistrationRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(StudentRegistrationRequest $request)
    {
        $student = Student::where('user_id', Sentinel::getUser()->id)->first();

        $exists = Registration::where('user_id', Sentinel::getUser()->id)
            ->where('school_year_id', session('current_company_year'))
            ->where('semester_id', session('current_company_semester'))
            ->where('company_id', '=', session('current_company'))
            ->where('subject_id', '=', $request->subject_id)
            ->first();
        if (!isset($exists->id)) {
            foreach ($request['subject_id'] as $subject_id) {
                $registration                   = new Registration($request->except('section_id', 'direction_id'));
                $registration->user_id          = Sentinel::getUser()->id ;
                $registration->student_id       = $student->id;
                $registration->subject_id       = $subject_id;
                $registration->level_id         = $student->level_id;
                $registration->section_id       = $student->section_id;
                $registration->company_id        = session('current_company');
                $registration->school_year_id = session('current_company_year');
                $registration->semester_id      = session('current_company_semester');
                $registration->save();
            }
        }


        if (!empty($request['level_id']))
        {
            $student->level_id      = $request['level_id'];
            $student->save();
        }

        StudentStatus::firstOrCreate(['company_id' => session('current_company'), 'school_year_id' => session('current_company_year'), 'semester_id' => session('current_company_semester'), 'student_id' => $student->id]);

        return 'Registration Successfully!';
    }

    /**
     * Display the specified resource.
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function show(Registration $studentRegistration)
    {
        $pdf = PDF::loadView('report.invoice', compact('studentRegistration'));
        return $pdf->stream();
    }


    public function regPrint()
    {
       $title = trans('student.edit');

       $student = Student::where('user_id', Sentinel::getUser()->id)->first();
       $registrations = $this->registrationRepository->getAllForStudent(Sentinel::getUser()->id)
           ->get();


        /*$pdf = PDF::loadView('student_registration._print', compact('title', 'student', 'registrations'));
        return $pdf->stream();*/

        return view('student_registration._print', compact('title', 'student', 'registrations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function edit(Registration $studentRegistration)
    {
        $title = trans('registration.edit');
        $this->generateParams();


        $subjects = $this->subjectRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->fullname. ' ' .$item->code : "",
                ];
            })
            ->pluck('title', 'id')
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'studentRegistration', 'subjects', 'levels'));
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
    public function update(StudentRegistrationRequest $request, Registration $studentRegistration)
    {


        $studentRegistration->update($request->except('id', 'section_id', 'direction_id'));



        return redirect('/student_registration');
    }

    /**
     *
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function delete(Registration $studentRegistration)
    {
        $title = trans('registration.delete');

        return view('/student_registration/delete', compact('studentRegistration', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Registration $registration
     *
     * @return Response
     */
    public function destroy(Registration $studentRegistration)
    {
        $studentRegistration->delete();

        return redirect('/student_registration');
    }






    public function data()
    {
            $registrations = $this->registrationRepository->getAllForStudent(Sentinel::getUser()->id)
            ->get()
            ->map(function ($registration) {
                return [
                    "id" => $registration->id,
                    "subject" => @$registration->subject->title. '  '. ' | ' .@$registration->subject->code ,
                    "date" => @$registration->created_at->formatLocalized('%A %d %B %Y'),
                ];
            });

        return Datatables::make($registrations)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'invoice.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/student_registration/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <!--<a target="_blank" href="{{ url(\'/student_registration/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>-->
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'invoice.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/student_registration/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->rawColumns([ 'actions' ])->make();
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
