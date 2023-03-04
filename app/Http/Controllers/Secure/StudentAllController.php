<?php
namespace App\Http\Controllers\Secure;

use App\Exports\StudentsExport;
use App\Helpers\CustomFormUserFields;
use App\Helpers\ExcelfileValidator;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\StudentImportRequest;
use App\Helpers\Thumbnail;
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
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use DB;
use Sentinel;
use App\Http\Requests\Secure\StudentRequest;

class StudentAllController extends SecureController
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

        view()->share('type', 'all');

        $columns = ['id','sID','full_name','session', 'programme', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('student.all_students');

        $maleStudents = $this->studentRepository
            ->getAllMale()
            ->get();

        $femaleStudents = $this->studentRepository
            ->getAllFemale()
            ->get();

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

        $semesters = $this->semesterRepository
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


        return view('student.all', compact('title', 'maleStudents', 'femaleStudents', 'sections', 'sessions', 'schoolyears', 'semesters', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'countries2', 'maritalStatus', 'religions', 'graduationyears'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('student.new');
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
            ->prepend(trans('student.select_marital_status'), '')
            ->toArray();

        $religion = $this->religionRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_religion'), '')
            ->toArray();


        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'student_document_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            });
        $custom_fields =  CustomFormUserFields::getCustomUserFields('student');
        return view('layouts.create', compact('title', 'sections', 'sessions', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'maritalStatus', 'religion', 'document_types', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StudentRequest $request
     * @return Response
     */
    public function store(StudentRequest $request)
    {
        $user = $this->studentRepository->create($request->except('document', 'document_id', 'image_file'));

        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $user->picture = $picture;
            $user->save();
        }

        if ($request->hasFile('document') != "") {
            $file = $request->file('document');
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
        CustomFormUserFields::storeCustomUserField('student', $user->id, $request);

        return redirect('/student');
    }

    /**
     * Display the specified resource.
     *
     * @param Student $student
     * @return Response
     */
    public function show(Student $student)
    {
        $title = trans('student.details');
        $action = 'show';
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);
        return view('layouts.show', compact('student', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Student $student
     * @return Response
     */
    public function edit(Student $student)
    {
        $title = trans('student.edit');
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


        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'student_document_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            });
        $documents = UserDocument::where('user_id', $student->user->id)->first();
        $custom_fields =  CustomFormUserFields::fetchCustomValues('student', $student->user_id);
        return view('layouts.edit', compact('title', 'student', 'sections', 'sessions', 'directions', 'levels', 'intakeperiods', 'entrymodes', 'campus', 'countries', 'maritalStatus', 'religion', 'document_types', 'documents', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StudentRequest $request
     * @param Student $student
     * @return Response
     */
    public function update(StudentRequest $request, Student $student)
    {
        $student->update($request->only('section_id', 'order', 'section_id', 'direction_id', 'level_of_adm', 'level_id', 'entry_mode_id', 'intake_period_id', 'campus_id', 'country_id', 'marital_status_id', 'no_of_children', 'religion_id', 'denomination', 'disability', 'contact_relation', 'contact_name', 'contact_address', 'contact_phone', 'contact_email', 'session_id'));
        $student->save();
        if ($request->password != "") {
            $student->user->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $student->user->picture = $picture;
            $student->user->save();
        }

        $student->user->update($request->except('section_id', 'order', 'password', 'document', 'document_id', 'image_file'));

        if ($request->hasFile('document') != "") {
            $file = $request->file('document');
            $user = $student->user;
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
        CustomFormUserFields::updateCustomUserField('student', $student->user->id, $request);

        return redirect('/student');
    }

    /**
     * @param Student $student
     * @return Response
     */
    public function delete(Student $student)
    {
        $title = trans('student.delete');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);
        return view('/student/delete', compact('student', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Student $student
     * @return Response
     */
    public function destroy(Student $student)
    {
        $student->delete();
        return redirect('/student');
    }




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





    public function data()
    {
        $students = $this->studentRepository->getAllForSchool(session('current_company'))
            ->with('user', 'section')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'sID' => isset($student->user) ? $student->sID : "",
                    'full_name' => isset($student->user) ? $student->user->full_name : "",
                    'session' => isset($student->section) ? $student->section->title : "",
                    'programme' => isset($student->programme) ? $student->programme->title : "",
                    'user_id' => $student->user_id,
                    'active' => isset($student->active) ? $student->active->count() : "",
                    'defer' => isset($student->deferred) ? $student->deferred->count() : ""
                ];
            });
        return Datatables::make($students)
            ->addColumn('actions', '<!--@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif-->
                                    <!--<a href="{{ url(\'/report/\' . $user_id . \'/forstudent\' ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-bar-chart"></i>  {{ trans("table.report") }}</a>-->

                                      @if($active==0)

                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-danger btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("student.notactive") }}</a>

                                     @elseif($defer>0)

                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("student.deferred") }}</a>

                                           @else
                                     <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-sm btn-success" >
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i> {{trans("student.active") }}
                                            </a>
                                           @endif






                                    <!--<a href="{{ url(\'/student_card/\' . $id ) }}" target="_blank" class="btn btn-success btn-sm" >
                                            <i class="fa fa-credit-card"></i>  {{ trans("student.student_card") }}</a>-->
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student.delete\', Sentinel::getUser()->permissions)))
                                     <!--<a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->
                                      @endif')

            ->removeColumn('user_id')
            ->removeColumn('active')
            ->rawColumns([ 'actions' ])->make();
    }

    public function getImport()
    {
        $title = trans('student.import_student');

        return view('student.import', compact('title'));
    }


    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'first_name'=>$request->get('first_name')[$item],
                'last_name'=>$request->get('last_name')[$item],
                'email'=>$request->get('email')[$item],
                'password'=>$request->get('password')[$item],
                'mobile'=>$request->get('mobile')[$item],
                'fax'=>$request->get('fax')[$item],
                'birth_date'=>$request->get('birth_date')[$item],
                'birth_place'=>$request->get('birth_place')[$item],
                'address'=>$request->get('address')[$item],
                'order'=>$request->get('order')[$item],
                'section_id'=>$request->get('section_id')[$item],
                'student_group_id'=>$request->get('student_group_id')[$item],
                'gender'=>$request->get('gender')[$item],
            ];
            $this->studentRepository->create($import_data);
        }

        return redirect('/student');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/students.xlsx'));
    }

    public function studentFilter(Request $request)
    {
        $students = $this->studentRepository->getAllFilter($request)->get();
        return view('student.allFilteredList', ['students' => $students], ['count' => '1']);//
    }

    public function generalExport(Request $request)
    {
        $students = $this->studentRepository->getAllExport($request);
        return Excel::download(new StudentsExport($students), 'students.xlsx');

    }
}
