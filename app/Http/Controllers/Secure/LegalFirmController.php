<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\LegalFirmRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\LegalFirm;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;

class LegalFirmController extends SecureController
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

        view()->share('type', 'legalFirm');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.categories');
        $legalFirms = LegalFirm::get();
        return view('legalFirm.index', compact('title', 'legalFirms'));
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
     * @param Request|LegalFirmRequest $request
     * @return Response
     */
    public function store(LegalFirmRequest $request)
    {


        try
        {
            DB::transaction(function() use ($request) {

                $legalFirm = LegalFirm::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'title' => $request['title'],
                                    'description' => $request['description'],
                                ]
                            );

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Legal Firm Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param LegalFirm $legalFirm
     * @return Response
     */
    public function show(LegalFirm $legalFirm)
    {
        $title = trans('procurement.category');

        $action = 'show';

        return view('layouts.show', compact('legalFirm', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param LegalFirm $legalFirm
     * @return Response
     */
    public function edit(LegalFirm $legalFirm)
    {
        $title = trans('procurement.edit_category');

        return view('layouts.edit', compact('title', 'legalFirm'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param LegalFirm $legalFirm
     * @return Response
     */
    public function update(LegalFirmRequest $request, LegalFirm $legalFirm)
    {
        $legalFirm->update($request->all());

        return 'Legal Firm updated Successfully';
    }

    public function delete(LegalFirm $legalFirm)
    {
        if ($legalFirm->legalCases->count() > 0)
            return response()->json(['exception'=>'Category has Legal Cases associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($legalFirm) {
                $legalFirm->delete();
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }
        return response('Legal Firm Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(LegalFirm $legalFirm)
    {
        $legalFirm->delete();
        return 'Legal Firm Deleted';


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
