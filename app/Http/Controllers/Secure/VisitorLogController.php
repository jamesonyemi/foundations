<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\DailyActivityRequest;
use App\Http\Requests\Secure\VisitorLogRequest;
use App\Models\DailyActivity;
use App\Models\Employee;
use App\Models\EmployeeKpiActivity;
use App\Models\UserDocument;
use App\Models\VisitorLog;
use App\Notifications\SendSMS;
use App\Repositories\SectionRepository;
use App\Repositories\VisitorLogRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Sentinel;

class VisitorLogController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var VisitorLogRepository
     */
    private $visitorLogRepository;

    protected $module = 'visitorLog';

    /**
     * EmployeeController constructor.
     * @param VisitorLogRepository $visitorLogRepository
     */
    public function __construct(
        VisitorLogRepository $visitorLogRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();
        $this->visitorLogRepository = $visitorLogRepository;
        $this->sectionRepository = $sectionRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'visitorLog');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('dailyActivity.visitorLogs');
        $visitorLogs = $this->visitorLogRepository->getAllMine(session('current_employee'))
            ->get();

        return view('visitorLog.index', compact('title', 'visitorLogs'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function filter(Request $request)
    {
        $date = Carbon::create($request->ddate);
        $title = trans('dailyActivity.visitorLogs');

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function ($query) use ($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        if ($request->department_id > 0) {
            $visitorLogs = $this->visitorLogRepository->getAllForSchoolDepartmentDay(session('current_company'), $request->department_id, $date)
                ->get();
        } elseif ($request->employee_id == 'all') {
            $visitorLogs = $this->visitorLogRepository->getAllForSchoolDay(session('current_company'), $date)
                ->get();
        } else {
            $visitorLogs = $this->visitorLogRepository->getForEmployee($request->employee_id, $date)
                ->get();
        }

        return view('visitorLog.load', compact('title', 'visitorLogs', 'request', 'employees'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexAll()
    {
        $title = trans('dailyActivity.visitor_log');
        $visitorLogs = $this->visitorLogRepository->getAllForSchool(session('current_company'))
            ->get();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('All Employees', 'all')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('visitorLog.all', compact('title', 'visitorLogs', 'employees', 'sections'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('DailyActivity.new');
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
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

        return view('layouts.create', compact('title', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorLogRequest $request
     * @return Response
     */
    public function store(VisitorLogRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $visitorLog = new VisitorLog();
                $visitorLog->company_year_id = session('current_company_year');
                $visitorLog->company_id = session('current_company');
                $visitorLog->visited_employee_id = $request->visited_employee_id ?? '';
                $visitorLog->name = $request->name;
                $visitorLog->purpose = $request->purpose;
                $visitorLog->organization = $request->organization;
                $visitorLog->email = $request->email;
                $visitorLog->phone_number = $request->phone_number;
                $visitorLog->car_number = $request->car_number;
                $visitorLog->access_card_number = $request->access_card_number;
                $visitorLog->check_in = $request->check_in;
                $visitorLog->check_out = $request->check_out;
                $visitorLog->observations = $request->observations;
                $visitorLog->save();

                /*Send a thank you email to the visitor*/
                    /*@GeneralHelper::sendNewEmployee_email($user,$employee);*/
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Daily Activity Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function show(VisitorLog $visitorLog)
    {
        $title = $visitorLog->name;
        $action = 'show';

        return view('layouts.show', compact('visitorLog', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function edit(VisitorLog $visitorLog)
    {
        $title = 'Edit '.$visitorLog->name.'';
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'visitorLog', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VisitorLogRequest $request
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function update(VisitorLogRequest $request, VisitorLog $visitorLog)
    {
        try {
            $visitorLog->visited_employee_id = $request->visited_employee_id;
            $visitorLog->name = $request->name;
            $visitorLog->purpose = $request->purpose;
            $visitorLog->organization = $request->organization;
            $visitorLog->email = $request->email;
            $visitorLog->phone_number = $request->phone_number;
            $visitorLog->car_number = $request->car_number;
            $visitorLog->access_card_number = $request->access_card_number;
            $visitorLog->check_in = $request->check_in;
            $visitorLog->check_out = $request->check_out;
            $visitorLog->observations = $request->observations;
            $visitorLog->save();

            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $visitorLog->id,
                'activity'  => 'updated',
            ]);
            /* Flash::success("Employee Information Updated Successfully");*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    /**
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function delete(VisitorLog $visitorLog)
    {
        $title = 'Delete '.$visitorLog->name.'';

        return view('employee.delete', compact('visitorLog', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function destroy(VisitorLog $visitorLog)
    {
        $visitorLog->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $visitorLog->id,
            'activity'  => 'Deleted',
        ]);
        Flash::success('Deleted successfully');

        return 'Deleted';
    }
}
