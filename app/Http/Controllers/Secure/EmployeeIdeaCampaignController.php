<?php
namespace App\Http\Controllers\Secure;

use App\Events\LeaveApproveEvent;
use App\Helpers\CompanySettings;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\EmployeeIdeaCampaignRequest;
use App\Http\Requests\Secure\EmployeeRequestCommentRequest;
use App\Http\Requests\Secure\EmployeeRequestRequest;
use App\Http\Requests\Secure\VisitorLogRequest;
use App\Models\DailyActivity;
use App\Models\Employee;
use App\Models\EmployeeIdeaCampaign;
use App\Models\EmployeeIdeaCampaignDocument;
use App\Models\EmployeeIdeaDocument;
use App\Models\EmployeeRequest;
use App\Models\EmployeeRequestApprover;
use App\Models\EmployeeRequestCategory;
use App\Models\EmployeeRequestComment;
use App\Models\EmployeeRequestCopy;
use App\Models\EmployeeRequestDocument;
use App\Models\LegalRequest;
use App\Models\StaffLeave;
use App\Models\VisitorLog;
use App\Notifications\EmployeeRequestApprovalNotification;
use App\Notifications\EmployeeRequestApprovedCopyNotification;
use App\Notifications\EmployeeRequestApprovedNotification;
use App\Notifications\EmployeeRequestApproverCommentNotification;
use App\Notifications\EmployeeRequestCopyCommentNotification;
use App\Notifications\EmployeeRequestCopyNotification;
use App\Notifications\EmployeeRequestOwnerCommentNotification;
use App\Notifications\EmployeeRequestSelfApprovedNotification;
use App\Notifications\EmployeeRequestSelfNotification;
use App\Repositories\ActivityLogRepository;
use App\Repositories\VisitorLogRepository;
use App\Repositories\SectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Sentinel;
use App\Notifications\SendSMS;
use Illuminate\Support\Facades\DB;
use PDF;

