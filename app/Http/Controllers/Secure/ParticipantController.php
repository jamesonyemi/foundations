<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Helpers\ExcelfileValidator;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\StudentRequest;
use App\Models\Center;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Session;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Models\UserDocument;
use App\Notifications\ConferenceInvitationNotification;
use App\Notifications\ConferenceRegistrationNotification;
use App\Repositories\CommitteeRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\LevelRepository;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Sentinel;

class ParticipantController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $committeeRepository;

    /**
     * @var SectionRepository
     */
    private $studentRepository;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var ExcelRepository
     */
    private $excelRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var DirectionRepository
     */
    private $directionRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var SchoolYearRepository
     */
    private $schoolYearRepository;

    /**
     * @var SessionRepository
     */
    private $employeeRepository;
    /**
     * @var SessionRepository
     */

    /**
     * StudentController constructor.
     *
     * @param StudentRepository $studentRepository
     * @param LevelRepository $levelRepository
     * @param DirectionRepository $directionRepository
     * @param SchoolYearRepository $schoolYearRepository
     * @param SectionRepository $sectionRepository
     */
    public function __construct(
        CommitteeRepository $committeeRepository,
        StudentRepository $studentRepository,
        EmployeeRepository $employeeRepository,
        LevelRepository $levelRepository,
        DirectionRepository $directionRepository,
        SchoolYearRepository $schoolYearRepository,
        SectionRepository $sectionRepository

    ) {
        parent::__construct();
        $this->committeeRepository = $committeeRepository;
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->sectionRepository = $sectionRepository;
        $this->levelRepository = $levelRepository;
        $this->directionRepository = $directionRepository;
        $this->schoolYearRepository = $schoolYearRepository;

        $this->middleware('authorized:student.show',
            ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.create', [
            'only' => [
                'create',
                'store',
                'getImport',
                'postImport',
                'downloadTemplate',
            ],
        ]);
        /*$this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'participant');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Participants Registration';

        return view('participant.index', compact('title', ));
    }

    public function registeredList()
    {
        $title = 'Registered Participants';
        $data = $this->employeeRepository->getAllForSchoolYearAndSchool(session('current_company_year'), $this->currentEmployee->center_id)
            ->with('user')
            ->get();

        return view('participant.registered_list', compact('title', 'data'), ['count' => '1']);
    }

    public function registeredListAll()
    {
        $title = 'Registered Participants';
        $data = $this->employeeRepository->getAllForSchoolYearAndSchoolAll(session('current_company_year'))
            ->with('user')
            ->get();

        return view('participant.registered_list', compact('title', 'data'), ['count' => '1']);
    }

    private function generateParams()
    {
        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id');

        $committees = $this->committeeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id');

        $centers = Center::
        get()
            ->pluck('name', 'id');

        $levels = $this->levelRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id');

        /*$employees = $this->employeeRepository->getAll()
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id" => $item->id,
                    "name" => (isset($item->user) ? $item->user->full_name : "") . ' ' . str_pad($item->sID, 9, '0', STR_PAD_LEFT),
                ];
            })->pluck("name", 'id')
            ->prepend('Select Employee', 0)
            ->toArray();*/

        $data = $this->employeeRepository->getAllForSchoolYearAndSchool(session('current_company_year'))
            ->with('user')
            ->orderBy('sID', 'Desc')
            ->get();

        /*view()->share('employees', $employees);*/
        view()->share('data', $data);
        view()->share('sections', $sections);
        view()->share('committees', $committees);
        view()->share('levels', $levels);
        view()->share('centers', $centers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('student.new');
        $this->generateParams();
        $custom_fields = CustomFormUserFields::getCustomUserFields('student');

        return view('layouts.create', compact('title', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StudentRequest $request
     *
     * @return Response
     */
    public function store(StudentRequest $request)
    {
        $user = $this->studentRepository->create($request->except('document', 'document_id', 'image_file'));

        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $user->picture = $picture;
            $user->save();
        }

        if ($request->hasFile('document') != '') {
            $file = $request->file('document');
            $extension = $file->getClientOriginalExtension();
            $document = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/documents/';
            $file->move($destinationPath, $document);

            UserDocument::where('user_id', $user->id)->delete();

            $userDocument = new UserDocument;
            $userDocument->user_id = $user->id;
            $userDocument->document = $document;
            $userDocument->option_id = $request->get('document_id');
            $userDocument->save();
        }
        CustomFormUserFields::storeCustomUserField('student', $user->id, $request);

        /*$user->notify(new SendSMS($user));*/

        return redirect('/student')->with('status', 'Participant Successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param Student $student
     *
     * @return Response
     */
    public function show(Employee $employee)
    {
        $title = trans('student.details');
        $action = 'show';
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('student', $employee->user_id);

        return view('layouts.show', compact('employee', 'title', 'action', 'custom_fields'));
    }

    public function getParticipant(Request $request)
    {
        $title = trans('student.details');
        $action = 'show';
        try {
            $employee = Employee::where('sID', $request->id)->first();

            if (! isset($employee)) {
                return response()->json(['exception'=>'No Employee Record found with the given ID']);
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return view('participant.register', compact('employee', 'title', 'action'));
    }

    public function showParticipantRecord(Request $request)
    {
        try {
            $employee = Employee::findOrFail($request->id);
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return view('participant.register', compact('employee'));
    }

    public function searchParticipant(Request $request)
    {
        try {
            $employees = Employee::whereHas('user', function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->id.'%')->orWhere('middle_name', 'like', '%'.$request->id.'%')->orWhere('last_name', 'like', '%'.$request->id.'%');
            })->get()->take(100);

            if (! isset($employees)) {
                return response()->json(['exception'=>'No Employee Record found with the given name']);
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return view('participant.search_list', compact('employees', ));
    }

    /*
    public function register(Employee $employee)
    {
        $title = trans('student.details');
        $action = 'show';

        $this->generateParams();

        return view('participant.register', compact('employee', 'title', 'action'));
    }*/

    public function registerStore(Employee $employee)
    {
        if ($this->currentEmployee->center_id == 0) {
            return response()->json(['exception'=>'OOPS!!, Your Center ID is Not Set']);
        }

        try {
            StudentStatus::firstOrCreate(
            [
                'company_year_id' => session('current_company_year'),
                'employee_id' => $employee->id,
            ],
            [
                'company_id' => session('current_company'),
                'center_id' => $this->currentEmployee->center_id,
            ]
        );
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        //send email to user
        if (GeneralHelper::validateEmail($employee->user->email)) {
            @Notification::send($employee->user, new ConferenceRegistrationNotification($employee->user, $this->quarter));
        }

        return response('<div class="alert alert-success">Participant Registered Successfully</div>');
    }

    public function registerStore2(StudentRequest $request, Employee $employee)
    {
        $employee->center_id = $request->center_id;
        $employee->save();
        StudentStatus::firstOrCreate(['company_id' => session('current_company'),
            'company_year_id' => session('current_company_year'),
            'employee_id' => $employee->id, ]);

        return redirect('/student')->with('status', ''.$employee->user->full_name.' Registered Successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Student $student
     *
     * @return Response
     */
    public function edit(Employee $employee)
    {
        $title = trans('student.edit');
        $this->generateParams();

        $documents = UserDocument::where('user_id', $employee->user->id)->first();
        $custom_fields = CustomFormUserFields::fetchCustomValues('student', $employee->user_id);

        return view('layouts.edit', compact('title', 'employee', 'documents', 'custom_fields'));
    }

    public function meeba(Employee $employee)
    {
        $studentStatus = StudentStatus::
        where('company_year_id', session('current_company_year'))
            ->where('student_id', $employee->id)->first();
        $studentStatus->confirm = '1';
        /*$studentStatus->confirm_date = now();*/
        $studentStatus->save();

        return redirect('/')->with('status', 'Confirmed Successfully!');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StudentRequest $request
     * @param Student $student
     *
     * @return Response
     */

    /**
     * @param Student $student
     *
     * @return Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param Student $student
     *
     * @return Response
     */
    public function destroy(Employee $employee)
    {
        try {
            StudentStatus::where('employee_id', $employee->id)->where('company_year_id', session('current_company_year'))->delete();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Deleted Successful!!!</div>');
    }

    public function data($first_name = null,
                         $last_name = null,
                         $student_no = null,
                         $session_id = null, $section_id = null,
                         $level_id = null,
                         $entry_mode_id = null,
                         $gender = null, $marital_status_id = null, $dormitory_id = null)
    {
        $request = ['first_name' => $first_name, 'last_name' => $last_name,
            'student_no' => $student_no,
            'section_id' => $section_id, 'level_id' => $level_id,
            'gender' => $gender, ];
        $students = $this->employeeRepository->getAllForSchoolWithFilter(session('current_company'), session('current_company_year'), $request)
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_no' => str_pad($student->student_no, 4, '0', STR_PAD_LEFT),
                    'full_name' => $student->full_name,
                    'gender' => ($student->gender == '1') ? trans('student.male') : trans('student.female'),
                    'phone' => $student->User->mobile,
                    'section' => $student->section,
                    'level' => $student->level,
                    'user_id' => $student->user_id,
                    'room' => Employee::find($student->id)->dormitory->Count(),
                ];
            });

        return Datatables::make($students)
            ->addColumn('actions', '
                                    <!--<a href="{{ url(\'/report/\' . $user_id . \'/forstudent\' ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-bar-chart"></i>  {{ trans("table.report") }}</a>-->
                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.edit") }}</a>
                                     @if($room==0)
                                    <a href="{{ url(\'/registration/create\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.no_allocation") }}</a>
                                    @endif

                                    <a href="{{ url(\'/student/\' . $id . \'/invite\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-eye"></i>  Send Invitation</a>')
            ->removeColumn('id')
            ->removeColumn('user_id')
            ->removeColumn('room')
            ->rawColumns(['actions'])->make();
    }

    public function getImport()
    {
        $title = trans('student.import_student');

        return view('student.import', compact('title'));
    }

    public function postImport(ImportRequest $request)
    {
        $title = trans('student.import_student');

        ExcelfileValidator::validate($request);

        $reader = $this->excelRepository->load($request->file('file'));

        $students = $reader->all()->map(function ($row) {
            return [
                'first_name' => trim($row->first_name),
                'last_name' => trim($row->last_name),
                'email' => trim($row->email),
                'password' => trim($row->password),
                'mobile' => trim($row->mobile),
                'gender' => intval($row->gender),
            ];
        });

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()->map(function ($section) {
                return [
                    'text' => $section->title,
                    'id' => $section->id,
                ];
            })->pluck('text', 'id');

        $levels = $this->levelRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id');

        return view('student.import_list', compact('students', 'sections', 'title', 'levels'));
    }

    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'first_name' => $request->get('first_name')[$item],
                'last_name' => $request->get('last_name')[$item],
                'email' => $request->get('email')[$item],
                'password' => $request->get('password')[$item],
                'mobile' => $request->get('mobile')[$item],
                'section_id' => $request->get('section_id')[$item],
                'level_id' => $request->get('level_id')[$item],
                'gender' => $request->get('gender')[$item],
            ];
            $this->studentRepository->create($import_data);
        }

        return redirect('/student');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/participants.xlsx'));
    }

    public function export()
    {
        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($student) {
                return [
                    'No' => str_pad($student->student_no, 4, '0', STR_PAD_LEFT),
                    'Name' => isset($student->user) ? $student->user->full_name : '',
                    'Subsidiary' => isset($student->section) ? $student->section->title : '',
                    'Gender' => ($student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => isset($student->level) ? $student->level->name : '',
                    'Phone' => isset($student->user) ? $student->user->mobile : '',
                    'Email' => isset($student->user) ? $student->user->email : '',
                    'Committee' => isset($student->committee) ? $student->committee->title : 'NON COMMITTEE',

                    /* 'Presence'     => ($student->attended=='1') ? "ATTENDED" : "NON ATTENDED",*/   /*Student::find($student->id)->dormitory->Count(),*/
                ];
            })->toArray();

        Excel::create(trans('student.student'), function ($excel) use ($students) {
            $excel->sheet(trans('student.student'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    /**
     * @param Session $session
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSectionBySession(Session $session)
    {
        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchoolAndSession(session('current_company_year'), session('current_company'), $session->id)
            ->select('title', 'id')
            ->get();

        return response()->json(['sections' => $sections]);
    }

    /**
     * @param Section $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLevelsBySection(Section $section)
    {
        $levels = $this->levelRepository->getAllForSection($section->id)
            ->select('name', 'id')
            ->get();

        $student_groups = $this->studentGroupRepository->getAllForSection($section->id)
            ->select('title', 'id')
            ->get();

        return response()->json(['levels' => $levels, 'student_groups' => $student_groups]);
    }

    public function studentFilter(Request $request)
    {
        $query = Employee::join('users', 'users.id', '=', 'students.user_id')
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->leftJoin('levels', 'levels.id', '=', 'students.level_id')
            ->whereNull('users.deleted_at')
            ->whereNull('sections.deleted_at')
            ->where('students.company_id', session('current_company'))
            ->where('students.company_year_id', session('current_company_year'));

        if (isset($request->fname_x) && ! empty($request->fname_x)) {
            $query = $query->where('users.first_name', 'LIKE', "$request->fname_x%")->orwhere('users.middle_name', 'LIKE', "$request->fname_x%");
        }

        if (isset($request->name_x) && ! empty($request->name_x)) {
            $query = $query->where('users.surname', 'LIKE', "$request->name_x%");
        }

        if (isset($request->id) && ! empty($request->id)) {
            $query = $query->where('students.student_no', '=', $request->id);
        }

        // Get the results
        // After this call, it is now an Eloquent model
        $students2 = $query->get();

        return view('student.studentfilteredlist', ['students2' => $students2], ['count' => '1']); //
    }

    public function invite(Employee $employee)
    {

        //send email to user
        if (GeneralHelper::validateEmail($employee->user->email)) {
            @Notification::send($employee->employee->user, new ConferenceInvitationNotification($employee->employee->user, $center));
        }

        return response('<div class="alert alert-success">Invitation Sent Successfully</div>');
    }

    public function exportStudentWithOutRoom()
    {
        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->with('user', 'section')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {
                return [
                    'No' => str_pad($student->id, 4, '0', STR_PAD_LEFT),
                    'Name' => isset($student->user) ? $student->user->full_name : '',
                    'Subsidiary' => isset($student->section) ? $student->section->title : '',
                    'Gender' => ($student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => isset($student->level) ? $student->level->name : '',
                    'Committee' => isset($student->committee) ? $student->committee->title : 'NON COMMITTEE',

                    'Block' => $this->showDormitoryDetails($student->student_no),
                    'Room' => $this->showRoomDetail($student->student_no),

                ];
            })->toArray();

        Excel::create(trans('student.student'), function ($excel) use ($students) {
            $excel->sheet(trans('student.student'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    private function showDormitoryDetails($studentNo)
    {
        try {
            $confirmStatus = $this->getRoomStatus($studentNo);
            if (isset($confirmStatus) && isset($confirmStatus->dormitory)) {
                return $confirmStatus->dormitory[0]['title'];
            }

            return 'Not Assigned';
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function getRoomStatus($studentNo)
    {
        $participant = Employee::with(['user:id,first_name,last_name,title,last_login,picture,mobile,gender',
            'department:id,title',
            'dormitory', 'room', 'position:id,title', 'isActive',
        ])->where('company_id', session('current_company'))
            ->where('company_year_id', session('current_company_year'))
            ->where('student_no', $studentNo)->first();
        if (! is_null($participant)) {
            return $participant;
        } else {
            return null;
        }
    }

    private function showRoomDetail($studentNo)
    {
        try {
            $confirmStatus = $this->getRoomStatus($studentNo);
            if (isset($confirmStatus) && isset($confirmStatus->room)) {
                return $confirmStatus->room[0]['title'];
            }

            return ' Not Assigned';
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}
