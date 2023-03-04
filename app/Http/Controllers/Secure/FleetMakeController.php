<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FleetCategoryRequest;
use App\Http\Requests\Secure\FleetMakeRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\FleetCategory;
use App\Models\FleetMake;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class FleetMakeController extends SecureController
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

        view()->share('type', 'fleetMake');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('fleet.Fleets');
        $fleetCategories = FleetCategory::get();
        return view('fleetCategory.index', compact('title', 'fleetCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('fleet.new_category');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(FleetMakeRequest $request)
    {

        try
        {
            DB::transaction(function() use ($request) {

                            $procurementCategory = FleetMake::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'company_id' => session('current_company'),
                                    'title' => $request['title'],
                                    'category_code' => $request['category_code'],
                                    'description' => $request['description'],
                                ]
                            );

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Category Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function show(FleetCategory $fleetCategory)
    {
        $title = trans('procurement.category');

        $action = 'show';

        return view('layouts.show', compact('fleetCategory', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function edit(FleetCategory $fleetCategory)
    {
        $title = trans('procurement.edit_category');

        $fleetCategories = FleetCategory::pluck('title', 'id')
            ->prepend(trans('fleet.categories'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'fleetCategory',  'fleetCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(FleetCategoryRequest $request, FleetCategory $fleetCategory)
    {
        $fleetCategory->update($request->all());

        return 'Category updated';
    }

    public function delete(FleetCategory $fleetCategory)
    {
        if ($fleetCategory->fleets->count() > 0)
            return response()->json(['exception'=>'Category has items associations and cannot be deleted']);

        if ($fleetCategory->children->count() > 0)
            return response()->json(['exception'=>'Category has Sub Categories and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($fleetCategory) {
                $fleetCategory->delete();
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }
        return response('Category Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(FleetCategory $fleetCategory)
    {
        $fleetCategory->delete();
        return 'Position Deleted';


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
