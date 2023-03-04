<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LegalCaseCategoryRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Http\Requests\Secure\PublicationCategoryRequest;
use App\Models\LegalCaseCategory;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\PublicationCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class PublicationCategoryController extends SecureController
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

        view()->share('type', 'publication_category');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Publication Categories';
        $publicationCategories = PublicationCategory::get();
        return view('publication_category.index', compact('title', 'publicationCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'Create New Publication Category';

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(PublicationCategoryRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                            $publicationCategory = PublicationCategory::firstOrCreate
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
     * @param PublicationCategory $publicationCategory
     * @return Response
     */
    public function show(PublicationCategory $publicationCategory)
    {
        $title = 'Publication Category';
        $action = 'show';

        return view('layouts.show', compact('publicationCategory', 'title', 'action'));
    }


    public function publications(PublicationCategory $publicationCategory)
    {
        $title = $publicationCategory->title;
        $publications = $publicationCategory->publications;

        return view('publication_category.publications', compact('publicationCategory', 'title', 'publications'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param PublicationCategory $publicationCategory
     * @return Response
     */
    public function edit(PublicationCategory $publicationCategory)
    {
        $title = 'Edit Publication Category';

        return view('layouts.edit', compact('title', 'publicationCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PublicationCategoryRequest $request
     * @param PublicationCategory $publicationCategory
     * @return Response
     */
    public function update(PublicationCategoryRequest $request, PublicationCategory $publicationCategory)
    {
        $publicationCategory->update($request->all());

        return 'Category updated';
    }

    public function delete(PublicationCategory $publicationCategory)
    {
        if ($publicationCategory->publications->count() > 0)
            return response()->json(['exception'=>'Category has Publications associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($publicationCategory) {
                $publicationCategory->delete();
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
    public function destroy(PublicationCategory $publicationCategory)
    {
        $publicationCategory->delete();
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
