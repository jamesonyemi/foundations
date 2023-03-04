<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyFrameworkRequest;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\CompetencyFrameworkResource;
use App\Http\Resources\KpiPerformanceResource;
use App\Models\BscPerspective;
use App\Models\Competency;
use App\Models\CompetencyFramework;
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
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;
use Validator;

class CompetencyFrameworkController extends SecureController
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

        view()->share('type', 'competency_framework');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Competency Framework';

        return view('competency_framework.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Competency Framework';

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

        $positions = Position::get()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $competencyMatrix = CompetencyMatrix::whereHas('competency', function ($q) {
            $q->where('competencies.company_id', session('current_company'));
        })->get()
            ->pluck('full_title', 'id')
            ->toArray();

        $businessCompetencies = Competency::where('company_id', session('current_company'))->where('competency_type_id', 2)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '0')
            ->toArray();

        $leadershipCompetencies = Competency::where('company_id', session('current_company'))->where('competency_type_id', 3)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $peopleCompetencies = Competency::where('company_id', session('current_company'))->where('competency_type_id', 4)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $departments = Department::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'positions', 'competencyMatrix', 'businessCompetencies', 'leadershipCompetencies', 'peopleCompetencies', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|CompetencyFrameworkRequest $request
     * @return Response
     */
    public function store(CompetencyFrameworkRequest $request)
    {
        $competency_matrix_ids = $request['competency_matrix_id'];
        $position_id = $request['position_id'];
        $section_id = $request['section_id'];

        try {
            if (isset($request['competency_matrix_id'])) {
                foreach ($competency_matrix_ids as $index => $competency_matrix_id) {
                    $competencyFramework = new CompetencyFramework();
                    $competencyFramework->competency_matrix_id = $competency_matrix_id;
                    $competencyFramework->position_id = $position_id;
                    $competencyFramework->section_id = $section_id;
                    $competencyFramework->save();
                }
            }

            /* END OF ONE*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Framework Created Successfully</div>');
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
     * @param CompetencyFramework $competency_framework
     * @return Response
     */
    public function show(CompetencyFramework $competency_framework)
    {
        $title = 'Competency Framework Details';
        $action = 'show';

        return view('layouts.show', compact('competency_framework', 'title', 'action'));
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
        $positions = Position::where('company_id', session('current_company'))->get()
            ->pluck('title', 'id')
            ->prepend('Select Position', '')
            ->toArray();

        $competencies = $competency_type->competencies;

        return view('competency_type._details', compact('competency_type', 'title', 'action', 'positions', 'competencies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyFramework $competency_framework
     * @return Response
     */
    public function edit(CompetencyFramework $competency_framework)
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
            ->prepend('Select Responsibility', '')
            ->toArray();

        $positions = Position::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $competencyMatrix = CompetencyMatrix::whereHas('competency', function ($q) {
            $q->where('competencies.company_id', session('current_company'));
        })->get()
            ->pluck('full_title', 'id')
            ->toArray();

        $departments = Department::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('section.select_section'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'competency_framework', 'competencyMatrix', 'departments', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|$request
     * @param CompetencyFramework $competency_framework
     * @return Response
     */
    public function update(CompetencyFrameworkRequest $request, CompetencyFramework $competency_framework)
    {
        try {
            $competency_framework->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">FRAMEWORK UPDATED SUCCESSFULLY</div>');
    }

    public function delete(CompetencyFramework $competency_framework)
    {
        $title = 'Delete Competency Framework';

        return view('competency_framework.delete', compact('competency_framework', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  KpiPerformance $kPerformance
     * @return Response
     */
    public function destroy(CompetencyFramework $competency_framework)
    {
        $competency_framework->delete();

        return 'Deleted';
    }

    public static function data()
    {
        return new CompetencyFrameworkResource(CompetencyFramework::where('company_id', session('current_company')));
    }
}
