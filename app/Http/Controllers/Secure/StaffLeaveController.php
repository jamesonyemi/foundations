<?php

namespace App\Http\Controllers\Secure;

use App\Events\LeaveApplicationEvent;
use App\Events\LeaveApproveEvent;
use App\Events\LoginEvent;
use App\Helpers\GeneralHelper;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KpiCommentRequest;
use App\Http\Requests\Secure\StaffLeaveCommentRequest;
use App\Http\Requests\Secure\StaffLeaveRecordRequest;
use App\Http\Requests\Secure\StaffLeaveRequest;
use App\Models\Employee;
use App\Models\EmployeeIdeaDocument;
use App\Models\EmployeeKpiTimeline;
use App\Models\Holiday;
use App\Models\Kpi;
use App\Models\KpiResponsibility;
use App\Models\StaffLeave;
use App\Models\StaffLeaveComment;
use App\Models\StaffLeaveDocument;
use App\Models\StaffLeavePlan;
use App\Models\UserDocument;
use App\Notifications\CascadeKpiNotification;
use App\Notifications\LeaveApproveRelieverNotification;
use App\Notifications\LeaveCommentNotification;
use App\Notifications\LeaveCommentSupervisorNotification;
use App\Notifications\LeaveHRApproveNotification;
use App\Notifications\LeaveHRNotification;
use App\Notifications\LeaveRecordRelieverNotification;
use App\Notifications\LeaveRecordSelfNotification;
use App\Notifications\LeaveRecordSupervisorNotification;
use App\Notifications\LeaveRelieverNotification;
use App\Notifications\LeaveSupervisorNotification;
use App\Notifications\SendBscSignOffEmail;
use App\Repositories\EmployeeRepository;
use App\Repositories\StaffLeaveTypeRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
use Sentinel;

