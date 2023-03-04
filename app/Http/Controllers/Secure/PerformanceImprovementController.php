<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PerformanceGradeRequest;
use App\Http\Requests\Secure\PerformanceImprovementRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\CompetencyEmployeeResource;
use App\Http\Resources\KpiPerformanceResource;
use App\Http\Resources\PerformanceImprovementResource;
use App\Http\Resources\PerformanceScoreGradeResource;
use App\Models\BscPerspective;
use App\Models\Employee;
use App\Models\EmployeeCompetencyMatrix;
use App\Models\Kpi;
use App\Models\KpiPerformance;
use App\Models\Kra;
use App\Models\Level;
use App\Models\PerformanceGrade;
use App\Models\PerformanceImprovement;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;

class PerformanceImprovementController extends SecureController
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

        view()->share('type', 'performance_improvement');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Performance Improvement Employees';

        return view('performance_improvement.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Improvement Employees';

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

        $supervisors = Employee::find(session('current_employee'))->supervisors2
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Supervisor', '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'supervisors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(PerformanceImprovementRequest $request)
    {
        $employee_ids = $request['employee_id'];

        try {
            foreach ($employee_ids as $index => $employee_id) {
                PerformanceImprovement::firstOrCreate(
                    [
                        'employee_id' => $employee_id,
                        'kpis' => $request->kpis,
                        'end_date' => $request->end_date,
                        'supervisor_employee_id' => $request->supervisor_employee_id,
                        'company_year_id' => session('current_company_year'),
                    ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employees Added Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param PerformanceImprovement $performance_improvement
     * @return Response
     */
    public function show(PerformanceImprovement $performance_improvement)
    {
        $title = trans('level.details');
        $action = 'show';

        return view('layouts.show', compact('performance_improvement', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param PerformanceGrade $performance_grade
     * @return Response
     */
    public function edit(PerformanceImprovement $performance_improvement)
    {
        $title = 'Edit Performance Improvement Plan';
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

        $supervisors = Employee::find(session('current_employee'))->supervisors2
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Supervisor', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'performance_improvement', 'employees', 'supervisors'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param PerformanceImprovement $performance_improvement
     * @return Response
     */
    public function update(PerformanceImprovementRequest $request, PerformanceImprovement $performance_improvement)
    {
        try {
            $performance_improvement->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Performance Improvement Plan Updated Successfully</div>');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PerformanceImprovement $performance_improvement
     * @return Response
     */
    public function destroy(PerformanceImprovement $performance_improvement)
    {
        $performance_improvement->delete();

        return 'Employee Removed';
    }

    public function data()
    {
        return new PerformanceImprovementResource(PerformanceImprovement::whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->get());
    }
}
