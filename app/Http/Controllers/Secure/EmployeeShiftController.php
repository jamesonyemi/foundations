<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\employeeShiftRequest;
use App\Models\Employee;
use App\Models\EmployeeKpiActivity;
use App\Models\EmployeeShift;
use App\Models\UserDocument;
use App\Notifications\SendSMS;
use App\Repositories\EmployeeShiftRepository;
use App\Repositories\SectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Sentinel;

class EmployeeShiftController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var EmployeeShiftRepository
     */
    private $employeeShiftRepository;

    protected $module = 'employeeShift';

    /**
     * EmployeeController constructor.
     * @param employeeShiftRepository $employeeShiftRepository
     */
    public function __construct(
        EmployeeShiftRepository $employeeShiftRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();
        $this->employeeShiftRepository = $employeeShiftRepository;
        $this->sectionRepository = $sectionRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'employeeShift');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('employeeShift.dailyActivities');
        $shifts = $this->employeeShiftRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('employeeShift.index', compact('title', 'shifts'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function filter(Request $request)
    {
        $date = Carbon::create($request->ddate);
        $title = trans('employeeShift.dailyActivities');

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function ($query) use ($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        if ($request->department_id > 0) {
            $dailyActivities = $this->employeeShiftRepository->getAllForSchoolDepartmentDay(session('current_company'), $request->department_id, $date)
                ->get();
        } elseif ($request->employee_id == 'all') {
            $dailyActivities = $this->employeeShiftRepository->getAllForSchoolDay(session('current_company'), $date)
                ->get();
        } else {
            $dailyActivities = $this->employeeShiftRepository->getForEmployee($request->employee_id, $date)
                ->get();
        }

        return view('employeeShift.load', compact('title', 'dailyActivities', 'request', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('employeeShift.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EmployeeShiftRequest $request
     * @return Response
     */
    public function store(EmployeeShiftRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $employeeShift = new EmployeeShift();
                $employeeShift->company_id = session('current_company');
                $employeeShift->title = $request->title;
                $employeeShift->description = $request->description;
                $employeeShift->save();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Daily Activity Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param EmployeeShift $employeeShift
     * @return Response
     */
    public function show(EmployeeShift $employeeShift)
    {
        $title = $employeeShift->title;
        $action = 'show';

        return view('layouts.show', compact('employeeShift', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EmployeeShift $employeeShift
     * @return Response
     */
    public function edit(EmployeeShift $employeeShift)
    {
        $title = 'Edit '.$employeeShift->title.'';
        $activities = EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'));
        })->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->kpi) ? $item->kpi->full_title.'  '.' |'.$item->title.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Kpi Activity', 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'employeeShift', 'activities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EmployeeShiftRequest $request
     * @param EmployeeShift $employeeShift
     * @return Response
     */
    public function update(EmployeeShiftRequest $request, EmployeeShift $employeeShift)
    {
        try {
            $employeeShift->title = $request->title;
            $employeeShift->description = $request->description;
            $employeeShift->save();

            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $employeeShift->id,
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
     * @param EmployeeShift $employeeShift
     * @return Response
     */
    public function delete(EmployeeShift $employeeShift)
    {
        $title = 'Delete '.$employeeShift->title.'';

        return view('employee.delete', compact('employeeShift', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param EmployeeShift $employeeShift
     * @return Response
     */
    public function destroy(EmployeeShift $employeeShift)
    {
        $employeeShift->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $employeeShift->id,
            'activity'  => 'Deleted',
        ]);
        Flash::success('Deleted successfully');

        return 'Deleted';
    }
}
