<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyMatrixRequest;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\KpiPerformanceResource;
use App\Models\BscPerspective;
use App\Models\Competency;
use App\Models\CompetencyGrade;
use App\Models\CompetencyLevel;
use App\Models\CompetencyMatrix;
use App\Models\CompetencyType;
use App\Models\Kpi;
use App\Models\KpiPerformance;
use App\Models\Kra;
use App\Models\Level;
use App\Models\Position;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;

class CompetencyMatrixController extends SecureController
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

        view()->share('type', 'competency_matrix');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kpi.matrix');

        return view('competency_matrix.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'Define New Competency Matrix';

        $competencyGrades = CompetencyGrade::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Position', '')
            ->toArray();

        $competencies = Competency::where('competencies.company_id', session('current_company'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Competency', '')
            ->toArray();

        return view('layouts.create', compact('title', 'competencies', 'competencyGrades'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|CompetencyMatrixRequest $request
     * @return Response
     */
    public function store(CompetencyMatrixRequest $request)
    {
        try {
            $competencyMatrix = new CompetencyMatrix();
            $competencyMatrix->company_id = session('current_company');
            $competencyMatrix->competency_id = $request->competency_id;
            $competencyMatrix->competency_grade_id = $request->competency_grade_id;
            $competencyMatrix->description = $request->description;
            $competencyMatrix->save();
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">MATRIX DEFINITION CREATED Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param CompetencyMatrix $competency_matrix
     * @return Response
     */
    public function show(CompetencyMatrix $competency_matrix)
    {
        $title = 'Competency Matrix Details';
        $action = 'show';

        return view('layouts.show', compact('competency_matrix', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyMatrix $competency_matrix
     * @return Response
     */
    public function edit(CompetencyMatrix $competency_matrix)
    {
        $title = 'Edit Competency Matrix Definition';

        $competencyGrades = CompetencyGrade::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Grade', '')
            ->toArray();

        $competencies = Competency::where('competencies.company_id', session('current_company'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Competency', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'competency_matrix', 'competencyGrades', 'competencies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiPerformanceRequest $request
     * @param CompetencyMatrix $competency_matrix
     * @return Response
     */
    public function update(CompetencyMatrixRequest $request, CompetencyMatrix $competency_matrix)
    {
        try {
            $competency_matrix->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">MATRIX UPDATED SUCCESSFULLY</div>');
    }

    public function delete(CompetencyMatrix $competency_matrix)
    {
        $title = 'Delete Matrix Definition';

        return view('competency_matrix.delete', compact('competency_matrix', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CompetencyMatrix $competency_matrix
     * @return Response
     */
    public function destroy(CompetencyMatrix $competency_matrix)
    {
        try {
            $competency_matrix->delete();
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">MATRIX DELETED SUCCESSFULLY</div>');
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
