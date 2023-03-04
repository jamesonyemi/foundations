<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\StaffLeavePlanRequest;
use App\Http\Requests\Secure\StaffLeaveRequest;
use App\Models\StaffLeave;
use App\Models\StaffLeavePlan;
use App\Models\UserDocument;
use App\Repositories\StaffLeaveTypeRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use Sentinel;

class StaffLeavePlanController extends SecureController
{
    /**
     * @var StaffLeaveTypeRepository
     */
    private $staffLeaveTypeRepository;

    /**
     * StaffLeaveController constructor.
     *
     * @param StaffLeaveTypeRepository $staffLeaveTypeRepository
     */
    public function __construct(StaffLeaveTypeRepository $staffLeaveTypeRepository)
    {
        parent::__construct();

        view()->share('type', 'staff_leave_plan');

        $this->staffLeaveTypeRepository = $staffLeaveTypeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('staff_leave.staff_leave_plan');
        $staffLeaves = StaffLeavePlan::where('employee_id', session('current_employee'))->get();

        return view('staff_leave_plan.index', compact('title', 'staffLeaves'));
    }

    public function approvals()
    {
        $title = trans('staff_leave.staff_leaves');
        $staffLeaves = StaffLeave::get();

        return view('staff_leave.approvals', compact('title', 'staffLeaves'));
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

        return view('staff_leave_plan.modalForm', compact('title', 'staff_leave_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StaffLeaveRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StaffLeavePlanRequest $request)
    {

        /*CHECK FOR NUMBER OF LEAVE DAYS AVAILABLE TO STAFF*/
        if ($request->days > $this->currentEmployee->leaveLeft) {
            /*return response('<div class="alert alert-warning">Your Total Leave Days ('.$this->currentEmployee->leaveLeft.') is less than the '.$request->days.' You Are Requesting</div>') ;*/

            return response()->json(['error'=>'Your Total Leave Days ('.$this->currentEmployee->leaveLeft.') is less than the '.$request->days.' You Are Requesting']);
        }
        try {
            $staffLeavePlan = new StaffLeavePlan();
            $staffLeavePlan->title = $request->title;
            $staffLeavePlan->start_date = $request->start_date;
            $staffLeavePlan->end_date = $request->end_date;
            $staffLeavePlan->staff_leave_type_id = $request->staff_leave_type_id;
            $staffLeavePlan->days = $request->days;
            $staffLeavePlan->description = $request->description;
            $staffLeavePlan->company_year_id = session('current_company_year');
            $staffLeavePlan->employee_id = session('current_employee');
            $staffLeavePlan->applied_date = now();
            $staffLeavePlan->save();

            if ($request->hasFile('document') != '') {
                $file = $request->file('document');
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);

                $staffLeavePlan->document = $document;
                $staffLeavePlan->save();
            }
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Leave Plan Submitted For Approval</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param StaffLeavePlan $staffLeavePlan
     * @return Response
     */
    public function show(StaffLeavePlan $staffLeavePlan)
    {
        $title = trans('staff_leave.details');
        $action = 'show';

        return view('layouts.show', compact('staffLeavePlan', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param StaffLeavePlan $staffLeavePlan
     * @return Response
     */
    public function edit(StaffLeavePlan $staffLeavePlan)
    {
        $title = trans('staff_leave.edit');
        $staff_leave_types = $this->staffLeaveTypeRepository->getAll()->pluck('title', 'id');

        return view('layouts.edit', compact('title', 'staffLeavePlan', 'staff_leave_types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StaffLeaveplanRequest $request
     * @param StaffLeavePlan $staffLeavePlan
     * @return Response
     */
    public function update(StaffLeaveplanRequest $request, StaffLeavePlan $staffLeavePlan)
    {
        /*CHECK FOR NUMBER OF LEAVE DAYS AVAILABLE TO STAFF*/
        if ($request->days > $this->currentEmployee->leaveLeft) {
            return response()->json(['error'=>'Your Total Leave Days ('.$this->currentEmployee->leaveLeft.') is less than the '.$request->days.' You Are Requesting']);
        }
        try {
            $staffLeavePlan->title = $request->title;
            $staffLeavePlan->start_date = $request->start_date;
            $staffLeavePlan->end_date = $request->end_date;
            $staffLeavePlan->staff_leave_type_id = $request->staff_leave_type_id;
            $staffLeavePlan->days = $request->days;
            $staffLeavePlan->description = $request->description;
            $staffLeavePlan->company_year_id = session('current_company_year');
            $staffLeavePlan->employee_id = session('current_employee');
            $staffLeavePlan->applied_date = now();
            $staffLeavePlan->save();

            if ($request->hasFile('document') != '') {
                $file = $request->file('document');
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);

                $staffLeavePlan->document = $document;
                $staffLeavePlan->save();
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Application Updated Successfully</div>');
    }

    /**
     * @param StaffLeave $staffLeave
     * @return Response
     */
    public function delete(StaffLeavePlan $staffLeavePlan)
    {
        try {
            $staffLeavePlan->delete();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Leave Plan Deleted Successfully</div>');
    }

    /**
     * Remove the specified resource from storage.
     * @param StaffLeavePlan $staffLeavePlan
     * @return Response
     */
    public function destroy(StaffLeavePlan $staffLeavePlan)
    {
        $staffLeavePlan->delete();

        return redirect('/staff_leave_plan');
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
        $staffLeave->approved = true;
        $staffLeave->save();

        return redirect()->back();
    }

    public function noApproveLeave(StaffLeave $staffLeave)
    {
        $staffLeave->approved = false;
        $staffLeave->save();

        return redirect()->back();
    }
}
