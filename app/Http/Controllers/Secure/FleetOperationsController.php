<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FleetCategoryRequest;
use App\Http\Requests\Secure\FleetOperationRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\Fleet;
use App\Models\FleetCategory;
use App\Models\FleetOperation;
use App\Models\FleetType;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\EmployeeRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class FleetOperationsController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;


    /**
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {
        /*$this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);*/
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'fleetOperation');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('fleet.Fleets');
        $fleetOperations = FleetOperation::whereHas('fleet',  function ($q)   {
            $q->where('fleet.company_id', session('current_company'));
        })->get();
        return view('fleetOperation.index', compact('title', 'fleetOperations'));
    }

    public function indexAll()
    {
        $title = trans('fleet.Fleets');
        $fleetOperations = FleetOperation::get();
        return view('fleetOperation.index', compact('title', 'fleetOperations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('fleet.new_operation_record');
        $fleets = Fleet::where('company_id', session('current_company'))->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->fleet_number,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Fleet', '')
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Driver', 0)
            ->toArray();

        return view('layouts.create', compact('title', 'fleets', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(FleetOperationRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                            $procurementCategory = FleetOperation::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'company_year_id' => session('current_company_year'),
                                    'fleet_id' => $request['fleet_id'],
                                    'driver_employee_id' => $request['driver_employee_id'],
                                    'date' => now(),
                                    'fleet_status' => $request['fleet_status'],
                                    'odometer_reading' => $request['odometer_reading'],
                                    'fuel_reading' => $request['fuel_reading'],
                                    'description' => $request['description'],
                                ]
                            );

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Operation Record Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function show(FleetOperation $fleetOperation)
    {
        $title = 'Fleet Operation Details';

        $action = 'show';

        return view('layouts.show', compact('fleetOperation', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param FleetOperation $fleetOperation
     * @return Response
     */
    public function edit(FleetOperation $fleetOperation)
    {
        $title = trans('procurement.edit_category');

        $fleets = Fleet::where('company_id', session('current_company'))->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->fleet_number,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Fleet', '')
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Driver', 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'fleetOperation',  'fleets', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(FleetOperationRequest $request, FleetOperation $fleetOperation)
    {
        $fleetOperation->update($request->all());

        return 'Operation Record Updated';
    }

    public function delete(FleetOperation $fleetOperation)
    {
        /*if ($fleetCategory->fleets->count() > 0)
            return response()->json(['exception'=>'Category has items associations and cannot be deleted']);*/

        /*if ($fleetCategory->children->count() > 0)
            return response()->json(['exception'=>'Category has Sub Categories and cannot be deleted']);*/

        try
        {
            DB::transaction(function() use ($fleetOperation) {
                $fleetOperation->delete();
            });
        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Operation Record Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(FleetOperation $fleetOperation)
    {
        $fleetOperation->delete();
        return 'Operation Record Deleted';


    }




    public function findProcurementCategory(Request $request)
    {
        $categories = ProcurementCategory::where('procurement_master_category_id', $request->procurement_master_category_id)->where('category_code', '!=', '')->get()
        ->map(function ($item) {
            return [
                "id"   => $item->id,
                "title" =>$item->title. ' | ' .$item->category_code,
            ];
        })->pluck("title", 'id')
            ->prepend(trans('procurement.categories'), '')
            ->toArray();
        return $categories;
    }


}
