<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Http\Requests\Secure\ProjectCategoryRequest;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\ProjectCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class ProjectCategoryController extends SecureController
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

        view()->share('type', 'projectCategory');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.categories');
        $projectCategories = ProjectCategory::get();
        return view('projectCategory.index', compact('title', 'projectCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Project Category';

        return view('projectCategory._form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ProjectCategoryRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                            $projectCategory = ProjectCategory::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
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
     * @param ProjectCategory $projectCategory
     * @return Response
     */
    public function show(ProjectCategory $projectCategory)
    {
        $title = trans('procurement.category');

        $action = 'show';

        return view('layouts.show', compact('projectCategory', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function edit(ProjectCategory $projectCategory)
    {
        $title = 'Edit Project Category';

        return view('projectCategory._form', compact('title', 'projectCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory)
    {
        $projectCategory->update($request->all());

        return 'Category updated';
    }

    public function delete(ProjectCategory $projectCategory)
    {
        if ($projectCategory->projects->count() > 0)
            return response()->json(['exception'=>'Category has items associations and cannot be deleted']);



        if ($projectCategory->suppliers->count() > 0)
            return response()->json(['exception'=>'Category has Supplier associations and cannot be deleted']);
        try
        {
            DB::transaction(function() use ($projectCategory) {
                $projectCategory->delete();
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }
        return response('Category Deleted Successfully') ;
    }

}
