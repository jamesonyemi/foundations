<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Models\Applicant_school;
use App\Models\BscPerspective;
use App\Models\Kpi;
use App\Models\KpiTimeline;
use App\Models\Level;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;
use Validator;

class TestController extends SecureController
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

        view()->share('type', 'test');
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

        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))->get();

        return view('test.index', compact('title', 'kpis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kpi.new');

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

        $krasFinancial = $this->kraRepository
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
    public function store(KpiRequest $request)
    {
        $rules = [];

        foreach ($request->input('name') as $key => $value) {
            $rules["name.{$key}"] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            foreach ($request->input('name') as $key => $value) {
                TagList::create(['name'=>$value]);
            }

            return response()->json(['success'=>'done']);
        }

        return response()->json(['error'=>$validator->errors()->all()]);
    }

    /**
     * Display the specified resource.
     *
     * @param Kpi $kpi
     * @return Response
     */
    public function show(Kpi $kpi)
    {
        $title = trans('kpi.details');
        $action = 'show';

        return view('layouts.show', compact('kpi', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Kpi $kpi
     * @return Response
     */
    public function edit(Kpi $kpi)
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

        $kras = $this->kraRepository
            ->getAllForSchoolYearSchoolKpi(session('current_company'), session('current_company_year'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend('Select KRA', 0)
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

        return view('layouts.edit', compact('title', 'kpi', 'kras', 'employees', 'kpitimelines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiRequest $request
     * @param Level $level
     * @return Response
     */
    public function update(KpiRequest $request, Kpi $kpi)
    {
        try {
            $kpi->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($kpi->save()) {
            return response('<div class="alert alert-success">KPI CREATED Successfully</div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    public function delete(Kpi $kpi)
    {
        $title = trans('kpi.delete');

        return view('kpi.delete', compact('kpi', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Kpi $kpi
     * @return Response
     */
    public function destroy(Kpi $kpi)
    {
        $kpi->delete();

        return redirect('/kpi');
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
        return new BscPerspectives(BscPerspective::all());
    }
}
