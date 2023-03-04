<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyGapRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Models\Applicant_school;
use App\Models\BscPerspective;
use App\Models\CompetencyGap;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiTimeline;
use App\Models\Level;
use App\Models\PerspectiveWeight;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;
use Validator;

class CompetencyGapController extends SecureController
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

        view()->share('type', 'competency_gap');
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

            return view('competency_gap.index', compact('title', 'kpis'));
        }

        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))->get();

        return view('competency_gap.index', compact('title', 'kpis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('competency_gap.new');

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

        $krasFinancial = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', 1);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
            ->toArray();

        $krasCustomer = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', 2);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
            ->toArray();

        $krasInternal = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', 3);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
            ->toArray();

        $krasLearning = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', 4);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
            ->toArray();

        $krasLiving = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', 5);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
            ->toArray();

        $krasPersonal = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', 6);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi Objective', '')
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

        return view('layouts.create', compact('title', 'employees', 'krasCustomer', 'krasFinancial', 'krasInternal', 'krasLearning', 'krasLiving', 'krasPersonal', 'kpitimelines'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(Request $request)
    {
        $kpi_ids = $request['kpi_id'];
        $titles = $request['title'];

        try {
            foreach ($kpi_ids as $index => $kpi_id) {
                if (! empty($titles[$index])) {
                    $competencyGap = new CompetencyGap();
                    $competencyGap->kpi_id = $kpi_id;
                    $competencyGap->title = $titles[$index];
                    $competencyGap->save();
                }
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Competency Gap Successfully</div>');

        /* END OF ONE*/
    }

    public function calPerspectivePercentage(KpiRequest $request)
    {
        $weights = $request['weight'];

        $percentage = array_sum($weights);
        if ($percentage <= 100) {
            return response('<div class="alert alert-success">'.$percentage.'</div>');
        } else {
            return response('<div class="alert alert-danger">'.$percentage.'</div>');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param CompetencyGap $competency_gap
     * @return Response
     */
    public function show(CompetencyGap $competency_gap)
    {
        $title = trans('competency_gap.details');
        $action = 'show';

        return view('layouts.show', compact('competency_gap', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Kpi $kpi
     * @return Response
     */
    public function edit(CompetencyGap $competency_gap)
    {
        $title = 'Edit Competency Gap';

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

        /*$krasFinancial = $this->kraRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'), 1)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select KRA', 0)
            ->toArray();

        $krasCustomer = $this->kraRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'), 2)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select KRA', 0)
            ->toArray();

        $krasInternal = $this->kraRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'), 3)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select KRA', 0)
            ->toArray();

        $krasLearning = $this->kraRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'), 4)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select KRA', 0)
            ->toArray();

        $krasLiving = $this->kraRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'), 5)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select KRA', 0)
            ->toArray();

        $krasPersonal = $this->kraRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'), 6)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select KRA', 0)
            ->toArray();*/

        $kpiObjectives = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kras.company_id', session('current_company'))
                ->where('kras.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
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

        return view('layouts.edit', compact('title', 'competency_gap', 'kpiObjectives', 'employees', 'kpitimelines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiRequest $request
     * @param CompetencyGap $competency_gap
     * @return Response
     */
    public function update(CompetencyGapRequest $request, CompetencyGap $competency_gap)
    {
        try {
            $competency_gap->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($competency_gap->save()) {
            return response('<div class="alert alert-success">KPI CREATED Successfully</div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    public function delete(CompetencyGap $competency_gap)
    {
        $title = 'Delete Competency Gap';

        return view('competency_gap.delete', compact('competency_gap', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CompetencyGap $competency_gap
     * @return Response
     */
    public function destroy(CompetencyGap $competency_gap)
    {
        $competency_gap->delete();

        return 'Deleted';
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
        $levels = $this->levelRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'section' => $level->section->title,
                ];
            });

        return Datatables::make($levels)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.edit\', Sentinel::getUser()->permissions)))
										<a href="{{ url(\'/levels/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.show\', Sentinel::getUser()->permissions)))
                                    	<a href="{{ url(\'/levels/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @endif
                                     @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.delete\', Sentinel::getUser()->permissions)))
                                     	<a href="{{ url(\'/levels/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
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

    /*    public static function perspectives()
        {
            $data = [];

            $bscPerspectives = BscPerspective::where('company_id', session('current_company'))->get();
            foreach ($bscPerspectives as $key) {
                $orders = [];
                foreach ($key->kras as $kra) {

                    array_push($orders, [
                            "RecordID" => $kra->id,
                            "OrderID" => $kra->title,
                            "Country" => $kra->title,
                            "ShipCountry" => $kra->title,
                            "ShipCity" => $kra->title,
                            "ShipName" => $kra->title,
                            "ShipAddress" => $kra->title,
                            "CompanyAgent" => $kra->title,
                            "Status" => 5,
                            "Type" => 1,
                        ]
                    );
                }

                array_push($data, [
                    "data" => $orders,


                ]
                );


            }
            return json_encode($data, JSON_UNESCAPED_SLASHES);

        }*/

    public static function perspectives()
    {
        return new BscPerspectives(BscPerspective::where('company_id', session('current_company'))->get());
    }
}
