<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\HelpDeskRequest;
use App\Models\Employee;
use App\Models\HelpDesk;
use App\Models\HelpDeskCategory;
use App\Models\HelpDeskPriority;
use App\Models\HelpDeskSubCategory;
use App\Models\ProcurementCategorySupplier;
use App\Models\SupplierDocument;
use App\Models\UserDocument;
use App\Notifications\HelpDeskAttentionNotification;
use App\Notifications\HelpDeskUserCreateNotification;
use App\Notifications\KpiSelfReviewSupervisorNotification;
use App\Notifications\SendSMS;
use App\Repositories\EmployeeRepository;
use App\Repositories\HelpDeskRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use PDF;
use Sentinel;

class HelpDeskController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var HelpdeskRepository
     */
    private $helpDeskRepository;

    protected $module = 'HelpDesk';

    /**
     * EmployeeController constructor.
     * @param HelpDeskRepository $helpDeskRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        HelpdeskRepository $helpDeskRepository
    ) {
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->helpDeskRepository = $helpDeskRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'helpDesk');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('helpDesk.tickets');
        $helpDesks = $this->helpDeskRepository->getAllMe(session('current_employee'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function me()
    {
        $title = trans('helpDesk.me');
        $helpDesks = $this->helpDeskRepository->getAllMe(session('current_employee'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function mine()
    {
        $title = trans('helpDesk.mine');
        $helpDesks = $this->helpDeskRepository->getAllMine(session('current_employee'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function closed()
    {
        $title = trans('helpDesk.closed');
        $helpDesks = $this->helpDeskRepository->getAllClosed(session('current_company'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function open()
    {
        $title = trans('helpDesk.open');
        $helpDesks = $this->helpDeskRepository->getAllOpen(session('current_company'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('helpDesk.new');

        /*$helpDeskCategories = HelpDeskCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();

        $helpDeskPriorities = HelpDeskPriority::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_priority'), 0)
            ->toArray();*/

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'employees'));
    }

    public function createFor()
    {
        $title = trans('helpDesk.new');
        $createFor = 1;

        $helpDeskCategories = HelpDeskCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();

        $helpDeskPriorities = HelpDeskPriority::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_priority'), 0)
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
            ->toArray();

        return view('layouts.create', compact('title', 'helpDeskCategories', 'helpDeskPriorities', 'employees', 'createFor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param HelpDeskRequest $request
     * @return Response
     */
    public function store(HelpDeskRequest $request)
    {
        $number = HelpDesk::latest()->first()->help_desk_number ?? 0;
        $employee = $this->currentEmployee;

        try {
            DB::transaction(function () use ($request, $number, $employee) {
                $helpDesk = new HelpDesk();
                $helpDesk->title = $request->title;
                $helpDesk->description = $request->description;
                $helpDesk->help_desk_number = $number + 1;
                $helpDesk->employee_id = session('current_employee');
                $helpDesk->company_year_id = session('current_company_year');
                $helpDesk->save();

                if ($request->hasFile('file') != '') {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/documents/';
                    $file->move($destinationPath, $document);
                    $helpDesk->file = $document;
                    $helpDesk->save();
                }

                $helpDesk->employeeAttentions()->attach($request->input('attention_employee_id'));

                //send email to user
                $when = now()->addMinutes(1);
                /*if (GeneralHelper::validateEmail($employee->user->email))
                {
                    @Notification::send($employee->user, new HelpDeskUserCreateNotification($helpDesk, $helpDesk->employeeAttentions, $employee->user));

                }*/

                //send email to attention employees
                foreach ($helpDesk->employeeAttentions as $attentionEmployee) {
                    $when = now()->addMinutes(1);
                    if (GeneralHelper::validateEmail($attentionEmployee->user->email)) {
                        @Notification::send($attentionEmployee->user, new HelpDeskAttentionNotification($helpDesk, $employee, $attentionEmployee->user));
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employee Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function show(HelpDesk $helpDesk)
    {
        $title = $helpDesk->title;
        $action = 'show';

        return view('layouts.show', compact('helpDesk', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function edit(HelpDesk $helpDesk)
    {
        $title = 'Edit '.$helpDesk->title.'';

        $helpDeskCategories = HelpDeskCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();

        $helpDeskPriorities = HelpDeskPriority::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_priority'), 0)
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
            ->toArray();

        $employeeAttentions = $helpDesk->employeeAttentionIds()
            ->pluck('employee_id')
            ->toArray();

        return view('helpDesk.modalForm', compact('title', 'helpDesk', 'helpDeskPriorities', 'helpDeskCategories', 'employees', 'employeeAttentions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param HelpDeskRequest $request
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function update(HelpDeskRequest $request, HelpDesk $helpDesk)
    {
        try {
            $helpDesk->update($request->all());

            $helpDesk->employeeAttentions()->sync($request->input('attention_employee_id'));

            /*$this->activity->record([
                'module'    => $this->module,
                'module_id' => $helpDesk->id,
                'activity'  => 'updated'
            ]);*/

        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Operation Successful!!!');
    }

    /**
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function delete(HelpDesk $helpDesk)
    {
        if (! Sentinel::hasAccess('employees_delete')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        $title = 'Delete '.$employee->user->full_name.'';

        return view('employee.delete', compact('employee', 'title', 'helpDesk'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function destroy(HelpDesk $helpDesk)
    {
        if (! Sentinel::hasAccess('employees_delete')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        $helpDesk->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $helpDesk->id,
            'activity'  => 'Deleted',
        ]);
        Flash::success('Employee Deleted successfully');

        return 'Employee Deleted';
    }

    public function findHelpdeskSubCategory(Request $request)
    {
        $directions = HelpDeskSubCategory::where('help_desk_category_id', $request->section_id)->get()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_sub_category'), '')
            ->toArray();

        return $directions;
    }
}
