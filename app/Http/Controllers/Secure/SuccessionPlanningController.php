<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\SuccessionPlanningRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\KpiPerformanceResource;
use App\Http\Resources\SuccessionPlanningResource;
use App\Models\BscPerspective;
use App\Models\CompanyYear;
use App\Models\Competency;
use App\Models\CompetencyLevel;
use App\Models\CompetencyMatrix;
use App\Models\CompetencyType;
use App\Models\Department;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiPerformance;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Level;
use App\Models\Position;
use App\Models\SchoolDirection;
use App\Models\SuccessionPlanning;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class SuccessionPlanningController extends SecureController
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

        view()->share('type', 'succession_planning');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Succession Planning';

        $successionPlannings = SuccessionPlanning::get();

        return view('succession_planning.index', compact('title', 'successionPlannings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Succession Planning Definition';

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

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $years = CompanyYear::get()
            ->pluck('title', 'id')
            ->prepend('Select Year', '')
            ->toArray();

        $departments = Department::get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'positions', 'years', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|SuccessionPlanningRequest $request
     * @return Response
     */
    public function store(SuccessionPlanningRequest $request)
    {
        try {
            $SuccessionPlanning = new SuccessionPlanning();
            $SuccessionPlanning->employee_id = $request->employee_id;
            $SuccessionPlanning->position_id = $request->position_id;
            $SuccessionPlanning->section_id = $request->section_id;
            $SuccessionPlanning->ready_year_id = $request->ready_year_id;
            $SuccessionPlanning->remarks = $request->remarks;
            $SuccessionPlanning->save();
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Succession Plan Created Successfully</div>');
    }

    public function addCompetency(Request $request)
    {
        $competency_type_id = $request['competency_type_id'];
        $position_ids = $request['position_id'];
        $titles = $request['title'];

        $rules = [];

        foreach ($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
            $rules["position_id.{$key}"] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            try {
                foreach ($titles as $index => $title) {
                    if (! empty($titles[$index])) {
                        $competency = new Competency();
                        $competency->position_id = $position_ids[$index];
                        $competency->competency_type_id = $competency_type_id;
                        $competency->company_id = session('current_company');
                        $competency->title = $title;
                        $competency->save();
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }

            /* return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;*/
            $competencies = CompetencyType::find($request['competency_type_id'])->competencies;

            return view('competency_type.competencies', compact('competencies'));
        }

        return response()->json(['error'=>$validator->errors()->all()]);

        /* END OF ONE*/
    }

    /**
     * Display the specified resource.
     *
     * @param SuccessionPlanning $succession_planning
     * @return Response
     */
    public function show(SuccessionPlanning $succession_planning)
    {
        $title = 'Competency Framework Details';
        $action = 'show';

        return view('layouts.show', compact('succession_planning', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyType $competency_type
     * @return Response
     */
    public function showCompetencyType(CompetencyType $competency_type)
    {
        $title = 'Competency Type Details';
        $action = 'show';
        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend('Select Position', '')
            ->toArray();

        $competencies = $competency_type->competencies;

        return view('competency_type._details', compact('competency_type', 'title', 'action', 'positions', 'competencies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param SuccessionPlanning $succession_planning
     * @return Response
     */
    public function edit(SuccessionPlanning $succession_planning)
    {
        $title = 'Edit Competency Framework';

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

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $years = CompanyYear::get()
            ->pluck('title', 'id')
            ->prepend('Select Year', '')
            ->toArray();

        $departments = Department::get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'succession_planning', 'employees', 'departments', 'positions', 'years'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|$request
     * @param SuccessionPlanning $succession_planning
     * @return Response
     */
    public function update(SuccessionPlanningRequest $request, SuccessionPlanning $succession_planning)
    {
        try {
            $succession_planning->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success"> UPDATED SUCCESSFULLY</div>');
    }

    public function delete(SuccessionPlanning $succession_planning)
    {
        $title = 'Delete Competency Framework';

        return view('succession_planning.delete', compact('succession_planning', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  KpiPerformance $kPerformance
     * @return Response
     */
    public function destroy(SuccessionPlanning $succession_planning)
    {
        $succession_planning->delete();

        return 'Deleted';
    }

    public static function data()
    {
        return new SuccessionPlanningResource(SuccessionPlanning::where('company_id', session('current_company')));
    }
}
