<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Helpers\ExcelfileValidator;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\StudentRequest;
use App\Models\Section;
use App\Models\Session;
use App\Models\SmsMessage;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Models\User;
use App\Models\UserDocument;
use App\Notifications\AttendedSms;
use App\Repositories\DirectionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

/*
use App\Repositories\DenominationRepository;
use App\Repositories\DormitoryRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\OptionRepository;*/
/*use App\Repositories\SessionRepository;
use App\Repositories\StudentGroupRepository;*/
/*use App\Repositories\IntakePeriodRepository;
use App\Repositories\EntryModeRepository;
use App\Repositories\CountryRepository;*/

/*use App\Repositories\MaritalStatusRepository;
use App\Repositories\ReligionRepository;*/

class AttendedController extends SecureController
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
     * @var IntakePeriodRepository
     */
    private $intakePeriodRepository;

    /**
     * @var EntryModeRepository
     */
    private $entryModeRepository;

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @var MaritalStatusRepository
     */
    private $maritalStatusRepository;

    /**
     * @var ReligionRepository
     */
    private $religionRepository;

    /**
     * @var SchoolYearRepository
     */
    private $schoolYearRepository;

    /**
     * @var SessionRepository
     */
    private $sessionRepository;

    /**
     * @var StudentGroupRepository
     */
    private $studentGroupRepository;

    /**
     * @var DenominationRepository
     */
    private $denominationRepository;

    /**
     * @var DormitoryRepository
     */
    private $dormitoryRepository;

    /**
     * StudentController constructor.
     *
     * @param StudentRepository $studentRepository
     * @param OptionRepository $optionRepository
     * @param ExcelRepository $excelRepository
     * @param LevelRepository $levelRepository
     * @param EntryModeRepository $entryModeRepository
     * @param IntakePeriodRepository $intakePeriodRepository
     * @param CountryRepository $countryRepository
     * @param MaritalStatusRepository $maritalStatusRepository
     * @param ReligionRepository $religionRepository
     * @param DirectionRepository $directionRepository
     * @param SchoolYearRepository $schoolYearRepository
     * @param SessionRepository $sessionRepository
     * @param SectionRepository $sectionRepository
     * @param StudentGroupRepository $studentGroupRepository
     * @param DenominationRepository $denominationRepository
     * @param DormitoryRepository $dormitoryRepository
     */
    public function __construct(
        StudentRepository $studentRepository,
        /*	OptionRepository $optionRepository,
            ExcelRepository $excelRepository,*/
        LevelRepository $levelRepository,
        /*	EntryModeRepository $entryModeRepository,
            IntakePeriodRepository $intakePeriodRepository,
            CountryRepository $countryRepository,
            MaritalStatusRepository $maritalStatusRepository,
            ReligionRepository $religionRepository,*/
        DirectionRepository $directionRepository,
        SchoolYearRepository $schoolYearRepository,
        /*SessionRepository $sessionRepository,*/
        SectionRepository $sectionRepository
    ) {
        parent::__construct();
        $this->studentRepository = $studentRepository;

        $this->sectionRepository = $sectionRepository;
        $this->levelRepository = $levelRepository;
        $this->directionRepository = $directionRepository;
        $this->schoolYearRepository = $schoolYearRepository;

        $this->middleware('authorized:student.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.create', [
            'only' => [
                'create',
                'store',
                'getImport',
                'postImport',
                'downloadTemplate',
            ],
        ]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'attended');

        $columns = ['student_no', 'full_name', 'gender', 'phone', 'section', 'level', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Participants Present';

        $this->generateParams();

        $sectionsChart = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->select('title', 'id')
            ->get();

        $maleStudents = $this->studentRepository->getAllMalePresent()->count();
        $femaleStudents = $this->studentRepository->getAllFemalePresent()->count();

        return view('attended.index', compact('title', 'sectionsChart', 'maleStudents', 'femaleStudents'));
    }

    private function generateParams()
    {
        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id');

        $levels = $this->levelRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id');

        $students = $this->studentRepository->getAllForSchoolYearAndSection(session('current_company_year'), session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => (isset($item->user) ? $item->user->full_name : '').' '.str_pad($item->student_no, 4, '0', STR_PAD_LEFT),
                ];
            })->pluck('name', 'id')
            ->prepend(trans('student.select_participants'), 0)
            ->toArray();

        $data = $this->studentRepository->getAllForSchoolWithFilterPresent(session('current_company'), session('current_company_year'));

        view()->share('students', $students);
        view()->share('data', $data);
        view()->share('levels', $levels);
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

        return redirect('/student');
    }

    /**
     * Display the specified resource.
     *
     * @param Student $student
     *
     * @return Response
     */
    public function show(Student $student)
    {
        $title = trans('student.details');
        $action = 'show';

        return view('attended.register', compact('student', 'title', 'action'));
    }

    public function attend(Student $student)
    {
        $title = trans('student.student');

        $this->generateParams();

        $sectionsChart = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->select('title', 'id')
            ->get();

        $maleStudents = $this->studentRepository->getAllMalePresent()->count();
        $femaleStudents = $this->studentRepository->getAllFemalePresent()->count();

        $studentStatus = StudentStatus::where('student_id', $student->id)->where('school_year_id', session('current_company_year'))->first();
        $studentStatus->attended = '1';
        $studentStatus->attended_date = now();
        $studentStatus->save();

        $message = 'You are warmly welcome to JLC 2020.For assistance call ICE on 0244654291.Thank you';

        $this->SendSmsAfterRegistration($student->user->id, $message);

        return view('attended.index', compact('title', 'sectionsChart', 'maleStudents', 'femaleStudents'))
            ->with('status', 'Attendance Recorded Successfully!');
    }

    /**
     * @param Section $section
     * @return \Illuminate\Http\JsonResponse
     */
    private function SendSmsAfterRegistration($user_id, $message)
    {
//        $school = School::find(session('current_company'))->first();
        /*if($school->limit_sms_messages == 0 ||
           $school->limit_sms_messages > $school->sms_messages_year) {*/

        try {
            $theUser = User::find($user_id);
            if (! is_null($theUser) && strlen($theUser->mobile) > 9 && strlen($theUser->mobile) < 17) {
                $when = Carbon::now()->addMinutes(2);
                //Notification::send(new AttendedSms($theUser, $message))->delay($when);
                $theUser->notify(new AttendedSms($theUser, $message));

                $smsMessage = new SmsMessage();
                $smsMessage->text = $message;
                $smsMessage->number = $theUser->mobile;
                $smsMessage->user_id = $user_id;
                $smsMessage->user_id_sender = $this->user->id;
                $smsMessage->company_id = session('current_company');
                $smsMessage->save();
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Student $student
     *
     * @return Response
     */
    public function edit(Student $student)
    {
        $title = trans('student.edit');
        $this->generateParams();

        $documents = UserDocument::where('user_id', $student->user->id)->first();
        $custom_fields = CustomFormUserFields::fetchCustomValues('student', $student->user_id);
        $levels = $this->levelRepository->getAll()
            ->pluck('name', 'id');

        $student_groups_select = $this->studentGroupRepository->getAllForSection($student->section_id)
            ->pluck('title', 'id');

        return view('layouts.edit', compact('title', 'student', 'documents', 'custom_fields', 'levels', 'student_groups_select'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StudentRequest $request
     * @param Student $student
     *
     * @return Response
     */
    public function update(StudentRequest $request, Student $student)
    {
        $student->update($request->only('section_id', 'order',
            'level_of_adm', 'level_id', 'intake_period_id', 'campus_id'));
        $student->save();
        if ($request->password != '') {
            $student->user->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $student->user->picture = $picture;
            $student->user->save();
        }

        $student->user->update($request->except('section_id', 'order', 'password', 'document', 'document_id', 'image_file',
            'entry_mode_id', 'country_id', 'marital_status_id', 'no_of_children', 'religion_id', 'denomination',
            'disability', 'contact_relation', 'contact_name', 'contact_address', 'contact_phone',
            'contact_email'));

        if ($request->hasFile('document') != '') {
            $file = $request->file('document');
            $user = $student->user;
            $extension = $file->getClientOriginalExtension();
            $document = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/documents/';
            $file->move($destinationPath, $document);

            UserDocument::where('user_id', $user->id)->delete();

            $userDocument = new UserDocument;
            $userDocument->user_id = $user->id;
            $userDocument->document = $document;
            $userDocument->option_id = $request->document_id;
            $userDocument->save();
        }
        CustomFormUserFields::updateCustomUserField('student', $student->user->id, $request);

        return redirect('/student');
    }

    /**
     * @param Student $student
     *
     * @return Response
     */
    public function delete(Student $student)
    {
        $title = trans('student.delete');
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);

        return view('/student/delete', compact('student', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Student $student
     *
     * @return Response
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect('/student');
    }

    public function data($first_name = null, $last_name = null, $student_no = null,
                         $session_id = null, $section_id = null, $level_id = null, $entry_mode_id = null,
                         $gender = null, $marital_status_id = null, $dormitory_id = null)
    {
        $request = ['first_name' => $first_name, 'last_name' => $last_name,
            'student_no' => $student_no,
            'section_id' => $section_id, 'level_id' => $level_id,
            'gender' => $gender, ];
        $students = $this->studentRepository->getAllForSchoolWithFilterPresent(session('current_company'), session('current_company_year'), $request)
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
                ];
            });

        return Datatables::make($students)
            ->addColumn('actions', '
                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <!--@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                      @endif-->')
            ->removeColumn('id')
            ->removeColumn('user_id')
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
        return response()->download(base_path('resources/excel-templates/students.xlsx'));
    }

    public function export()
    {
        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->with('user', 'section')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {
                return [
                    'No' => $student->student_no,
                    'Subsidiary' => isset($student->section) ? $student->section->title : '',
                    'Name' => isset($student->user) ? $student->user->full_name : '',
                    /*'Order'        => $student->order,*/
                    /*'Student id'   => $student->id*/
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
}
