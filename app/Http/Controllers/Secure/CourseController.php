<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\CourseRequest;
use App\Http\Requests\Secure\CourseUpdateRequest;
use App\Http\Requests\Secure\KpiCommentRequest;
use App\Http\Requests\Secure\LegalCaseRequest;
use App\Http\Requests\Secure\LegalCaseUpdateRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\Company;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseComment;
use App\Models\Kpi;
use App\Models\KpiComment;
use App\Models\LegalCase;
use App\Models\LegalCaseCategory;
use App\Models\LegalCaseComment;
use App\Models\LegalFirm;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Notifications\CourseUpdateNotification;
use App\Notifications\KpiCommentResponsibleEmployeesEmail;
use App\Notifications\KpiCommentSupervisorEmail;
use App\Notifications\LegalCaseUpdateNotification;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\EmployeeRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Validator;
use Illuminate\Http\Request;

class CourseController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * DirectionController constructor.
     *
     * @param EmployeeRepository $employeeRepository
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {
        /*$this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);*/
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'course');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Courses';
        $courses = Course::with(['courseCategory'])->get();
        return view('course.index', compact('title', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Course';
        $courseCategories = CourseCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();


        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Stake Holder', 0)
            ->toArray();


        return view('layouts.create', compact('title', 'courseCategories', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(CourseRequest $request)
    {


        try
        {
            DB::transaction(function() use ($request) {

                $course = Course::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'company_id' => session('current_company'),
                                    'title' => $request['title'],
                                    'course_category_id' => $request['course_category_id'],
                                    'location' => $request['location'],
                                    'start_date' => $request['start_date'],
                                    'end_date' => $request['end_date'],
                                    'description' => $request['description'],
                                    'institution' => $request['institution'],
                                    'status' => $request['status'],
                                ]
                            );

                /*$course->companies()->attach($request->input('company_id'));*/
                $course->stakeHolders()->attach($request->input('employee_id'));

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Course Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param LegalCase $legalCase
     * @return Response
     */
    public function show(Course $course)
    {
        $title = 'Course';

        $action = 'show';

        return view('layouts.show', compact('course', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param Course $course
     * @return Response
     */
    public function edit(Course $course)
    {
        $title = 'Edit Course';

        $courseCategories = CourseCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();



        $companies = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();



        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Stake Holder', 0)
            ->toArray();

        $legal_case_stakeholders = $course->stakeHolderIds()
            ->pluck('employee_id')
            ->toArray();


        return view('layouts.edit', compact('title', 'course', 'courseCategories', 'companies', 'employees', 'legal_case_stakeholders'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LegalCase $legalCase
     * @param LegalCase $legalCase
     * @return Response
     */
    public function update(CourseRequest $request, Course $course)
    {
        try
        {
            DB::transaction(function() use ($course, $request) {
                $course->update($request->except('company_id', 'employee_id'));
                /*$course->companies()->sync($request->company_id);*/
                $course->stakeHolders()->sync($request->employee_id);

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Course Updated Successfully') ;
    }

    public function delete(Course $course)
    {
        if ($course->comments->count() > 0)
            return response()->json(['exception'=>'Course has Comment associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($course) {
                $course->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Course Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy($course)
    {
        $course->delete();
        return 'Legal Case Deleted';


    }




    public function findCourseCategory(Request $request)
    {
        $categories = CourseCategory::where('procurement_master_category_id', $request->procurement_master_category_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();
        return $categories;
    }



    public function latestCourseUpdates(Course $course)
    {
        return view('course.updates', compact('course'));
    }




    public function addComment(CourseUpdateRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                if (!empty($request->caseUpdate))
                {
                    $comment = new CourseComment();
                    $comment->course_id = $request->legal_case_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->caseUpdate;
                    $comment->save();

                }

            });

            //send email to stakeholders
            $course = Course::find($request->course_id);
            foreach ($course->stakeHolders as $employee)
            {
                if (GeneralHelper::validateEmail($employee->user->email))
                {
                    @Notification::send($employee->user, new CourseUpdateNotification($employee->user, $course));
                }
            }


            //Send email to responsible employees
            /*foreach (Kpi::find($request->kpi_id)->kpiResponsibleEmployees as $kpiResponsibleEmployee)
            {
                if (GeneralHelper::validateEmail($kpiResponsibleEmployee->user->email))
                {
                    $when = now()->addMinutes(1);
                    Mail::to($kpiResponsibleEmployee->user->email)
                        ->later($when, new KpiCommentResponsibleEmployeesEmail($kpiResponsibleEmployee->user));
                }
            }*/

        }


        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        $course = Course::find($request->course_id);
        return view('course.updates', compact('course'));

    }

}
