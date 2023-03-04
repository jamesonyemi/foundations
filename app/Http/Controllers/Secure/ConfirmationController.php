<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\RegistrationRequest;
use App\Models\DormitoryRoom;
use App\Models\Registration;
use App\Models\Student;
use App\Repositories\DirectionRepository;
use App\Repositories\DormitoryRoomRepository;
use App\Repositories\LevelRepository;
use App\Repositories\OptionRepository;
use App\Repositories\RegistrationRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use DB;
use Efriandika\LaravelSettings\Facades\Settings;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class ConfirmationController extends SecureController
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

    /**
     * @var DirectionRepository
     */
    private $directionRepository;

    /**
     * @var DirectionRepository
     */
    private $levelRepository;

    /**
     * @var DormitoryRepository
     */
    private $dormitoryRepository;

    /**
     * @var DormitoryRoomRepository
     */
    private $dormitoryRoomRepository;

    /**
     * RegistrationController constructor.
     *
     * @param RegistrationRepository $registrationRepository
     * @param StudentRepository $studentRepository
     * @param SubjectRepository $subjectRepository
     * @param LevelRepository $levelRepository
     * @param FeeCategoryRepository $feeCategoryRepository
     * @param DirectionRepository $directionRepository
     * @param SectionRepository $sectionRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        RegistrationRepository $registrationRepository,
        StudentRepository $studentRepository,
        LevelRepository $levelRepository,
        DirectionRepository $directionRepository,
        SectionRepository $sectionRepository,
        OptionRepository $optionRepository,
        DormitoryRoomRepository $dormitoryRoomRepository
    ) {
        parent::__construct();

        $this->registrationRepository = $registrationRepository;
        $this->studentRepository = $studentRepository;
        $this->optionRepository = $optionRepository;
        $this->sectionRepository = $sectionRepository;
        $this->levelRepository = $levelRepository;
        $this->directionRepository = $directionRepository;
        $this->dormitoryRoomRepository = $dormitoryRoomRepository;

        $this->middleware('authorized:registration.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:registration.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:registration.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:registration.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'confirmation');

        $columns = ['student_no', 'full_name', 'gender', 'section', 'level', 'block', 'dormitory',  'date'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = trans('confirmation.confirmation');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get();
        $confirmations = $this->studentRepository->getAllForSchoolConfirm(session('current_company_year'), session('current_company'))
                                                      ->get();
        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                                                 ->get();

        $sectionsChart = $this->sectionRepository
            ->getAllForSchoolYearSchoolChart(session('current_company_year'), session('current_company'))
            ->select('title', 'id')
            ->get();

        $registrations = $this->studentRepository->getAllForSchoolConfirm(session('current_company_year'), session('current_company'));

        $data = $registrations->get();

        $maleStudents = $this->studentRepository->getAllMaleConfirm()->count();
        $femaleStudents = $this->studentRepository->getAllFemaleConfirm()->count();

        return view('confirmation.index', compact('title', 'sections', 'confirmations', 'students', 'maleStudents', 'femaleStudents', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = trans('registration.new');
        $this->generateParams();

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'sections'));
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
        foreach ($request->get('user_id') as $user_id) {
            $student = Student::where('user_id', $user_id)->where('school_year_id', session('current_company_year'))
                              ->where('company_id', session('current_company'))
                              ->first();

            $user_exists = Registration::where('user_id', $user_id)
                                           ->where('school_year_id', session('current_company_year'))
                                           ->where('company_id', '=', session('current_company'))
                                           ->where('dormitory_room_id', '=', $request->dormitory_room_id)
                                           ->first();

            $dormitory = DormitoryRoom::find($request->dormitory_room_id)->first();
            if (! isset($user_exists->id)) {
                $registration = new Registration();
                $registration->user_id = $user_id;
                $registration->student_id = $student->id;
                $registration->dormitory_id = $dormitory->dormitory_id;
                $registration->company_id = session('current_company');
                $registration->school_year_id = session('current_company_year');
                $registration->dormitory_room_id = $request->dormitory_room_id;
                $registration->save();
            }
        }

        return redirect('/confirmation');
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
        $pdf = PDF::loadView('report.registration', compact('registration'));

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
        $subjects = $this->subjectRepository->getAllForStudentGroup($registration->student_group_id)
                                            ->get()
                                            ->map(function ($subject) {
                                                return [
                                                    'id'   => $subject->id,
                                                    'name' => $subject->title,
                                                ];
                                            })->pluck('name', 'id');

        $levels = $this->levelRepository->getAllForSection(session('current_company'))
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->prepend(trans('student.select_level'), 0)
                                        ->toArray();

        return view('layouts.edit', compact('title', 'registration', 'subjects', 'levels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RegistrationRequest $request
     * @param  Registration $registration
     * @return Response
     */
    public function update(RegistrationRequest $request, Registration $registration)
    {
        $registration->update($request->only('user_id', 'dormitory_room_id'));

        return redirect('/confirmation');
    }

    /**
     * @param  Registration $registration
     *
     * @return Response
     */
    public function delete(Registration $registration)
    {
        $title = trans('confirmation.delete');

        return view('/confirmation/delete', compact('registration', 'title'));
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

        return redirect('/confirmation');
    }

    /*	public function data() {

            $registrations = $this->studentRepository->getAllForSchoolConfirm( session( 'current_company_year' ) , session( 'current_company' ) );

            $registrations = $registrations->get()
                                           ->map( function ( $registration ) {
                                               return [
                                                   "id"            => $registration->id,
                                                   "student_no"    => $registration->student_no,
                                                   "full_name"     => $registration->full_name,
                                                   'gender'        => ($registration->gender=='1') ? trans('student.male'):trans('student.female'),
                                                   "section"       => $registration->section,
                                                   "level"         => $registration->level,
                                                   "block"         => $registration->dormitory,
                                                   "dormitory"     => $registration->dormitoryRoom,
                                                   "date"          => @$registration->ddate,
                                               ];
                                           } );

        }*/

    /**
     * @return mixed
     */
    private function generateParams()
    {
        $students = $this->studentRepository->getAllForSchoolYearAndSection2(session('current_company_year'), session('current_company'))
                                                ->with('user')
                                                ->get()
                                                ->map(function ($item) {
                                                    return [
                                                        'id'   => $item->user_id,
                                                        'name' => (isset($item->user) ? $item->user->full_name : '').' '.$item->student_no,
                                                    ];
                                                })->pluck('name', 'id')->toArray();

        view()->share('students', $students);

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();
        view()->share('sections', $sections);

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();
        view()->share('levels', $levels);

        $dormitories = $this->dormitoryRepository->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_dormitory'), 0)
            ->toArray();
        view()->share('dormitories', $dormitories);

        $dormitory_rooms = $this->dormitoryRoomRepository->getFreeForSchool(session('current_company'))
            ->with('dormitory')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => (isset($item->dormitory) ? $item->dormitory->title : '').' '.$item->title.' '.'('.$item->occupancy.' Occupancy) ',
                ];
            })->pluck('title', 'id')
            ->toArray();
        view()->share('dormitory_rooms', $dormitory_rooms);
    }

    public function subjectsStudents(DormitoryRoom $studentGroup)
    {
        return response()->json(['occupancy' => $studentGroup->occupancy]);
    }
}
