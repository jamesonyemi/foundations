<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FleetCategoryRequest;
use App\Http\Requests\Secure\FleetRequest;
use App\Http\Requests\Secure\FleetTypeRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\Fleet;
use App\Models\FleetCategory;
use App\Models\FleetMake;
use App\Models\FleetType;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class FleetController extends SecureController
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
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {
        /*$this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);*/
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'fleet');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('fleet.Fleets');
        $fleets = Fleet::where('company_id', session('current_company'))->get();
        return view('fleet.index', compact('title', 'fleets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('fleet.new');
        $fleetTypes = FleetType::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Fleet Type', '')
            ->toArray();

        $fleetMakes = FleetMake::get()
            ->map(function ($item) {
            return [
                "id"   => $item->id,
                "name" => $item->title,
            ];
        })->pluck("name", 'id')
            ->prepend('Select Fleet Make', '')
            ->toArray();

        $fleetCategories = FleetCategory::get()
            ->map(function ($item) {
            return [
                "id"   => $item->id,
                "name" => $item->title,
            ];
        })->pluck("name", 'id')
            ->prepend('Select Category', '')
            ->toArray();;
        return view('layouts.create', compact('title', 'fleetTypes', 'fleetMakes', 'fleetCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(FleetRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                            $fleet = Fleet::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'company_id' => session('current_company'),
                                    'fleet_make_id' => $request['fleet_make_id'],
                                    'fleet_type_id' => $request['fleet_type_id'],
                                    'fleet_category_id' => $request['fleet_category_id'],
                                    'fleet_number' => $request['fleet_number'],
                                    'chassis_number' => $request['chassis_number'],
                                    'fleet_model' => $request['fleet_model'],
                                    'description' => $request['description'],
                                    'date_of_purchase' => $request['date_of_purchase'],
                                    'district_id' => $request['district_id'],
                                    'location' => $request['location'],
                                ]
                            );

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Fleet Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param FleetType $fleetType
     * @return Response
     */
    public function show(Fleet $fleet)
    {
        $title = 'Fleet';

        $action = 'show';

        return view('layouts.show', compact('fleet', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param Fleet $fleet
     * @return Response
     */
    public function edit(Fleet $fleet)
    {
        $title = trans('procurement.edit_category');

        $fleetTypes = FleetType::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Fleet Type', '')
            ->toArray();

        $fleetMakes = FleetType::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Fleet Make', '')
            ->toArray();

        $fleetCategories = FleetType::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Category', '')
            ->toArray();;

        return view('layouts.edit', compact('title', 'fleet',  'fleetCategories', 'fleetMakes', 'fleetTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(FleetRequest $request, Fleet $fleet)
    {
        $fleet->update($request->all());

        return 'Fleet updated';
    }

    public function delete(Fleet $fleet)
    {

        try
        {
            DB::transaction(function() use ($fleet) {
                $fleet->delete();
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }
        return response('Fleet Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Fleet $fleet
     * @return Response
     */
    public function destroy(Fleet $fleet)
    {
        $fleet->delete();
        return 'Fleet Deleted';


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
