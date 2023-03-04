<?php

namespace App\Http\Controllers\Secure;

use App\Models\AdmissionNonWaecExam;
use App\Models\AdmissionWaecExamSubject;
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
use App\Repositories\ActivityLogRepository;
use App\Http\Requests\Secure\ApplicantSchoolRequest;
use Yajra\DataTables\Facades\DataTables;
use Sentinel;

class ApplicantSchoolController extends SecureController
{
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
    protected $activity;
    protected $module = 'Applicant School';


    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(
        ApplicantSchoolRepository $applicantSchoolRepository,
        QualificationRepository $qualificationRepository,
        WaecExamRepository $waecExamRepository,
        WaecSubjectRepository $waecSubjectRepository,
        WaecSubjectGradeRepository $waecSubjectGradeRepository,
        ActivityLogRepository $activity
    ) {

        parent::__construct();

        $this->applicantSchoolRepository = $applicantSchoolRepository;
        $this->qualificationRepository = $qualificationRepository;
        $this->waecExamRepository = $waecExamRepository;
        $this->waecSubjectRepository = $waecSubjectRepository;
        $this->waecSubjectGradeRepository = $waecSubjectGradeRepository;
        $this->activity = $activity;

        view()->share('type', 'applicant_school');

        $columns = ['title', 'start_date', 'end_date', 'qualification', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $applicant = Applicant::where('user_id', '=', Sentinel::getUser()->id)->get()->first();

        $waecExams = $this->waecExamRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $waecSubjects = $this->waecSubjectRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $waecSubjectGrades = $this->waecSubjectGradeRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $qualifications = $this->qualificationRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_qualification'), '')
            ->toArray();

        $title = trans('applicant.academic_info');
        return view('applicant_school.index', compact('title', 'applicant', 'waecExams', 'waecSubjects', 'qualifications', 'waecSubjectGrades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('applicant.new_school');

        $applicant = Applicant::where('user_id', '=', Sentinel::getUser()->id)->get()->first();


        $qualifications = $this->qualificationRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_qualification'), 0)
            ->toArray();
        return view('layouts.create', compact('title', 'qualifications', 'applicant'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|ApplicantSchoolRequest $request
     * @return Response
     */
    public function store(ApplicantSchoolRequest $request)
    {

        $applicant = Applicant::where('user_id', '=', Sentinel::getUser()->id)->first();

        $applicant_school = new Applicant_school($request->all());
        $applicant_school->user_id = Sentinel::getUser()->id;
        $applicant_school->applicant_id = $applicant->id;
        $applicant_school->save();

        return redirect('/applicant_school');
    }

    /**
     * Display the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function show(Applicant_school $applicant_school)
    {
        $title = trans('behavior.details');
        $action = 'show';
        return view('layouts.show', compact('applicant_school', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(Applicant_school $applicant_school)
    {
        $title = trans('behavior.edit');
        $qualifications = $this->qualificationRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_qualification'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'applicant_school', 'qualifications'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|BehaviorAddEditRequest $request
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function update(ApplicantSchoolRequest $request, Applicant_school $applicant_school)
    {
        $applicant_school->update($request->all());
        return redirect('/applicant_school');
    }

    public function delete(Applicant_school $applicant_school)
    {
        $title = trans('behavior.delete');
        return view('/applicant_school/delete', compact('applicant_school', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function destroy(Applicant_school $applicant_school)
    {
        $applicant_school->delete();
        return redirect('/applicant_school');
    }

    public function data()
    {
        $schools = $this->applicantSchoolRepository->getAllForApplicant(Sentinel::getUser()->id)
            ->get()
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'title' => $school->title,
                    'start_date' => $school->start_date,
                    'end_date' => $school->end_date,
                    'qualification' => @$school->qualification->title,
                ];
            });

        return Datatables::make($schools)
            ->addColumn('actions', '<a href="{{ url(\'/applicant_school/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/applicant_school/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/applicant_school/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }


    public function ajaxAddSchool(ApplicantSchoolRequest $request)
    {

        $applicant = Applicant::where('user_id', '=', Sentinel::getUser()->id)->get()->first();

        $applicant_school = new Applicant_school($request->all());
        $applicant_school->user_id = Sentinel::getUser()->id;
        $applicant_school->applicant_id = $applicant->id;
        $applicant_school->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $applicant = Applicant::find($request->applicant_id);
        return view('applicant_school.schools', compact('title', 'action', 'count', 'applicant', 'thisUser'));
    }


    public function DeleteApplicantSchool(Request $request)
    {
        $item = Applicant_school::where('id',$request->item_id)->first();
        $item->delete();

        $this->activity->record([
            'module'    => 'Applicant School',
            'module_id' => $item->id,
            'activity'  => 'Deleted'
        ]);
        return $item->title.' '.'Deleted Successfully';
    }


    public function ajaxAddWaec(ApplicantWaecRequest $request)
    {
        $admissionWaecExam = new AdmissionWaecExam();
        $admissionWaecExam->index_number = $request->index_number;
        $admissionWaecExam->waec_exam_id = $request->waec_exam_id;
        $admissionWaecExam->applicant_id = $request->applicant_id;
        $admissionWaecExam->waec_year = $request->waec_year;
        $admissionWaecExam->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $exam = AdmissionWaecExam::find($request->waec_exam_id);
        $applicant = Applicant::find($request->applicant_id);
        $waecSubjects = $this->waecSubjectRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $waecSubjectGrades = $this->waecSubjectGradeRepository
            ->getAllForSchool(session('current_company'))
            ->get();
        return view('applicant_school.waec_exams', compact('title', 'action', 'count', 'applicant', 'thisUser', 'exam', 'waecSubjects', 'waecSubjectGrades'));
    }

    public function DeleteApplicantWaecExam(Request $request)
    {
        $item = AdmissionWaecExam::findOrFail($request->item_id);
        $item->delete();

        /*$this->activity->record([
            'module'    => 'Applicant Waec Exam',
            'module_id' => $item->id,
            'activity'  => 'Deleted'
        ]);*/
        return 'Deleted Successfully '.$item->exams->title;
    }


    public function ajaxAddWaecSubject(ApplicantWaecExamSubject $request)
    {
        AdmissionWaecExamSubject::firstOrCreate([
            'company_id' => session('current_company'),
            'admission_waec_exam_id' => $request->subject_exam_id,
            'applicant_id' => $request->applicant_id,
            'waec_subject_id' => $request->waec_subject_id,
            'waec_subject_grade_id' => $request->grade_id]);


        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $exam = AdmissionWaecExam::find($request->subject_exam_id);
        $applicant = Applicant::find($request->applicant_id);
        return view('applicant_school.subjects', compact('title', 'action', 'count', 'applicant', 'thisUser', 'exam'));
    }

    public function DeleteApplicantWaecSubject(Request $request)
    {
        $item = AdmissionWaecExamSubject::where('id',$request->item_id)->first();
        $item->delete();

        $this->activity->record([
            'module'    => 'Applicant Waec Subject',
            'module_id' => $item->id,
            'activity'  => 'Deleted'
        ]);
        return 'Deleted Successfully';
    }



    public function ajaxAddNonWaec(ApplicantNonWaecRequest $request)
    {
        $admissionNonWaecExam = new AdmissionNonWaecExam();
        $admissionNonWaecExam->index_number = $request->index_number;
        $admissionNonWaecExam->title = $request->title;
        $admissionNonWaecExam->program = $request->program;
        $admissionNonWaecExam->grade = $request->grade;
        $admissionNonWaecExam->applicant_id = $request->applicant_id;
        $admissionNonWaecExam->year = $request->year;
        $admissionNonWaecExam->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser= Sentinel::getUser()->id;
        $applicant = Applicant::find($request->applicant_id);
        return view('applicant_school.non_waec_exams', compact('title', 'action', 'count', 'applicant', 'thisUser'));
    }

    public function DeleteApplicantNonWaecExam(Request $request)
    {
        $item2 = AdmissionNonWaecExam::where('id',$request->item_id)->first();
        $item2->delete();

        $this->activity->record([
            'module'    => 'Applicant Non Waec Exam',
            'module_id' => $item2->id,
            'activity'  => 'Deleted'
        ]);
        return $item2->title. ' '.'Deleted';
    }
}
