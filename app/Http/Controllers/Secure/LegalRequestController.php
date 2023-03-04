<?php
namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\DailyActivityRequest;
use App\Http\Requests\Secure\LegalRequestCommentRequest;
use App\Http\Requests\Secure\LegalRequestRequest;
use App\Http\Requests\Secure\StaffLeaveCommentRequest;
use App\Http\Requests\Secure\VisitorLogRequest;
use App\Models\DailyActivity;
use App\Models\Employee;

use App\Models\EmployeeKpiActivity;
use App\Models\Kpi;
use App\Models\LegalRequest;
use App\Models\LegalRequestCategory;
use App\Models\LegalRequestComment;
use App\Models\StaffLeave;
use App\Models\StaffLeaveComment;
use App\Models\UserDocument;
use App\Models\VisitorLog;
use App\Notifications\DeleteKpiNotification;
use App\Notifications\LeaveCommentNotification;
use App\Notifications\LegalRequestCommentNotification;
use App\Repositories\ActivityLogRepository;
use App\Repositories\VisitorLogRepository;
use App\Repositories\SectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Sentinel;
use App\Notifications\SendSMS;
use Illuminate\Support\Facades\DB;
use PDF;

class LegalRequestController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var VisitorLogRepository
     */
    private $legalRequestRepository;

    protected $module = 'legalRequest';
    protected $activity;

    /**
     * EmployeeController constructor.
     * @param VisitorLogRepository $legalRequestRepository
     */
    public function __construct(
        VisitorLogRepository $legalRequestRepository,
        ActivityLogRepository $activity,
        SectionRepository $sectionRepository
    ) {

        parent::__construct();
        $this->visitorLogRepository = $legalRequestRepository;
        $this->sectionRepository = $sectionRepository;
        $this->activity = $activity;


        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'legalRequest');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Legal Service Requests';

        $legalRequests = LegalRequest::where('employee_id', session('current_employee'))
            ->get();

       return view('legalRequest.index', compact('title', 'legalRequests'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function filter(Request $request)
    {
        $date = Carbon::create($request->ddate);
        $title = 'Legal Service Requests';

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function($query) use($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        /*if($request->department_id > 0) {
            $legalRequests = $this->visitorLogRepository->getAllForSchoolDepartmentDay(session('current_company') ,$request->department_id, $date )
                ->get();
        }

        elseif($request->employee_id == 'all') {
            $legalRequests = $this->visitorLogRepository->getAllForSchoolDay(session('current_company'),$date )
                ->get();
        }
        else {
            $legalRequests = $this->visitorLogRepository->getForEmployee($request->employee_id, $date )
                ->get();
        }*/

        $legalRequests = LegalRequest::all();

       return view('legalRequest.load', compact('title', 'legalRequests', 'request', 'employees'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexAll()
    {
        $title = 'Legal Service Requests';
        $legalRequests = LegalRequest::get();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('All Employees', 'all')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();



       return view('legalRequest.all', compact('title', 'legalRequests', 'employees', 'sections'));
    }







    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Legal Service Request';
        $legalRequestCategories = LegalRequestCategory::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Category', '')
            ->toArray();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Employee', '')
            ->toArray();


        return view('layouts.create', compact('title', 'employees', 'legalRequestCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorLogRequest $request
     * @return Response
     */


    public function store(LegalRequestRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                $legalRequest = new LegalRequest();
                $legalRequest->company_year_id = session('current_company_year');
                $legalRequest->employee_id = session('current_employee');
                $legalRequest->legal_request_category_id = $request->legal_request_category_id;
                $legalRequest->title = $request->title;
                $legalRequest->score_of_work = $request->score_of_work;
                $legalRequest->duration_of_service = $request->duration_of_service;
                $legalRequest->consideration = $request->consideration;
                $legalRequest->parties = $request->parties;
                $legalRequest->dead_line = $request->dead_line;
                $legalRequest->other_info = $request->other_info;
                $legalRequest->save();

                    /*Send a thank you email to the visitor*/
                    /*@GeneralHelper::sendNewEmployee_email($user,$employee);*/



            });
    }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }



        return response('<div class="alert alert-success">Request Created Successfully</div>') ;


    }

    /**
     * Display the specified resource.
     *
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function show(LegalRequest $legalRequest)
    {
        $title = $legalRequest->title;
        $action = 'show';

        return view('layouts.show', compact('legalRequest', 'title', 'action'));
    }





    /**
     * Show the form for editing the specified resource.
     *
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function edit(LegalRequest $legalRequest)
    {
        $title = 'Edit '. $legalRequest->title.'';
        $legalRequestCategories = LegalRequestCategory::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Category', '')
            ->toArray();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'legalRequest', 'employees', 'legalRequestCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VisitorLogRequest $request
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function update(LegalRequestRequest $request, LegalRequest $legalRequest)
    {

        try
        {

            $legalRequest->legal_request_category_id = $request->legal_request_category_id;
            $legalRequest->title = $request->title;
            $legalRequest->score_of_work = $request->score_of_work;
            $legalRequest->duration_of_service = $request->duration_of_service;
            $legalRequest->consideration = $request->consideration;
            $legalRequest->parties = $request->parties;
            $legalRequest->dead_line = $request->dead_line;
            $legalRequest->other_info = $request->other_info;
            $legalRequest->save();



        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $legalRequest->id,
            'activity'  => 'updated'
        ]);
       /* Flash::success("Employee Information Updated Successfully");*/

        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }


            return response('<div class="alert alert-success">Request Updated Successfully</div>');

    }

    /**
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function delete(LegalRequest $legalRequest)
    {
        try
        {

                DB::transaction(function() use ($legalRequest) {
                    $legalRequest->Comments()->delete();
                    $legalRequest->delete();

                    $this->activity->record([
                        'module'    => $this->module,
                        'module_id' => $legalRequest->id,
                        'activity'  => 'Deleted'
                    ]);
                });

        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">REQUEST DELETED SUCCESSFULLY</div>') ;
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function destroy(VisitorLog $legalRequest)
    {

        $legalRequest->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $legalRequest->id,
            'activity'  => 'Deleted'
        ]);
        Flash::success("Deleted successfully");
        return 'Deleted';
    }




    public function latestLegalRequestComments(LegalRequest $legalRequest)
    {
        return view('legalRequest.comments', compact('legalRequest'));
    }






    public function addComment(LegalRequestCommentRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                if (!empty($request->newsComment))
                {
                    $comment = new LegalRequestComment();
                    $comment->legal_request_id = $request->legal_request_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->newsComment;
                    $comment->save();

                    $legalRequest = LegalRequest::find($request->legal_request_id);
                    $legalRequest->status = $request->status;
                    $legalRequest->save();

                    if (GeneralHelper::validateEmail($legalRequest->employee->user->email))
                    {
                        @Notification::send($legalRequest->employee->user, new LegalRequestCommentNotification($legalRequest->employee->user, $legalRequest, $comment));
                    }
                }

            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        $legalRequest = LegalRequest::find($request->legal_request_id);
        return view('legalRequest.comments', compact('legalRequest'));

    }




}
