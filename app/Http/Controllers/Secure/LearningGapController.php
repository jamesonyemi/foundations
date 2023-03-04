<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyGapRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LearningGapRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\CompetencyEmployeeResource;
use App\Http\Resources\LearningGapResource;
use App\Models\Applicant_school;
use App\Models\BscPerspective;
use App\Models\CompetencyGap;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiTimeline;
use App\Models\LearningGap;
use App\Models\Level;
use App\Models\PerspectiveWeight;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;
use Validator;

class LearningGapController extends SecureController
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

        view()->share('type', 'learning_gap');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('competency_gap.gaps');
        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))->get();

            return view('learning_gap.index', compact('title', 'kpis'));
        }

        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))->get();

        return view('learning_gap.index', compact('title', 'kpis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('competency_gap.new_learning_gap');

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

        $competencyGaps = CompetencyGap::whereHas('kpi.employee', function ($q) {
            $q->where('employees.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('title', 'id')
            ->prepend('Select Competency Gap', '')
            ->toArray();

        $kpitimelines = KpiTimeline::where('company_id', session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->title,
                ];
            })->pluck('name', 'id')
            ->prepend('Select Timeline', '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'competencyGaps', 'kpitimelines'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(LearningGapRequest $request)
    {
        try {
            $learningGap = new LearningGap();
            $learningGap->competency_gap_id = $request->competency_gap_id;
            $learningGap->intervention = $request->intervention;
            $learningGap->deadline = $request->deadline;
            $learningGap->save();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">KPI CREATED Successfully</div>');

        /* END OF ONE*/
    }

    /**
     * Display the specified resource.
     *
     * @param LearningGap $learning_gap
     * @return Response
     */
    public function show(LearningGap $learning_gap)
    {
        $title = trans('competency_gap.details');
        $action = 'show';

        return view('layouts.show', compact('learning_gap', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param LearningGap $learning_gap
     * @return Response
     */
    public function edit(LearningGap $learning_gap)
    {
        $title = trans('kpi.edit');

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

        $competencyGaps = CompetencyGap::whereHas('kpi.employee', function ($q) {
            $q->where('employees.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('title', 'id')
            ->prepend('Select Competency Gap', '')
            ->toArray();

        $kpitimelines = KpiTimeline::where('company_id', session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->title,
                ];
            })->pluck('name', 'id')
            ->prepend('Select Timeline', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'competencyGaps', 'employees', 'kpitimelines', 'learning_gap'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiRequest $request
     * @param LearningGap $learning_gap
     * @return Response
     */
    public function update(LearningGapRequest $request, LearningGap $learning_gap)
    {
        try {
            $learning_gap->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($learning_gap->save()) {
            return response('<div class="alert alert-success">KPI CREATED Successfully</div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    public function delete(LearningGap $learning_gap)
    {
        $title = trans('kpi.delete_learning_gap');

        return view('learning_gap.delete', compact('learning_gap', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  LearningGap $learning_gap
     * @return Response
     */
    public function destroy(LearningGap $learning_gap)
    {
        $learning_gap->delete();

        return response()->json(['success'=>'Resource Deleted successfully.']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        DB::table('products')->whereIn('id', explode(',', $ids))->delete();

        return response()->json(['success'=>'Products Deleted successfully.']);
    }

    public function data()
    {
        return new LearningGapResource(LearningGap::get());
    }

    public static function perspectives()
    {
        return new BscPerspectives(BscPerspective::where('company_id', session('current_company'))->get());
    }
}
