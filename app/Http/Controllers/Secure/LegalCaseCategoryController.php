<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LegalCaseCategoryRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\LegalCaseCategory;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class LegalCaseCategoryController extends SecureController
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

        view()->share('type', 'legalCaseCategory');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.categories');
        $legalCaseCategories = LegalCaseCategory::get();
        return view('legalCaseCategory.index', compact('title', 'legalCaseCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('procurement.new_category');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(LegalCaseCategoryRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                            $legalCaseCategory = LegalCaseCategory::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'company_id' => session('current_company'),
                                    'title' => $request['title'],
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
    public function show(LegalCaseCategory $legalCaseCategory)
    {
        $title = trans('procurement.category');

        $action = 'show';

        return view('layouts.show', compact('legalCaseCategory', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function edit(LegalCaseCategory $legalCaseCategory)
    {
        $title = trans('procurement.edit_category');


        return view('layouts.edit', compact('title', 'legalCaseCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(LegalCaseCategoryRequest $request, LegalCaseCategory $legalCaseCategory)
    {
        $legalCaseCategory->update($request->all());

        return 'Category updated';
    }

    public function delete(LegalCaseCategory $legalCaseCategory)
    {
        if ($legalCaseCategory->legalCases->count() > 0)
            return response()->json(['exception'=>'Category has Cases associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($legalCaseCategory) {
                $legalCaseCategory->delete();
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
    public function destroy(LegalCaseCategory $legalCaseCategory)
    {
        $legalCaseCategory->delete();
        return 'Category Deleted';


    }




    public function findProcurementCategory(Request $request)
    {
        $categories = ProcurementCategory::where('procurement_master_category_id', $request->procurement_master_category_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();
        return $categories;
    }


}
