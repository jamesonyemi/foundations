<?php
namespace App\Http\Controllers\Secure;

use App\Exports\StudentsExport;
use App\Http\Requests\Secure\SmsMessageRequest;
use App\Helpers\CustomFormUserFields;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\EnrollRequest;
use App\Http\Requests\Secure\StudentImportRequest;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\StudentNoteRequest;
use App\Http\Requests\Secure\UpgradeRequest;
use App\Mail\AdmissionApproverEmail;
use App\Mail\StudentApproveMail;
use App\Models\Applicant;
use App\Models\FeeCategory;
use App\Models\FeesStatus;
use App\Models\GeneralLedger;
use App\Models\Invoice;
use App\Models\MoodleUser;
use App\Models\RoleUser;
use App\Models\Section;
use App\Models\StudentAdmission;
use App\Models\Subject;
use App\Models\User;
use App\Models\Student;
use App\Models\Letter;
use App\Models\School;
use App\Models\StudentNote;
use App\Models\StudentUpgrade;
use App\Models\SmsMessage;
use App\Models\StudentStatus;
use App\Repositories\ActivityLogRepository;
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
use App\Helpers\Flash;
use Illuminate\Http\Request;
use Sentinel;
use App\Http\Requests\Secure\StudentRequest;
use App\Notifications\SendSMS;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class StudentController extends SecureController
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
    protected $activity;
    protected $module = 'Student';



    /**
     * StudentController constructor.
     * @param StudentRepository $studentRepository
     * @param OptionRepository $optionRepository
     * @param ExcelRepository $excelRepository
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
        SectionRepository $sectionRepository,
        ActivityLogRepository $activity
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
        $this->activity = $activity;

        $this->middleware('authorized:student.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'student');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        /*if (!Sentinel::hasAccess('student.show')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $title = trans('student.active_students');
        $maleStudents = $this->studentRepository
            ->getAllMale()
            ->get();

        $femaleStudents = $this->studentRepository
            ->getAllFemale()
            ->get();

        $this->filterParams();

        $students = $this->studentRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'programme')
            ->get();


        $count = 1;


        return view('student.index', compact('title', 'maleStudents', 'femaleStudents', 'students',  'count'));
    }


   public function pendingApproval()
    {
        if (!Sentinel::hasAccess('student.approveinfo')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title = trans('student.pending_students');

        $this->filterParams();

        $students = $this->studentRepository->getAllPendingApproval(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'programme')
            ->get();


        $count = 1;


        return view('student.pendingApproval', compact('title','students',  'count'));
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
        $custom_fields =  CustomFormUserFields::getCustomUserFields('student');
        return view('layouts.create', compact(
            'title',
            'custom_fields'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StudentRequest $request
     * @return Response
     */


    public function store(StudentRequest $request)
    {

        if (!Sentinel::hasAccess('student.create')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }
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



        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $user->student->id,
            'activity'  => 'created'
        ]);

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
        $title = $student->user->full_name;
        $action = 'show';
        $schoolType = session('current_company_type');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);
        $count = 1;
        return view('layouts.show', compact('student', 'title', 'action', 'custom_fields', 'schoolType', 'count'));
    }


    public function showModal(Student $student)
    {
        $title = $student->user->full_name;
        $action = 'show';
        $schoolType = session('current_company_type');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);
        $count = 1;
        return view('student.showModal', compact('student', 'title', 'action', 'custom_fields', 'schoolType', 'count'));
    }

    public function upgrade(Student $student)
    {
        $title = $student->user->full_name;
        $this->generateParams();
        $action = 'upgrade';
        $schoolType = session('current_company_type');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);
        return view('student.studentUpgrade', compact('student', 'title', 'action',
            'custom_fields'));
    }



    public function storeUpgrade(UpgradeRequest $request)
    {
       /* @$existStatus =StudentUpgrade::where('old_student_id', $request->old_student_id);
        if (is_null($existStatus))
        {*/

        $student = $this->studentRepository->enroll($request->All());

        $studentUpgrade = new StudentUpgrade();
        $studentUpgrade->old_dep_id = $request->old_section_id;
        $studentUpgrade->old_prog_id = $request->old_direction_id;
        $studentUpgrade->old_academic_year_id = $request->old_academic_year_id;
        $studentUpgrade->old_ssesion_id = $request->old_session_id;
        $studentUpgrade->old_sID = $request->old_student_sID;
        $studentUpgrade->old_student_id = $request->old_student_id;

        $studentUpgrade->new_dep_id = $student->section_id;
        $studentUpgrade->new_prog_id = $student->direction_id;
        $studentUpgrade->new_ssesion_id = $student->session_id;
        $studentUpgrade->new_sID = $student->sID;

        $studentUpgrade->student_id = $student->id;
        $studentUpgrade->admin_user_id = Sentinel::getUser()->id;
        $studentUpgrade->company_id = session('current_company');
        $studentUpgrade->school_year_id = session('current_company_year');
        $studentUpgrade->semester_id = session('current_company_semester');
        $studentUpgrade->save();


        $oldStudent = Student::findOrFail($request->old_student_id)->delete();


        return redirect('/student/'. $student->id .'/show')
            ->with('status', 'Student Upgrade Successful!');




    }


    public function changeUserRole($user_id)
    {

        $roleUser = RoleUser::where('user_id', $user_id)->first();
        $roleUser->role_id = 16;
        $roleUser->save();
    }

    public function reverse_admission(Student $student)
    {

        $title = $student->user->full_name;
        $applicant_id = $student->applicant_id;


        $applicant = Applicant::where('id', $applicant_id)->first();
        $applicant->status = 1;
        $applicant->save();

        $this->changeUserRole($applicant->user_id);


        @$invoice = GeneralLedger::where('student_id', $student->id)
            ->where('semester_id', session('current_company_semester'))->first();


        @$feesStatus =FeesStatus::where('student_id', $student->id)
            ->where('semester_id', session('current_company_semester'));
       if (! is_null($feesStatus))
       {
        @$feesStatus->delete();
       }
        if (! is_null($invoice)) {
        @$invoice->delete();
        }
        @$student->delete();


        $action = 'show';
        $schoolType = session('current_company_type');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('applicant', $applicant->user_id);
        view()->share('applicant', $applicant);
        view()->share('title', $title);
        view()->share('action', $action);
        view()->share('custom_fields', $custom_fields);
        return redirect( url('/applicant/'.$applicant_id.'/show'));

    }

    public function generateAppFee(Student $student)
    {

      if($student->discount == 0)
      {
        @$invoice = GeneralLedger::where('student_id', $student->id)
            ->where('semester_id', session('current_company_semester'))->first();

        if (! is_null($invoice)) {
            @$invoice->delete();
        }


        $fees = FeeCategory::all()->where('section_id', $student->section_id)
            ->where('company_id', '=', session('current_company'));



        foreach ($fees as $fee) {


            $generalLedger = new GeneralLedger();
            $generalLedger->student_id = $student->id;
            $generalLedger->user_id = $student->user_id;
            $generalLedger->company_id = session('current_company');
            $generalLedger->school_year_id = session('current_company_year');
            $generalLedger->semester_id = session('current_company_semester');
            $generalLedger->narration = $fee->title;
            $generalLedger->account_id = $fee->credit_account_id;
            if ($student->country_id == 1){
                $generalLedger->credit = $fee->local_amount;
            }
            else{
                $generalLedger->credit = $fee->local_amount;
            }
            $generalLedger->fee_category_id = $fee->id;
            $generalLedger->transaction_date = now();
            $generalLedger->transaction_type = 'credit';
            $generalLedger->save();
        }


        $title = $student->user->full_name;
        $action = 'show';
        $schoolType = session('current_company_type');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('student', $student->user_id);


        return redirect('/student/'. $student->id .'/show')
            ->with('status', 'Student Information Updated Successfully!');
}

else
{
    return redirect('/student/'. $student->id .'/show')
        ->with('status', 'Discount Already Applied, Cannot Regenerate Fee!');
}
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Student $student
     * @return Response
     */
    public function edit(Student $student)
    {
        $title = 'Edit '. $student->user->full_name.'';
        $this->generateParams();
        $documents = UserDocument::where('user_id', $student->user->id)->first();
        $custom_fields =  CustomFormUserFields::fetchCustomValues('student', $student->user_id);
        return view('student.modal_form', compact('title', 'student', 'documents', 'custom_fields'));
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
        if (!Sentinel::hasAccess('student.edit')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        try
        {
            DB::transaction(function() use ($request, $student) {
        $student->update($request->only('section_id', 'order', 'section_id', 'direction_id', 'level_of_adm', 'level_id', 'entry_mode_id', 'intake_period_id', 'campus_id', 'school_year_id', 'graduation_year_id', 'semester_id', 'country_id', 'marital_status_id', 'no_of_children', 'religion_id', 'denomination', 'disability', 'contact_relation', 'contact_name', 'contact_address', 'contact_phone', 'contact_email', 'session_id', 'fee' , 'sID'));
        $student->save();
        if ($request->password != "") {
            $student->user->password = bcrypt($request->password);
            /*if($student->user->moodleUser)
            {
                $student->user->moodleUser->password = bcrypt($request->password);
                $student->user->moodleUser->save();
            }*/

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


        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $student->id,
            'activity'  => 'updated'
        ]);

        /*return redirect('/student/'. $student->id .'/show')
            ->with('status', 'Student Information Updated Successfully!');*/
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        return response('Student info Updated Successfully') ;

    }

    /**
     * @param Student $student
     * @return Response
     */
    public function delete(Student $student)
    {
        if (!Sentinel::hasAccess('student.delete')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $title = 'Delete '. $student->user->full_name.'';
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
        if (!Sentinel::hasAccess('student.delete')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }
        $student->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $student->id,
            'activity'  => 'Deleted'
        ]);
        return redirect('/student');
    }



    public function admissionLetter_(Student $student)
    {
        $title = trans('student.edit');
        $letter = Letter::where('company_id', $student->company_id)->first();
        $custom_fields =  CustomFormUserFields::fetchCustomValues('student', $student->user_id);
        return view('letters.duc_2', compact('title', 'student', 'custom_fields', 'letter'));
    }


    public function admissionLetter(Student $student)
    {
        $pdf = PDF::loadView('letters.duc', compact('student'));
        return @$pdf->stream(@$student->user->full_name.'.pdf', array('Attachment'=>0));
    }


    public function markFee(Request $request, Student $student)
    {
        if ($student->fee == 'Yes')
        {
            $student->fee = 'No';
        }
        elseif($student->fee = 'No')
        $student->fee = 'Yes';
        $student->save();
        return $student->fee;
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
                    "id"   => $item->id,
                    "sid"   => $item->sID,
                    "name" => isset($item->user) ? $item->user->full_name. ' ' .$item->sID : "",
                ];
            })->pluck("name", 'id')
               ->prepend(trans('student.select_student'), 0)
               ->toArray();

        return $students;
    }


    public function findSectionStudents2(Request $request)
    {
        $students = $this->studentRepository->getAllForSection2($request->section_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
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


    public function findDirectionStudents2(Request $request)
    {
        $students = $this->studentRepository->getAllForDirection($request->direction_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "sid"   => $item->sID,
                    "name" => isset($item->user) ? $item->user->full_name. ' ' .$item->sID : "",
                ];
            })->pluck("name", 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        return $students;
    }


    public function findSchoolYearStudents(Request $request)
    {
        $students = $this->studentRepository->getAllForSchoolYear($request->school_year_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
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
        $students = $this->studentRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {

                if (session('current_company_type')== 3) {
                    return [
                        'id' => $student->id,
                        'sID' => isset($student->user) ? $student->sID : "",
                        'full_name' => isset($student->user) ? $student->user->full_name : "",
                        'session' => isset($student->section) ? $student->section->title : "",
                        'programme' => isset($student->programme) ? $student->programme->title : "",
                        'user_id' => $student->user_id
                    ];
                }

                if (session('current_company_type')!= 3) {
                    return [
                        'id' => $student->id,
                        'sID' => isset($student->user) ? $student->sID : "",
                        'full_name' => isset($student->user) ? $student->user->full_name : "",
                        'session' => isset($student->section) ? $student->section->title : "",
                        'programme' => isset($student->level) ? $student->level->name : "",
                        'user_id' => $student->user_id
                    ];
                }
            });
        return Datatables::make($students)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student.edit\', Sentinel::getUser()->permissions)))
                                        <!--<a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>-->
                                    @endif
                                    <!--<a href="{{ url(\'/report/\' . $user_id . \'/forstudent\' ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-bar-chart"></i>  {{ trans("table.report") }}</a>-->
                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <!--<a href="{{ url(\'/student_card/\' . $id ) }}" target="_blank" class="btn btn-success btn-sm" >
                                            <i class="fa fa-credit-card"></i>  {{ trans("student.student_card") }}</a>-->
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student.delete\', Sentinel::getUser()->permissions)))
                                     <!--<a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->
                                      @endif')

            ->removeColumn('user_id')
             ->rawColumns([ 'actions' ])->make();
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
        $students = $this->studentRepository->getAllActiveFilter($request)->get();

        return view('student.allFilteredList', ['students' => $students], ['count' => '1']);//
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





    private function generateParams()
    {
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
            ->prepend(trans('student.select_school_year'), 0)
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
            ->prepend(trans('student.select_semester'), 0)
            ->toArray();


        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'student_document_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            });

        $schoolType = session('current_company_type');

        $school = School::find(session('current_company'));


        view()->share('schoolType', $schoolType);
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
        view()->share('document_types', $document_types);
    }

    public function ajaxNote(StudentNoteRequest $request)
    {
        $studentNote = new StudentNote();
        $studentNote->user_id = Sentinel::getUser()->id;
        $studentNote->note = $request->note;
        $studentNote->student_id = $request->student_id;
        $studentNote->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $student = Student::find($request->student_id);
        return view('student.notes', compact('title', 'action', 'count', 'student', 'thisUser'));
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



    public function ajaxStudentApprove(Request $request)
    {
        session(['student_id' => $request->student_id]);
        try
        {
            DB::transaction(function() use ($request) {
                $student = Student::where('id', $request->student_id)->first();
                $student->status = 'active';
                $student->save();

                //Send email to the student approved
                $when = now()->addMinutes(3);
                Mail::to($student->user->email)
                    ->later($when, new StudentApproveMail($student));

if(!(MoodleUser::find($student->user->id))){
                //Create Moodel Account
                $moodleUser = new MoodleUser();
                $moodleUser->id = $student->user->id;
                $moodleUser->auth = 'manual';
                $moodleUser->confirmed = 1;
                $moodleUser->mnethostid = 1;
                $moodleUser->username = $student->user->email;
                $moodleUser->password = $student->user->password;
                $moodleUser->firstname = $student->user->first_name;
                $moodleUser->middlename = $student->user->middle_name;
                $moodleUser->lastname = $student->user->last_name;
                $moodleUser->email = $student->user->email;
                $moodleUser->mobile = $student->user->mobile;
                $moodleUser->address = $student->user->address;
                $moodleUser->save();
}
            });

            } catch (\Exception $e) {
            return $e;

        }
        session(['student_id' => '']);
        return 'Student Approved';

    }


    public function ajaxMakeActive(Request $request)
    {
        StudentStatus::firstOrCreate(['company_id' => session('current_company'), 'school_year_id' => session('current_company_year'), 'semester_id' => session('current_company_semester'), 'student_id' => $request->student_id]);


        return 'Operation Successful';

    }

    public function ajaxMakeInActive(Request $request)
    {
        StudentStatus::where('student_id', $request->student_id)->delete();

        return 'Operation Successful';

    }


    public function ajaxAcceptAdmission(Request $request)
    {
        $studentAdmission = StudentAdmission::where('student_id', $request->student_id)->first();
        $studentAdmission->status = 1;
        $studentAdmission->save();

        return 'Operation Successful';

    }




    /*public function generalExport(Request $request)
    {
        $students = $this->studentRepository->getAllActiveExport($request);

        return Excel::download(new StudentsExport($students), 'students.xlsx');
    }*/



    public function generalExport(Request $request)
    {
        $students = $this->studentRepository->getAllActiveFilter($request);
        return Excel::download(new StudentsExport($students), 'students.xlsx');

    }

    public function admittedExport(Request $request)
    {
        $students = $this->studentRepository->getAllActiveExport($request);

        return Excel::download(new StudentsExport($students), 'students.xlsx');
    }

    /*public function testRoleUsers()
    {
        $role = Sentinel::findRoleById(2);
// or findRoleBySlug('admin'); for example
        $users = $role->users()->get();
        foreach ($users as $user)
        {
            if ($user->hasAccess('student.approveinfo'))
            {
                $when = now()->addMinutes(3);
                Mail::to($user->email)
                    ->later($when, new AdmissionApproverEmail($user));
            }
        }
        dd($users);
    }*/





}
