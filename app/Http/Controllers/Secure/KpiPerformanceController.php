<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\KpiPerformanceResource;
use App\Models\BscPerspective;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\EmployeeKpiActivity;
use App\Models\KpiPerformance;
use App\Models\KpiActivityStatus;
use App\Models\KpiResponsibility;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Level;
use App\Models\PerspectiveWeight;
use App\Repositories\KraRepository;
use App\Repositories\EmployeeRepository;
use App\Models\SchoolDirection;
use App\Repositories\KpiRepository;
use App\Helpers\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KpiPerformanceController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;
    /**
     * @var KpiRepository
     */
    private $kpiRepository;
    /**
     * @var KraRepository
     */
    private $kraRepository;

    /**
     * DirectionController constructor.
     *
     * @param KpiRepository $kpiRepository
     * @param EmployeeRepository $employeeRepository
     * @param KraRepository $kraRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        KpiRepository $kpiRepository,
        KraRepository $kraRepository
    ) {

        parent::__construct();

        $this->employeeRepository = $employeeRepository;
        $this->kpiRepository = $kpiRepository;
        $this->kraRepository = $kraRepository;

        view()->share('type', 'kpi_performance');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kpi.kpis_execution');

        $kpis= KpiResponsibility::whereHas('kpi')->where('responsible_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->with('kpi', 'responsibilities')->get()->unique('kpi_id')
            ->map(function ($item) {
                return [
                    "id"   => $item->kpi_id,
                    "name" => $item->kpi->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select KPI', '')
            ->toArray();


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();

        $activities= EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'));
        })->get();
        return view('kpi_performance.index', compact('title',  'activities', 'perspectives', 'kpitimelines', 'kpis'));
    }




    public function filter(Request $request)
    {
        $date = Carbon::create($request->ddate);
        $nowDate = Carbon::create(now());
        $title = 'KPI Activities';

        if($request->kpi_id > 0) {
            $activities= EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) use ($request) {
                $q->where('kpis.company_year_id', session('current_company_year'))
                    ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'))
                    ->where('employee_kpi_activities.kpi_id', $request->kpi_id);
            })->orderBy('due_date', 'ASC')->get();
        }

        /*elseif($request->month != '') {
            $activities= EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) use ($request) {
                $q->where('kpis.company_year_id', session('current_company_year'))
                    ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'))
                    ->whereMonth('due_date', $request->month);
            })->get();
        }*/
        elseif($request->ddate != '') {
            $activities= EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) use ($date) {
                $q->where('kpis.company_year_id', session('current_company_year'))
                    ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'))
                    ->whereMonth('employee_kpi_activities.due_date', $date->month);
            })->orderBy('due_date', 'ASC')->get();
        }
        else {
            $activities= EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) {
                $q->where('kpis.company_year_id', session('current_company_year'))
                    ->whereMonth('employee_kpi_activities.due_date', Carbon::now()->month);
            })->orderBy('due_date', 'ASC')->get();
        }

        return view('kpi_performance.load', compact('title', 'request', 'activities'));
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kpi.kpis_execution');

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Responsibility', 0)
            ->toArray();

        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))
                ->get()
                ->map(function ($item) {
                    return [
                        "id"   => $item->id,
                        "name" => isset($item->kpiObjective) ? $item->kpiObjective->full_title. '  '. ' |' .$item->title . '| ' : "",
                    ];
                })->pluck("name", 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        }
        else
        {
        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->kpiObjective) ? $item->kpiObjective->full_title. '  '. ' |' .$item->title . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select KPI', 0)
            ->toArray();
        }
        return view('layouts.create', compact('title', 'kpis', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(KpiPerformanceRequest $request)
    {
        $kpi_ids = $request['kpi_id'];
        $comments = $request['comment'];
        try
        {
            if (isset($request['kpi_id'])){
                foreach ($kpi_ids as $index => $kpi_id)
                {
                    if (!empty($kpi_id) )
                    {
                        $performance = new KpiPerformance();
                        $performance->kpi_id = $kpi_id;
                        $performance->comment = $comments[$index];
                        $performance->save();
                    }
                }

            }

            /* END OF ONE*/



        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }


        if ($performance->save())
        {

            return response('<div class="alert alert-success">KPI NOTE CREATED Successfully</div>') ;
        }
        else
        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param KpiPerformance $kpi_performance
     * @return Response
     */
    public function show(KpiPerformance $kpi_performance)
    {
        $title = trans('kpi.performance_details');
        $action = 'show';
        return view('layouts.show', compact('kpi_performance', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param KpiPerformance $kpi_performance
     * @return Response
     */
    public function edit(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity');

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Responsibility', '')
            ->toArray();




        return view('kpi_performance._editActivity', compact('title', 'kpi_activity'));
    }



    public function editActivity(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity');

        $kpiStatus = KpiActivityStatus::all()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Status', '')
            ->toArray();




        return view('kpi_performance._editActivity', compact('title', 'kpi_activity', 'kpiStatus'));
    }


    public function editActivityModal01(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity');

        $kpiStatus = KpiActivityStatus::all()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Status', '')
            ->toArray();

        return view('kpi_performance._editActivityModal01', compact('title', 'kpi_activity', 'kpiStatus'));
    }


    public function editActivityModal02(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity');

        $kpiStatus = KpiActivityStatus::all()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Status', '')
            ->toArray();

        return view('kpi_performance._editActivityModal02', compact('title', 'kpi_activity', 'kpiStatus'));
    }

    public function editActivityModal03(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity');

        $kpiStatus = KpiActivityStatus::all()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Status', '')
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

        $subsidiaries = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();

        $company_daily_Activity = $kpi_activity->companyIds()
            ->pluck('company_id')
            ->toArray();

        return view('kpi_performance._editActivityModal03', compact('title', 'kpi_activity', 'kpiStatus', 'company_daily_Activity', 'employees', 'subsidiaries'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiPerformanceRequest $request
     * @param EmployeeKpiActivity $kpi_activity
     * @return Response
     */
    public function update(Request $request, EmployeeKpiActivity $kpi_activity)
    {
        dd($request);
        try
        {
            $kpi_activity->update($request->all());
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }


        if ($kpi_activity->save())
        {

            return response('<div class="alert alert-success">KPI ACTIVITY UPDATED SUCCESSFULLY</div>') ;
        }
        else
        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }

    }

    public function delete(KpiPerformance $kpi_performance)
    {
        $title = trans('level.delete');
        return view('kpi_performance.delete', compact('kpi_performance', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  KpiPerformance $kPerformance
     * @return Response
     */
    public function destroy(KpiPerformance $kpi_performance)
    {
        $kpi_performance->delete();
    }




    public static function data()
    {
        return new KpiPerformanceResource(KpiPerformance::
        whereHas('kpi', function ($q)  {
            $q->where('kpis.company_id', session('current_company'));;
           })->get());

    }



    public function findTimeLineKpi(Request $request)
    {
        $kpiTimeLine = KpiTimeline::find($request->kpi_timeline_id);
        $title = @$kpiTimeLine->title.' Kpis';
        /*$kpis = $kpiTimeLine->timeLineKpis($kpiTimeLine->id, $request->perspective_id, session('current_employee'));*/
        $perspectiveWeight = PerspectiveWeight::where('bsc_perspective_id', $request->perspective_id)
            ->where('employee_id', session('current_employee'))
            ->where('company_year_id', session('current_company_year'))
            ->first();
        $perspective_id = $request->perspective_id;
        $employee = $this->currentEmployee;

        if($request->perspective_id > 0) {
            $perspectives = BscPerspective::where('id', $request->perspective_id)->get();
        }

        else {
            $perspectives = BscPerspective::all();
        }

        return view('kpi_performance_review._self_kpis', compact('title', 'perspectiveWeight', 'kpiTimeLine', 'perspective_id', 'employee', 'perspectives', 'request'));
    }


    public function findSubordinateTimeLineKpi(Request $request)
    {

        $employee = Employee::find($request->employee_id);
        $kpiTimeLine = KpiTimeline::find($request->kpi_timeline_id);
        $title = @$kpiTimeLine->title.' Kpis';

        $perspective_id = $request->perspective_id;

        if($request->perspective_id > 0) {
            $perspectives = BscPerspective::where('id', $request->perspective_id)->get();
        }

        else {
            $perspectives = BscPerspective::all();
        }

        return view('kpi_performance_review._kpis', compact('title', 'kpiTimeLine', 'employee', 'perspective_id', 'perspectives', 'request'));
    }


    public function findAllKpi(Request $request)
    {

        $employee = Employee::find($request->employee_id);
        $title = $employee->user->full_name.' Review';
        $perspectives = BscPerspective::all();
        return view('kpi_performance_review.allBsc', compact('perspectives', 'title', 'employee'));
    }

}
