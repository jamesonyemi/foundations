<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\KpiPerformanceResource;
use App\Models\BscPerspective;
use App\Models\Kpi;
use App\Models\KpiPerformance;
use App\Models\Kra;
use App\Models\Level;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;

class LearningManagementController extends SecureController
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
        $title = trans('kpi.kpis');
        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))->get();

            return view('kpi.index', compact('title', 'kpis'));
        }

        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))

            ->get();

        return view('kpi_performance.index', compact('title', 'kpis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kpi.new_review');

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Responsibility', 0)
            ->toArray();

        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))
                ->get()
                ->map(function ($item) {
                    return [
                        'id'   => $item->id,
                        'name' => isset($item->title) ? $item->kra->full_title.'  '.' |'.$item->title.'| ' : '',
                    ];
                })->pluck('name', 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        } else {
            $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->title) ? $item->kra->full_title.'  '.' |'.$item->title.'| ' : '',
                ];
            })->pluck('name', 'id')
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
        try {
            if (isset($request['kpi_id'])) {
                foreach ($kpi_ids as $index => $kpi_id) {
                    if (! empty($kpi_id)) {
                        $performance = new KpiPerformance();
                        $performance->kpi_id = $kpi_id;
                        $performance->comment = $comments[$index];
                        $performance->save();
                    }
                }
            }

            /* END OF ONE*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($performance->save()) {
            return response('<div class="alert alert-success">KPI NOTE CREATED Successfully</div>');
        } else {
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
        $title = trans('level.details');
        $action = 'show';

        return view('layouts.show', compact('kpi_performance', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param KpiPerformance $kpi_performance
     * @return Response
     */
    public function edit(KpiPerformance $kpi_performance)
    {
        $title = trans('level.edit');

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Responsibility', '')
            ->toArray();

        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))
                ->get()
                ->map(function ($item) {
                    return [
                        'id'   => $item->id,
                        'name' => isset($item->title) ? $item->kra->full_title.'  '.' |'.$item->title.'| ' : '',
                    ];
                })->pluck('name', 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        } else {
            $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))
                ->get()
                ->map(function ($item) {
                    return [
                        'id'   => $item->id,
                        'name' => isset($item->title) ? $item->kra->full_title.'  '.' |'.$item->title.'| ' : '',
                    ];
                })->pluck('name', 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        }

        return view('layouts.edit', compact('title', 'kpi_performance', 'kpis'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiPerformanceRequest $request
     * @param KpiPerformance $kpi_performance
     * @return Response
     */
    public function update(KpiPerformanceRequest $request, KpiPerformance $kpi_performance)
    {
        try {
            $kpi_performance->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($kpi_performance->save()) {
            return response('<div class="alert alert-success">KPI NOTE UPDATED SUCCESSFULLY</div>');
        } else {
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

        return redirect('/kpi_performance');
    }

    public static function data()
    {
        return new KpiPerformanceResource(KpiPerformance::all());
    }

    public function findSectionLevel(Request $request)
    {
        $directions = $this->levelRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();

        return $directions;
    }
}
