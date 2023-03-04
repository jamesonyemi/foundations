<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\KpiPerformanceResource;
use App\Http\Resources\ProjectResource;
use App\Models\BscPerspective;
use App\Models\Kpi;
use App\Models\KpiPerformance;
use App\Models\Kra;
use App\Models\Level;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;

class ProjectManagementController extends SecureController
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

        view()->share('type', 'project_management');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('project.projects');
        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))->get();

            return view('kpi.index', compact('title', 'kpis'));
        }

        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))

            ->get();

        return view('project_management.index', compact('title', 'kpis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('project.new');

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

        $projectTypes = ProjectType::where('company_id', session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->title) ? $item->title : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Project Type', 0)
            ->toArray();

        return view('layouts.create', compact('title', 'projectTypes', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(KpiPerformanceRequest $request)
    {
        $project_type_ids = $request['project_type_id'];
        $titles = $request['title'];
        $descriptions = $request['description'];
        try {
            if (isset($request['project_type_id'])) {
                foreach ($project_type_ids as $index => $project_type_id) {
                    if (! empty($project_type_id)) {
                        $project = new Project();
                        $project->project_type_id = $project_type_id;
                        $project->title = $titles[$index];
                        $project->description = $descriptions[$index];
                        $project->company_id = session('current_company');
                        $project->save();
                    }
                }
            }

            /* END OF ONE*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($project->save()) {
            return response('<div class="alert alert-success">PROJECT CREATED SUCCESSFULLY</div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Project $project_management
     * @return Response
     */
    public function show(Project $project_management)
    {
        $title = trans('project.details');
        $action = 'show';

        return view('layouts.show', compact('project_management', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Project $project_management
     * @return Response
     */
    public function edit(Project $project_management)
    {
        $title = trans('project.edit');

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
        $projectTypes = ProjectType::where('company_id', session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->title) ? $item->title : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Project Type', 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'project_management', 'projectTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiPerformanceRequest $request
     * @param Project $project_management
     * @return Response
     */
    public function update(KpiPerformanceRequest $request, Project $project_management)
    {
        try {
            $project_management->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($project_management->save()) {
            return response('<div class="alert alert-success">PROJECT UPDATED SUCCESSFULLY</div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    public function delete(Project $project_management)
    {
        $title = trans('project.delete');

        return view('project_management.delete', compact('project_management', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Project $project_management
     * @return Response
     */
    public function destroy(Project $project_management)
    {
        $project_management->delete();

        return redirect('/project_management');
    }

    public static function data()
    {
        return new ProjectResource(Project::where('company_id', session('current_company'))->get());
    }
}
