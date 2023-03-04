<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Http\Requests\Secure\ProjectArtisanRequest;
use App\Http\Requests\Secure\ProjectCategoryRequest;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\ProjectArtisan;
use App\Models\ProjectCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class ProjectArtisanController extends SecureController
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
        $this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'projectArtisan');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Project Artisans';
        $projectArtisans = ProjectArtisan::get();
        return view('projectArtisan.index', compact('title', 'projectArtisans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Project Artisan';

        return view('projectArtisan._form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|ProjectArtisanRequest $request
     * @return Response
     */
    public function store(ProjectArtisanRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {

                            $projectArtisan = ProjectArtisan::firstOrCreate
                            (
                                [
                                    'title' => $request['title'],
                                    'artisan_code' => $request['artisan_code'],
                                    'type' => $request['type'],
                                    'description' => $request['description'],
                                    'status' => $request['status'],
                                ]
                            );

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Artisan Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param ProjectArtisan $projectArtisan
     * @return Response
     */
    public function show(ProjectArtisan $projectArtisan)
    {
        $title = $projectArtisan->title;

        $action = 'show';

        return view('layouts.show', compact('projectArtisan', 'title', 'action'));
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param ProjectArtisan $projectArtisan
     * @return Response
     */
    public function edit(ProjectArtisan $projectArtisan)
    {
        $title = trans('procurement.edit_category');

        return view('projectArtisan._form', compact('title', 'projectArtisan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(ProjectCategoryRequest $request, ProjectArtisan $projectArtisan)
    {
        $projectArtisan->update($request->all());

        return 'Artisan Info updated';
    }

    public function delete(ProjectArtisan $projectArtisan)
    {
        if ($projectArtisan->projects->count() > 0)
            return response()->json(['exception'=>'Artisan has project associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($projectArtisan) {
                $projectArtisan->delete();
            });
        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Artisan Deleted Successfully') ;
    }

}
