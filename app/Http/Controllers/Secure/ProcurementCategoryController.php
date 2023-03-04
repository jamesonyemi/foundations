<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class ProcurementCategoryController extends SecureController
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

        view()->share('type', 'procurementCategory');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.categories');
        $procurementCategories = ProcurementCategory::get();
        return view('procurementCategory.index', compact('title', 'procurementCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('procurement.new_category');
        $procurementItemCategories = ProcurementCategory::get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.categories'), 0)
            ->toArray();
        $procurementMasterCategories = ProcurementMasterCategory::get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.master_categories'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'procurementItemCategories', 'procurementMasterCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ProcurementCategoryRequest $request)
    {


        try
        {
            DB::transaction(function() use ($request) {

                            $procurementCategory = ProcurementCategory::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'title' => $request['title'],
                                    'category_order' => $request['category_order'],
                                    'procurement_master_category_id' => $request['procurement_master_category_id'],
                                    'parent_id' => $request['parent_id'],
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
    public function show(ProcurementCategory $procurementCategory)
    {
        $title = trans('procurement.category');

        $action = 'show';

        return view('layouts.show', compact('procurementCategory', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function edit(ProcurementCategory $procurementCategory)
    {
        $title = trans('procurement.edit_category');

        $procurementItemCategories = ProcurementCategory::where('procurement_master_category_id', $procurementCategory->procurement_master_category_id)->get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.categories'), 0)
            ->toArray();
        $procurementMasterCategories = ProcurementMasterCategory::get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.master_categories'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'procurementCategory', 'procurementMasterCategories', 'procurementItemCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(ProcurementCategoryRequest $request, ProcurementCategory $procurementCategory)
    {
        $procurementCategory->update($request->all());

        return 'Category updated';
    }

    public function delete(ProcurementCategory $procurementCategory)
    {
        if ($procurementCategory->items->count() > 0)
            return response()->json(['exception'=>'Category has items associations and cannot be deleted']);

        if ($procurementCategory->children->count() > 0)
            return response()->json(['exception'=>'Category has Sub Categories and cannot be deleted']);

        if ($procurementCategory->suppliers->count() > 0)
            return response()->json(['exception'=>'Category has Supplier associations and cannot be deleted']);
        try
        {
            DB::transaction(function() use ($procurementCategory) {
                $procurementCategory->delete();
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
    public function destroy(ProcurementCategory $procurementCategory)
    {
        $procurementCategory->delete();
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
