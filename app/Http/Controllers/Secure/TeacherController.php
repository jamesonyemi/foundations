<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\TeacherImportRequest;
use App\Models\Enrol;
use App\Models\MoodleUser;
use App\Models\TeacherSchool;
use App\Models\User;
use App\Models\UserDocument;
use App\Models\TeacherSubject;
use App\Models\UserEnrollment;
use App\Repositories\SubjectRepository;
use App\Repositories\OptionRepository;
use App\Repositories\TeacherSchoolRepository;
use App\Repositories\TeacherSubjectRepository;
use App\Helpers\Thumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use App\Http\Requests\Secure\TeacherRequest;

class TeacherController extends SecureController
{
    /**
     * @var TeacherSchoolRepository
     */
    private $teacherSchoolRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * TeacherController constructor.
     * @param TeacherSchoolRepository $teacherSchoolRepository
     * @param OptionRepository $optionRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        TeacherSchoolRepository $teacherSchoolRepository,
        OptionRepository $optionRepository,
        SubjectRepository $subjectRepository,
        TeacherSubjectRepository $teacherSubjectRepository
    ) {

        parent::__construct();

        $this->teacherSchoolRepository = $teacherSchoolRepository;
        $this->optionRepository = $optionRepository;
        $this->subjectRepository = $subjectRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;

        $this->middleware('authorized:teacher.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:teacher.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:teacher.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:teacher.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'teacher');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('teacher.teacher');
        $teachers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'));
        return view('teacher.index', compact('title', 'teachers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('teacher.new');

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

        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'staff_document_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            });
        $custom_fields =  CustomFormUserFields::getCustomUserFields('teacher');
        return view('layouts.create', compact('title', 'document_types', 'custom_fields', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TeacherRequest $request
     * @return Response
     */
    public function store(TeacherRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
        $user = $this->teacherSchoolRepository->create($request->except('document', 'document_id', 'image_file'));

        /*$user = User::find($user->id);*/

        $moodleUser = new MoodleUser();
        $moodleUser->id = $user->id;
        $moodleUser->auth = 'manual';
        $moodleUser->confirmed = 1;
        $moodleUser->mnethostid = 1;
        $moodleUser->username = $user->email;
        $moodleUser->password = $user->password;
        $moodleUser->firstname = $user->first_name;
        $moodleUser->middlename = $user->middle_name;
        $moodleUser->lastname = $user->last_name;
        $moodleUser->email = $user->email;
        $moodleUser->mobile = $user->mobile;
        $moodleUser->address = $user->address;
        $moodleUser->save();

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

            UserDocument::firstOrCreate(['user_id' => $user->id, 'document' => $document, 'option_id' => $request->document_id]);
        }


        /*$this->teacherSubjectRepository->getAllForSubjectAndGroup($subject->id, $studentGroup->id, session('current_company_semester'))
            ->delete();*/

        if (!empty($request['subject_id'])) {
            foreach ($request['subject_id'] as $subject_id) {
                $teacherSubject = new TeacherSubject;
                $teacherSubject->subject_id = $subject_id;
                $teacherSubject->school_year_id = session('current_company_year');
                $teacherSubject->company_id = session('current_company');
                $teacherSubject->semester_id = session('current_company_semester');
                $teacherSubject->student_group_id = 1;
                $teacherSubject->teacher_id = $user->id;
                $teacherSubject->save();
/*

                $userEnrollment = new UserEnrollment();
                $userEnrollment->userid = $user->id;
                $userEnrollment->enrolid = Enrol::where('courseid', $subject_id)->first();
                $userEnrollment->modifierid = Sentinel::getUser()->id;
                $userEnrollment->status = 0;
                $userEnrollment->save();*/
            }
        }


            });

        } catch (\Exception $e) {
            /*return $e;*/
        }

        /*CustomFormUserFields::storeCustomUserField('teacher', $user->id, $request);*/
        return redirect('/teacher');
    }

    /**
     * Display the specified resource.
     *
     * @param User $teacher
     * @return Response
     */
    public function show(User $teacher)
    {
        $title = trans('teacher.details');
        $action = 'show';
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('teacher', $teacher->id);
        /*$teacher_subjects = $this->teacherSubjectRepository->getAllForSchoolYearAndTeacher(session('current_company_year'), session('current_company'), session('current_company_semester'), $teacher->id)*/
        $teacher_subjects = $teacher->moodleCourses;
           /* ->get();*/
        return view('layouts.show', compact('teacher', 'title', 'action', 'custom_fields', 'teacher_subjects'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $teacher
     * @return Response
     */
    public function edit(User $teacher)
    {
        $title = trans('teacher.edit');


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

        /*$teacher_subject = $this->teacherSubjectRepository->getAllForSubjectAndGroup($teacher->id,  session('current_company_semester'))*/
        $teacher_subject = $teacher->moodleCourses;



        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'staff_document_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            });
        $documents = UserDocument::where('user_id', $teacher->id)->first();
        $custom_fields =  CustomFormUserFields::fetchCustomValues('teacher', $teacher->id);

        $teacher_subjects = $this->teacherSubjectRepository->getAllForSchoolYearAndTeacher(session('current_company_year'), session('current_company'), session('current_company_semester'), $teacher->id)
            ->get()
            ->pluck('subject_id', 'subject_id')
            ->toArray();

        return view('layouts.edit', compact('title', 'teacher', 'document_types', 'documents', 'custom_fields', 'subjects', 'teacher_subject', 'teacher_subjects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TeacherRequest $request
     * @param User $teacher
     * @return Response
     */
    public function update(TeacherRequest $request, User $teacher)
    {
        try
        {
            DB::transaction(function() use ($request, $teacher) {
        if ($request->password != "") {
            $teacher->password = bcrypt($request->password);
        }
        $teacher->update($request->except('password', 'document', 'document_id', 'image_file'));

        if($teacher->moodleUser)
        {
            $teacher->moodleUser->password = bcrypt($request->password);
            $teacher->moodleUser->save();
        }

        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $teacher->picture = $picture;
            $teacher->save();
        }
        if ($request->hasFile('document') != "") {
            $file = $request->file('document');
            $extension = $file->getClientOriginalExtension();
            $document = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/documents/';
            $file->move($destinationPath, $document);

            UserDocument::where('user_id', $teacher->id)->delete();

            UserDocument::firstOrCreate(['user_id' => $teacher->id, 'document' => $document, 'option_id' => $request->document_id]);
        }
        CustomFormUserFields::updateCustomUserField('teacher', $teacher->id, $request);


        /*TeacherSubject::where('teacher_id', $teacher->id)
            ->where('semester_id', session('current_company_semester'))
            ->delete();*/

        /*if (!empty($request['subject_id'])) {

            foreach ($request['subject_id'] as $subject_id) {
                $teacherSubject = new TeacherSubject;
                $teacherSubject->subject_id = $subject_id;
                $teacherSubject->school_year_id = session('current_company_year');
                $teacherSubject->company_id = session('current_company');
                $teacherSubject->semester_id = session('current_company_semester');
                $teacherSubject->student_group_id = 1;
                $teacherSubject->teacher_id = $teacher->id;
                $teacherSubject->save();
            }
        }*/

            });

        } catch (\Exception $e) {
            /*return $e;*/
        }


        return redirect('/teacher');
    }

    /**
     * @param User $teacher
     * @return Response
     */
    public function delete(User $teacher)
    {
        $title = trans('teacher.delete');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('teacher', $teacher->id);
        return view('/teacher/delete', compact('teacher', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $teacher
     * @return Response
     */
    public function destroy(User $teacher)
    {
        TeacherSchool::where('user_id', $teacher->id)
            ->where('company_id', session('current_company'))->delete();
        /*alert()->success('SuccessAlert','Lecturer Deleted Successfully!');*/

        return redirect('/teacher');
    }



    public function data()
    {
        $teachers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
            ->map(function ($teacher) {
                return [
                    'id'        => $teacher->id,
                    'full_name' => $teacher->full_name,
                ];
            });
        return Datatables::make($teachers)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'teacher.edit\', Sentinel::getUser()->permissions)))
                                 <a href="{{ url(\'/teacher/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/join_date/\' . $id ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-calendar"></i>  {{ trans("teacher.join_date") }}</a>
                                   @endif
                                   @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'staff_salary.show\', Sentinel::getUser()->permissions)))
                                   <a href="{{ url(\'/staff_salary/\' . $id ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-money"></i>  {{ trans("teacher.set_salary") }}</a>
                                   @endif
                                   <a href="{{ url(\'/teacher/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                   	@if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'teacher.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/teacher/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }


    function getExists(Request $request)
    {
        $teacher = User::where('email', $request->email)->first();
        return response()->json(['teacher' => $teacher]);
    }

    public function getImport()
    {
        $title = trans('teacher.import_teachers');

        return view('teacher.import', compact('title'));
    }




}