class EmployeeIdeaCampaignController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var VisitorLogRepository
     */
    private $legalRequestRepository;

    protected $module = 'employeeIdeaCampaign';
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

        view()->share('type', 'employeeIdeaCampaign');
        view()->share('link', 'employeeIdeaCampaign');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Employee Idea Campaigns';

        $employeeIdeaCampaigns = EmployeeIdeaCampaign::withCount(['employeeIdeas'])->get();

       return view('employeeIdeaCampaign.index', compact('title', 'employeeIdeaCampaigns'));
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

       return view('employeeIdea.load', compact('title', 'legalRequests', 'request', 'employees'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexAll()
    {
        $title = 'All Employee Requests';
        $legalRequests = EmployeeRequest::get();

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



       return view('employeeIdea.all', compact('title', 'legalRequests', 'employees', 'sections'));
    }


    public function requestApprovals()
    {
        $title = 'Request Approvals';
        $staffLeaves = EmployeeRequest::where('company_year_id', session('current_company_year'))->whereHas('approvers', function ($q) {
            $q->where('employee_request_approvers.employee_id', session('current_employee'));
        })->orderBy('employee_requests.id', 'DESC')->get();
        $action = 'approval';

        return view('employeeRequest.approvals', compact('title', 'staffLeaves', 'action'));
    }


    public function requestCopy()
    {
        $title = 'Request Copies';
        $employeeRequests = EmployeeRequest::where('company_year_id', session('current_company_year'))->whereHas('copies', function ($q) {
            $q->where('employee_request_copies.employee_id', session('current_employee'));
        })->orderBy('employee_requests.id', 'DESC')->get();
        $action = 'copy';

        return view('employeeRequest.copy', compact('title', 'employeeRequests', 'action'));
    }

    public function allCompanyEmployeeRequests()
    {
        $title = trans('staff_leave.staff_leaves');
        $staffLeaves = StaffLeave::where('company_year_id', session('current_company_year'))->whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->orderBy('applied_date', 'DESC')->get();
        $action = 'view';

        return view('staff_leave.all_leaves', compact('title', 'staffLeaves', 'action'));
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Employee Idea Campaign';


        return view('employeeIdeaCampaign.modal_form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorLogRequest $request
     * @return Response
     */


    public function store(EmployeeIdeaCampaignRequest $request)
    {
        $validated = $request->validated();
        /*if ($request->kpi_activity_status_id == 3 AND $request->comment == '<p><br></p>')
        {
            return response()->json(['exception'=>'Kindly provide activity report']);
        }*/
        try
        {
            $employeeIdea = EmployeeIdeaCampaign::firstOrCreate(
                [

                    'title' => $request->title,
                    'company_id' => session('current_company'),
                    'company_year_id' => session('current_company_year'),
                    'created_employee_id' => session('current_employee'),
                    'description' => $request->description,
                ],
            );




            //STORE ADDITIONAL DOCUMENTS
            foreach ($validated['kt_docs_repeater_basic'] as $upload)
            {
                if (!empty($upload['document_title']) AND !empty($upload['file']))
                {
                    $file = $upload['file'];
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) . '.' . $extension;
                    $destinationPath = public_path().'/uploads/documents/ideas';
                    $file->move($destinationPath, $document);
                    $employeeRequestDocument = new EmployeeIdeaCampaignDocument();
                    $employeeRequestDocument->employee_idea_campaign_id = $employeeIdea->id;
                    $employeeRequestDocument->document_title = $upload['document_title'];
                    $employeeRequestDocument->file = $document;
                    $employeeRequestDocument->save();

                }
            }

            //STORE REQUEST APPROVERS
            /*foreach ($validated['approvals_employee_id'] as $approver)
            {

                $employeeRequestApprover = new EmployeeRequestApprover();
                $employeeRequestApprover->employee_request_id = $employee_request->id;
                $employeeRequestApprover->employee_id = $approver;
                $employeeRequestApprover->save();

            }*/


            //STORE REQUEST COPIES
            /*foreach ($validated['copy_employee_id'] as $copy)
            {

                $employeeRequestCopy = new EmployeeRequestCopy();
                $employeeRequestCopy->employee_request_id = $employee_request->id;
                $employeeRequestCopy->employee_id = $copy;
                $employeeRequestCopy->save();

            }*/


            //send email to approvers
            /*foreach ($employee_request->approvers as $approver) {
                $when = now()->addMinutes(1);
                if (GeneralHelper::validateEmail($approver->employee->user->email)) {
                    @Notification::send($approver->employee->user, new EmployeeRequestApprovalNotification($approver->employee->user, $employee_request));
                }
            }*/

            //send email to copies
           /* foreach ($employee_request->copies as $copy) {
                $when = now()->addMinutes(1);
                if (GeneralHelper::validateEmail($copy->employee->user->email)) {
                    @Notification::send($copy->employee->user, new EmployeeRequestCopyNotification($copy->employee->user, $employee_request));
                }
            }*/



            //send email to self
            /*if ($this->school->hr_head)
            {*/
                /*if (GeneralHelper::validateEmail($employee_request->employee->user->email)) {
                    @Notification::send($employee_request->employee->user, new EmployeeRequestSelfNotification($employee_request->employee->user, $employee_request));
                }*/
            /*}*/


        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }


        return response('Request Created Successfully') ;
    }

    /**
     * Display the specified resource.
     *
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function show(EmployeeIdeaCampaign $employeeIdeaCampaign)
    {
        $title = $employeeIdeaCampaign->title;
        $action = 'show';

        return view('employeeIdeaCampaign._details', compact('employeeIdeaCampaign', 'title', 'action'));
    }



    public function modalShowApprove(EmployeeRequest $employeeRequest)
    {
        $title = $employeeRequest->title;
        $action = 'approval';

        return view('employeeIdea.modalShowApprove', compact('employeeRequest', 'title', 'action'));
    }



    public function approve(EmployeeRequest $employeeRequest)
    {
        try {
            $employeeRequestApprover = EmployeeRequestApprover::where('employee_request_id', $employeeRequest->id )->where('employee_id', session('current_employee'))->first();
            $employeeRequestApprover->status = true;
            $employeeRequestApprover->approve_date = now();
            $employeeRequestApprover->save();


            //send email to approvers
            foreach ($employeeRequest->approvers as $approver) {
                if (GeneralHelper::validateEmail($approver->employee->user->email)) {
                    @Notification::send($approver->employee->user, new EmployeeRequestApprovedNotification($approver->employee->user, $employeeRequest, $employeeRequestApprover));
                }
            }

            //send email to copies
            foreach ($employeeRequest->copies as $copy) {
                $when = now()->addMinutes(1);
                if (GeneralHelper::validateEmail($copy->employee->user->email)) {
                    @Notification::send($copy->employee->user, new EmployeeRequestApprovedCopyNotification($copy->employee->user, $employeeRequest, $employeeRequestApprover));
                }
            }



            //send email to self

            if (GeneralHelper::validateEmail($employeeRequest->employee->user->email)) {
                @Notification::send($employeeRequest->employee->user, new EmployeeRequestSelfApprovedNotification($employeeRequest->employee->user, $employeeRequest, $employeeRequestApprover));
            }



        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Request Approved Successfully</div>');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param VisitorLog $legalRequest
     * @return Response
     */





    public function edit(EmployeeIdeaCampaign $employeeIdeaCampaign)
    {
        $title = 'Edit '. $employeeIdeaCampaign->title.'';



        return view('employeeIdeaCampaign.modal_form', compact('title', 'employeeIdeaCampaign'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VisitorLogRequest $request
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function update(EmployeeIdeaCampaignRequest $request, EmployeeIdeaCampaign $employeeIdeaCampaign)
    {

        $validated = $request->validated();
        /*if ($request->kpi_activity_status_id == 3 AND $request->comment == '<p><br></p>')
        {
            return response()->json(['exception'=>'Kindly provide activity report']);
        }*/
        try
        {
            $employeeIdeaCampaign->title = $request->title;
            $employeeIdeaCampaign->description = $request->description;
            $employeeIdeaCampaign->status = 0;
            $employeeIdeaCampaign->save();



            //STORE ADDITIONAL DOCUMENTS
            foreach ($validated['kt_docs_repeater_basic'] as $upload)
            {
                if (!empty($upload['document_title']) AND !empty($upload['file']))
                {
                    $file = $upload['file'];
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) . '.' . $extension;
                    $destinationPath = public_path().'/uploads/documents/ideas';
                    $file->move($destinationPath, $document);
                    $employeeRequestDocument = new EmployeeIdeaCampaignDocument();
                    $employeeRequestDocument->employee_request_id = $employeeIdeaCampaign->id;
                    $employeeRequestDocument->document_title = $upload['document_title'];
                    $employeeRequestDocument->file = $document;
                    $employeeRequestDocument->save();

                }
            }

            //STORE REQUEST APPROVERS
            foreach ($validated['approvals_employee_id'] as $approver)
            {

                EmployeeRequestApprover::firstOrCreate(
                    [
                        'employee_request_id' => $employeeRequest->id,
                        'employee_id' => $approver,
                    ]
                );


            }


            //STORE REQUEST COPIES
            foreach ($validated['copy_employee_id'] as $copy)
            {

                EmployeeRequestCopy::firstOrCreate(
                    [
                        'employee_request_id' => $employeeRequest->id,
                        'employee_id' => $copy,
                    ]
            );

            }


            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $employeeRequest->id,
                'activity'  => 'updated'
            ]);


        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }


            return response('Request Updated Successfully');

    }

    /**
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function delete(EmployeeIdeaCampaign $employeeIdeaCampaign)
    {
        try
        {

                DB::transaction(function() use ($employeeIdeaCampaign) {
                    $employeeIdeaCampaign->delete();

                    $this->activity->record([
                        'module'    => $this->module,
                        'module_id' => $employeeIdeaCampaign->id,
                        'activity'  => 'Deleted'
                    ]);
                });

        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('REQUEST DELETED SUCCESSFULLY') ;
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param VisitorLog $legalRequest
     * @return Response
     */
    public function destroy(EmployeeIdeaCampaign $employeeIdeaCampaign)
    {

        $employeeIdeaCampaign->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $employeeIdeaCampaign->id,
            'activity'  => 'Deleted'
        ]);
        Flash::success("Deleted successfully");
        return 'Deleted';
    }




    public function requestComments(EmployeeRequest $employeeRequest)
    {
        return view('employeeIdea.comments', compact('employeeRequest'));
    }

    public function addComment(EmployeeRequestCommentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (! empty($request->newsComment)) {
                    $comment = new EmployeeRequestComment();
                    $comment->employee_request_id = $request->employee_request_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->newsComment;
                    $comment->save();

                    $employeeRequest = EmployeeRequest::find($request->employee_request_id);



                    //send email to approvers
                    foreach ($employeeRequest->approvers as $approver) {
                        if (GeneralHelper::validateEmail($approver->employee->user->email)) {
                            @Notification::send($approver->employee->user, new EmployeeRequestApproverCommentNotification($approver->employee->user, $employeeRequest, $comment));
                        }
                    }

                    //send email to copies
                    foreach ($employeeRequest->copies as $copy) {
                        $when = now()->addMinutes(1);
                        if (GeneralHelper::validateEmail($copy->employee->user->email)) {
                            @Notification::send($copy->employee->user, new EmployeeRequestCopyCommentNotification($copy->employee->user, $employeeRequest, $comment));
                        }
                    }



                    //send email to owner

                    if (GeneralHelper::validateEmail($employeeRequest->employee->user->email)) {
                        @Notification::send($employeeRequest->employee->user, new EmployeeRequestOwnerCommentNotification($employeeRequest->employee->user, $employeeRequest, $comment));
                    }

                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $employeeRequest = EmployeeRequest::find($request->employee_request_id);

        return view('employeeIdea.comments', compact('employeeRequest'));
    }





}
