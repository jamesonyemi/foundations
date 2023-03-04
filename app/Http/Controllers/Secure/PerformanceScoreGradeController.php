<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PerformanceGradeRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\CompetencyEmployeeResource;
use App\Http\Resources\KpiPerformanceResource;
use App\Http\Resources\PerformanceScoreGradeResource;
use App\Models\BscPerspective;
use App\Models\EmployeeCompetencyMatrix;
use App\Models\Kpi;
use App\Models\KpiPerformance;
use App\Models\Kra;
use App\Models\Level;
use App\Models\MarkValue;
use App\Models\PerformanceGrade;
use App\Models\PerformanceScoreGrade;
use App\Models\Position;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;

class PerformanceScoreGradeController extends SecureController
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
     * DirectionController constructor.
     *
     * @param KpiRepository $kpiRepository
     * @param EmployeeRepository $employeeRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        KpiRepository $kpiRepository,
    ) {
        parent::__construct();

        $this->employeeRepository = $employeeRepository;
        $this->kpiRepository = $kpiRepository;

        view()->share('type', 'performance_score_grade');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Performance Grades';

        return view('performance_score_grade.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Performance Score Grade';

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

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(PerformanceGradeRequest $request)
    {
        try {
            $markValue = new PerformanceScoreGrade($request->all());
            $markValue->company_id = session('current_company');
            $markValue->save();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Performance Grade Added Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param PerformanceGrade $performance_score_grade
     * @return Response
     */
    public function show(PerformanceScoreGrade $performance_score_grade)
    {
        $title = 'Grade Details';
        $action = 'show';

        return view('layouts.show', compact('performance_score_grade', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param PerformanceGrade $performance_score_grade
     * @return Response
     */
    public function edit(PerformanceScoreGrade $performance_score_grade)
    {
        $title = 'Edit Performance Grade';

        return view('layouts.edit', compact('title', 'performance_score_grade'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param PerformanceGrade $performance_score_grade
     * @return Response
     */
    public function update(PerformanceGradeRequest $request, PerformanceScoreGrade $performance_score_grade)
    {
        try {
            $performance_score_grade->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Performance Grade Updated Successfully</div>');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PerformanceGrade $performance_score_grade
     * @return Response
     */
    public function destroy($performance_score_grade)
    {
        $key = PerformanceScoreGrade::find($performance_score_grade);
        $key->delete();

        return 'Deleted';
    }

    public function data()
    {
        return new PerformanceScoreGradeResource(PerformanceScoreGrade::orderBy('id', 'desc')->get());
    }
}