class StaffLeaveController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var StaffLeaveTypeRepository
     */
    private $staffLeaveTypeRepository;

    /**
     * StaffLeaveController constructor.
     *
     * @param StaffLeaveTypeRepository $staffLeaveTypeRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        StaffLeaveTypeRepository $staffLeaveTypeRepository
    ) {
        parent::__construct();

        view()->share('type', 'staff_leave');
        view()->share('link', 'leave_management');

        $this->employeeRepository = $employeeRepository;
        $this->staffLeaveTypeRepository = $staffLeaveTypeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('staff_leave.staff_leaves');
        $staffLeaves = StaffLeave::where('employee_id', session('current_employee'))->where('company_year_id', session('current_company_year'))->get();

        return view('staff_leave.index', compact('title', 'staffLeaves'));
    }

    public function approvals()
    {
        $title = 'Direct Reports Leaves';
        $staffLeaves = StaffLeave::where('company_year_id', session('current_company_year'))->whereHas('employee.supervisors2', function ($q) {
            $q->where('employee_supervisors.employee_supervisor_id', session('current_employee'));
        })->orderBy('applied_date', 'DESC')->get();
        $action = 'approval';

        return view('staff_leave.approvals', compact('title', 'staffLeaves', 'action'));
    }

    public function allCompanyLeaves()
    {
        $title = trans('staff_leave.staff_leaves');
        $staffLeaves = StaffLeave::where('company_year_id', session('current_company_year'))->whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->orderBy('applied_date', 'DESC')->get();
        $action = 'view';

        return view('staff_leave.all_leaves', compact('title', 'staffLeaves', 'action'));
    }


    public function staffLeaveDays()
    {
        $title = trans('staff_leave.staff_leaves');
        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))->where('status', 1)
            ->with('user', 'section')
            ->get();
        $total_outstanding_leave_days = $this->school->total_outstanding_leave_days;
        $total_leave_days = $this->school->total_leave_days;
        $total_leave_applications_days = $this->school->leave_applications;
        $total_leave_days_left = $total_leave_days+$total_outstanding_leave_days-$total_leave_applications_days;

        return view('staff_leave.staff_leave_days', compact('title', 'employees', 'total_leave_days', 'total_leave_applications_days', 'total_outstanding_leave_days', 'total_leave_days_left'));
    }

    public function staff_leaves_applications(Employee $employee)
    {
        $title = $employee->user->full_name. ' Leave Application Details';
        $leaveEmployee = $employee;
        $staffLeaves = StaffLeave::where('employee_id', $employee->id)->where('company_year_id', session('current_company_year'))->where('approved', 1)->get();


        return view('staff_leave.staff_leave_applications', compact('title', 'staffLeaves', 'leaveEmployee'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('staff_leave.new');
        $staff_leave_types = $this->staffLeaveTypeRepository->getAll()->pluck('title', 'id');
        $staffLeavePlans = StaffLeavePlan::where('employee_id', session('current_employee'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Leave Plan', 0)
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Reliever', '')
            ->toArray();

        return view('staff_leave._form', compact('title', 'staff_leave_types', 'staffLeavePlans', 'employees'));
    }

    public function createRecord()
    {
        $title = 'New Staff Leave Record';
        $staff_leave_types = $this->staffLeaveTypeRepository->getAll()->pluck('title', 'id');

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Employee', '')
            ->toArray();

        return view('staff_leave.create_record', compact('title', 'staff_leave_types', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StaffLeaveRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StaffLeaveRequest $request)
    {
        $validated = $request->validated();
        /*CHECK FOR NUMBER OF LEAVE DAYS AVAILABLE TO STAFF*/
        if ($request->days > $this->currentEmployee->leaveLeft) {
            /*return response('<div class="alert alert-warning">Your Total Leave Days ('.$this->currentEmployee->leaveLeft.') is less than the '.$request->days.' You Are Requesting</div>') ;*/

            return response()->json(['error'=>'Your Total Leave Days ('.$this->currentEmployee->leaveLeft.') is less than the '.$request->days.' You Are Requesting']);
        }
        try {
            $staffLeave = new StaffLeave();
            $staffLeave->start_date = $request->start_date;
            $staffLeave->end_date = $request->end_date;
            $staffLeave->return_date = $request->return_date;
            $staffLeave->staff_leave_type_id = $request->staff_leave_type_id;
            $staffLeave->days = $request->days;
            $staffLeave->description = $request->description;
            $staffLeave->company_year_id = session('current_company_year');
            $staffLeave->employee_id = session('current_employee');
            $staffLeave->reliever_employee_id = $request->reliever_employee_id;
            $staffLeave->applied_date = now();
            $staffLeave->hand_over_notes = $request->hand_over_notes;
            $staffLeave->save();

            //STORE ADDITIONAL DOCUMENTS
            foreach ($validated['kt_docs_repeater_basic'] as $upload)
            {
                if (!empty($upload['document_title']) AND !empty($upload['file']))
                {
                    $file = $upload['file'];
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) . '.' . $extension;
                    $destinationPath = public_path().'/uploads/documents/leave';
                    $file->move($destinationPath, $document);
                    $employeeRequestDocument = new StaffLeaveDocument();
                    $employeeRequestDocument->staff_leave_id = $staffLeave->id;
                    $employeeRequestDocument->document_title = $upload['document_title'];
                    $employeeRequestDocument->file = $document;
                    $employeeRequestDocument->save();

                }
            }

            //send email to supervisors
            foreach ($this->currentEmployee->supervisors as $supervisor) {
                $when = now()->addMinutes(1);
                if (GeneralHelper::validateEmail($supervisor->employee->user->email)) {
                    @Notification::send($supervisor->employee->user, new LeaveSupervisorNotification($supervisor->employee->user, $staffLeave));
                }
            }

            //send email to reliever
                if (GeneralHelper::validateEmail($staffLeave->reliever_employee->user->email)) {
                    @Notification::send($staffLeave->reliever_employee->user, new LeaveRelieverNotification($staffLeave->reliever_employee->user, $staffLeave));
                }


            //send email to HR
            if ($this->school->hr_head)
            {
                if (GeneralHelper::validateEmail($this->school->hr_head->user->email)) {
                    @Notification::send($this->school->hr_head->user, new LeaveHRNotification($this->school->hr_head->user, $staffLeave));
                }
            }

            /*event(new LeaveApplicationEvent($staffLeave->employee->user));*/
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Application Submitted For Approval</div>');
    }



    public function storeRecord(StaffLeaveRecordRequest $request)
    {
        $validated = $request->validated();

        try {
            $staffLeave = new StaffLeave();
            $staffLeave->start_date = $request->start_date;
            $staffLeave->end_date = $request->end_date;
            $staffLeave->return_date = $request->return_date;
            $staffLeave->staff_leave_type_id = $request->staff_leave_type_id;
            $staffLeave->days = $request->days;
            $staffLeave->description = $request->description;
            $staffLeave->company_year_id = session('current_company_year');
            $staffLeave->employee_id = $request->employee_id;
            $staffLeave->reliever_employee_id = $request->reliever_employee_id;
            $staffLeave->applied_date = now();
            $staffLeave->hand_over_notes = $request->hand_over_notes;
            //if sick leave
            if ($request->staff_leave_type_id == 3 OR $request->staff_leave_type_id == 2 OR $request->staff_leave_type_id == 6 OR $request->staff_leave_type_id == 7)
            {
                $staffLeave->approved = 1;
            }

            $staffLeave->save();

            //STORE ADDITIONAL DOCUMENTS
            foreach ($validated['kt_docs_repeater_basic'] as $upload)
            {
                if (!empty($upload['document_title']) AND !empty($upload['file']))
                {
                    $file = $upload['file'];
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) . '.' . $extension;
                    $destinationPath = public_path().'/uploads/documents/leave';
                    $file->move($destinationPath, $document);
                    $employeeRequestDocument = new StaffLeaveDocument();
                    $employeeRequestDocument->staff_leave_id = $staffLeave->id;
                    $employeeRequestDocument->document_title = $upload['document_title'];
                    $employeeRequestDocument->file = $document;
                    $employeeRequestDocument->save();

                }
            }

            //send email to supervisors
            foreach ($this->currentEmployee->supervisors as $supervisor) {
                $when = now()->addMinutes(1);
                if (GeneralHelper::validateEmail($supervisor->employee->user->email)) {
                    @Notification::send($supervisor->employee->user, new LeaveRecordSupervisorNotification($supervisor->employee->user, $staffLeave));
                }
            }

            //send email to reliever
            if (GeneralHelper::validateEmail($staffLeave->reliever_employee->user->email)) {
                @Notification::send($staffLeave->reliever_employee->user, new LeaveRecordRelieverNotification($staffLeave->reliever_employee->user, $staffLeave));
            }


            //send email to employee
            if (GeneralHelper::validateEmail(@$staffLeave->employee->user->email)) {
                @Notification::send(@$staffLeave->employee->user, new LeaveRecordSelfNotification($staffLeave->employee->user, $staffLeave));
            }


            //send email to HR
            if ($this->school->hr_head)
            {
                if (GeneralHelper::validateEmail($this->school->hr_head->user->email)) {
                    @Notification::send($this->school->hr_head->user, new LeaveHRNotification($this->school->hr_head->user, $staffLeave));
                }
            }

        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Record Added Successfully</div>');
    }


    /**
     * Display the specified resource.
     *
     * @param StaffLeave $staffLeave
     * @return Response
     */
    public function show(StaffLeave $staffLeave)
    {
        $title = $staffLeave->employee->user->full_name.' leave Details';
        $action = 'show';

        return view('layouts.show', compact('staffLeave', 'title', 'action'));
    }

    public function showApprove(StaffLeave $staffLeave)
    {
        $title = $staffLeave->employee->user->full_name.' leave Details';
        $action = 'approval';

        return view('layouts.show', compact('staffLeave', 'title', 'action'));
    }

    public function modalShowApprove(StaffLeave $staffLeave)
    {
        $title = $staffLeave->employee->user->full_name.' leave Details';
        $action = 'approval';

        return view('staff_leave.modalShowApprove', compact('staffLeave', 'title', 'action'));
    }

    public function approve(StaffLeave $staffLeave)
    {
        $title = $staffLeave->employee->user->full_name.' leave Details';
        $action = 'approval';

        return view('layouts.show', compact('staffLeave', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param StaffLeave $staffLeave
     * @return Response
     */
    public function edit(StaffLeave $staffLeave)
    {
        $title = trans('staff_leave.edit');
        $staff_leave_types = $this->staffLeaveTypeRepository->getAll()->pluck('title', 'id');
        $staffLeavePlans = StaffLeavePlan::where('employee_id', session('current_employee'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Leave Plan', 0)
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Reliever', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'staffLeave', 'staff_leave_types', 'staffLeavePlans', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StaffLeaveRequest $request
     * @param StaffLeave $staffLeave
     * @return Response
     */
    public function update(StaffLeaveRequest $request, StaffLeave $staffLeave)
    {
        $validated = $request->validated();

        /*CHECK FOR NUMBER OF LEAVE DAYS AVAILABLE TO STAFF*/
        if ($request->days > $this->currentEmployee->leaveLeft) {
            return response()->json(['error'=>'Your Total Leave Days ('.$this->currentEmployee->leaveLeft.') is less than the '.$request->days.' You Are Requesting']);
        }
        try {
            $staffLeave->start_date = $request->start_date;
            $staffLeave->end_date = $request->end_date;
            $staffLeave->return_date = $request->return_date;
            $staffLeave->staff_leave_type_id = $request->staff_leave_type_id;
            $staffLeave->days = $request->days;
            $staffLeave->description = $request->description;
            $staffLeave->company_year_id = session('current_company_year');
            $staffLeave->employee_id = session('current_employee');
            $staffLeave->reliever_employee_id = $request->reliever_employee_id;
            $staffLeave->applied_date = now();
            $staffLeave->hand_over_notes = $request->hand_over_notes;
            $staffLeave->save();


            //STORE ADDITIONAL DOCUMENTS
            foreach ($validated['kt_docs_repeater_basic'] as $upload)
            {
                if (!empty($upload['document_title']) AND !empty($upload['file']))
                {
                    $file = $upload['file'];
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) . '.' . $extension;
                    $destinationPath = public_path().'/uploads/documents/leave';
                    $file->move($destinationPath, $document);
                    $employeeRequestDocument = new StaffLeaveDocument();
                    $employeeRequestDocument->staff_leave_id = $staffLeave->id;
                    $employeeRequestDocument->document_title = $upload['document_title'];
                    $employeeRequestDocument->file = $document;
                    $employeeRequestDocument->save();

                }
            }


        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Application Updated Successfully</div>');
    }

    public function calculateDays(Request $request)
    {
        $start = new \DateTime($request->start_date);
        $end = new \DateTime($request->end_date);
        // otherwise the  end date is excluded (bug?)
        $end->modify('+1 day');

        $interval = $end->diff($start);

        // total days
        $days = $interval->days;

        // create an iterateable period of date (P1D equates to 1 day)
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);

        // best stored as array, so you can add more than one
        /*$holidays = array('2022-02-01', '2022-02-02', '2022-02-03');*/

        $holidays = Holiday::pluck('date')->toArray();

        /*dd($holidays);*/

        /*$holidays = Holiday::get('date');*/

        foreach ($period as $dt) {
            $curr = $dt->format('D');

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            }

            // (optional) for the updated question
            elseif (in_array($dt->format('Y-m-d'), $holidays)) {
                $days--;
            }
        }

        return response($days);
    }

    /**
     * @param StaffLeave $staffLeave
     * @return Response
     */
    public function delete(StaffLeave $staffLeave)
    {
        try {
            $staffLeave->delete();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Application Deleted Successfully</div>');
    }

    /**
     * Remove the specified resource from storage.
     * @param StaffLeave $staffLeave
     * @return Response
     */
    public function destroy(StaffLeave $staffLeave)
    {
        $staffLeave->delete();

        return redirect('/staff_leave');
    }

    public function data()
    {
        $staffLeave = StaffLeave::join('users', 'users.id', '=', 'staff_leaves.user_id')
                            ->join('staff_leave_types', 'staff_leave_types.id', '=', 'staff_leaves.staff_leave_type_id')
                            ->orderBy('staff_leaves.date', 'desc')
                            ->where('staff_leaves.company_year_id', session('current_company_year'))
                            ->where('staff_leaves.company_id', session('current_company'))
                            ->select(['staff_leaves.id', 'staff_leaves.date', 'staff_leaves.description',
                                'staff_leave_types.title', 'staff_leaves.approved',
                                DB::raw('CONCAT(users.first_name, " ", users.last_name) as full_name'), ]);
        if (Sentinel::inRole('teacher') || Sentinel::inRole('librarian')) {
            $staffLeave = $staffLeave->where('user_id', Sentinel::getUser()->id);
        }
        $staffLeave = $staffLeave->get();

        return Datatables::make($staffLeave)
            ->editColumn('approved', function ($staffLeave) {
                if (is_null($staffLeave->approved)) {
                    return trans('staff_leave.new_request');
                } elseif ($staffLeave->approved == 1) {
                    return trans('staff_leave.approved');
                } else {
                    return trans('staff_leave.no_approved');
                }
            })
            ->addColumn('actions', function ($staffLeave) {
                if (Sentinel::inRole('teacher') || Sentinel::inRole('librarian')) {
                    if (Carbon::createFromFormat(Settings::get('date_format'), $staffLeave->date) > Carbon::now()) {
                        return '<a href="'.url('staff_leave/'.$staffLeave->id.'/edit').'" class="btn btn-success btn-sm" >
                                        <i class="fa fa-pencil-square-o "></i>  '.trans('table.edit').'</a>
                                <a href="'.url('staff_leave/'.$staffLeave->id.'/delete').'" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i> '.trans('table.delete').'</a>';
                    } else {
                        return '';
                    }
                } else {
                    return '<a href="'.url('staff_leave/'.$staffLeave->id.'/edit').'" class="btn btn-success btn-sm" >
                           		<i class="fa fa-pencil-square-o "></i>  '.trans('table.edit').'</a>
                            <a href="'.url('staff_leave/'.$staffLeave->id.'/show').'" class="btn btn-primary btn-sm" >
                            	<i class="fa fa-eye"></i>  '.trans('table.details').'</a>
                            <a href="'.url('staff_leave/'.$staffLeave->id.'/approve').'" class="btn btn-success btn-sm" >
                                <i class="fa fa-check-circle "></i>  '.trans('staff_leave.approve').'</a>
                            <a href="'.url('staff_leave/'.$staffLeave->id.'/no_approve').'" class="btn btn-danger btn-sm" >
                            	<i class="fa fa-circle "></i>  '.trans('staff_leave.no_approve').'</a>
                            <a href="'.url('staff_leave/'.$staffLeave->id.'/delete').'" class="btn btn-danger btn-sm">
                               	<i class="fa fa-trash"></i> '.trans('table.delete').'</a>';
                }
            })
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function approveLeave(StaffLeave $staffLeave)
    {
        try {
            $staffLeave->approved = true;
            $staffLeave->save();

            event(new LeaveApproveEvent($staffLeave->employee->user));




            //send email to reliever
            if (GeneralHelper::validateEmail($staffLeave->reliever_employee->user->email)) {
                @Notification::send($staffLeave->reliever_employee->user, new LeaveApproveRelieverNotification($staffLeave->reliever_employee->user, $staffLeave));
            }


            //send email to HR
            if ($this->school->hr_head)
            {
                if (GeneralHelper::validateEmail($this->school->hr_head->user->email)) {
                    @Notification::send($this->school->hr_head->user, new LeaveHRApproveNotification($this->school->hr_head->user, $staffLeave));
                }
            }


        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Application Approved Successfully</div>');
    }

    public function recallLeave(StaffLeave $staffLeave)
    {
        try {
            $staffLeave->approved = false;
            $staffLeave->save();

            /*event(new LeaveApproveEvent($staffLeave->employee->user));*/
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Recalled Successfully</div>');
    }

    public function noApproveLeave(StaffLeave $staffLeave)
    {
        $staffLeave->approved = false;
        $staffLeave->save();

        return redirect()->back();
    }

    public function findLeavePlan(StaffLeavePlan $staffLeavePlan)
    {
        return [
            'start_date' => $staffLeavePlan->start_date,
            'end_date' => $staffLeavePlan->end_date,
            'days' => $staffLeavePlan->days,
            'description' => $staffLeavePlan->description,
            'staff_leave_type_id' => $staffLeavePlan->staff_leave_type_id,
        ];
    }

    public function latestLeaveComments(StaffLeave $staff_leave)
    {
        return view('staff_leave.comments', compact('staff_leave'));
    }

    public function addComment(StaffLeaveCommentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (! empty($request->newsComment)) {
                    $comment = new StaffLeaveComment();
                    $comment->staff_leave_id = $request->staff_leave_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->newsComment;
                    $comment->save();

                    $leave = StaffLeave::find($request->staff_leave_id);

                    //send email to supervisors
                    foreach ($leave->employee->supervisors as $supervisor) {
                        if (GeneralHelper::validateEmail($supervisor->employee->user->email)) {
                            @Notification::send($supervisor->employee->user, new LeaveCommentSupervisorNotification($supervisor->employee->user, $leave, $comment));
                        }
                    }

                    if (GeneralHelper::validateEmail($leave->employee->user->email)) {
                        @Notification::send($leave->employee->user, new LeaveCommentNotification($leave->employee->user, $leave, $comment));
                    }
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $staff_leave = StaffLeave::find($request->staff_leave_id);

        return view('staff_leave.comments', compact('staff_leave'));
    }




    public function employeeOutstandingLeavedays()
    {
        set_time_limit(0);
        try
        {

            Employee::chunk(100, function ($employees){
                foreach ($employees as $employee)
                {
                    if ($employee->outstanding_leave_days > 5)
                    {
                        if ($employee->leaveapplications->sum('days') > 4)
                        {
                            $employee->outstanding_leave_days = 5;
                            $employee->save();
                        }


                        else
                        {
                            $employee->outstanding_leave_days = 0;
                            $employee->save();
                        }
                    }

                }
            });
        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">  LEAVE DAYS SYNCHED SUCCESSFULLY</div>') ;


    }
}
