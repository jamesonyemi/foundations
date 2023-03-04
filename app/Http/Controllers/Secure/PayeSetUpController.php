<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KraRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PayeSetupRequest;
use App\Http\Requests\Secure\PayrollComponentRequest;
use App\Models\BscPerspective;
use App\Models\Holiday;
use App\Models\Kpi;
use App\Models\Kra;
use App\Models\Level;
use App\Models\PayeSetUp;
use App\Models\PayrollComponent;
use App\Models\PrTaxLaw;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KraRepository;
use App\Repositories\SectionRepository;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class PayeSetUpController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var KraRepository
     */
    private $kraRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * DirectionController constructor.
     *
     * @param KraRepository $kraRepository
     * @param SectionRepository $sectionRepository
     * @param EmployeeRepository $employeeRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        KraRepository $kraRepository,
        EmployeeRepository $employeeRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();

        $this->kraRepository = $kraRepository;
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'paye_setup');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        /*if (!Sentinel::hasAccess('key_result_areas')) {
            Flash::warning("Permission Denied");
            return view('flash-message');
        }*/
        $title = 'Paye Set Up';
        $payeSetups = PayeSetUp::get();

        return view('paye_setup.index', compact('title', 'payeSetups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Paye Set Up';

        $taxLaws = PrTaxLaw::get()
            ->pluck('title', 'id')
            ->prepend('Select Tax Law', '')
            ->toArray();

        return view('paye_setup.modalForm', compact('title', 'taxLaws'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KraRequest $request
     * @return Response
     */
    public function store(PayeSetupRequest $request)
    {
        try {
            $payeSetup = new PayeSetUp();
            $payeSetup->pr_tax_law_id = $request->pr_tax_law_id;
            $payeSetup->paye_tier = $request->paye_tier;
            $payeSetup->rate = $request->rate;
            $payeSetup->description = $request->description;
            $payeSetup->company_id = session('current_company');
            $payeSetup->save();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Paye Setup Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param PayeSetUp $paye_setup
     * @return Response
     */
    public function show(PayeSetUp $paye_setup)
    {
        $title = 'Paye Setup Details';
        $action = 'show';

        return view('paye_setup.modalForm', compact('paye_setup', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Kra $kra
     * @return Response
     */
    public function edit(PayeSetUp $paye_setup)
    {
        $title = 'Edit Paye Set Up';

        $taxLaws = PrTaxLaw::get()
            ->pluck('title', 'id')
            ->prepend('Select Tax Law', '')
            ->toArray();

        return view('paye_setup.modalForm', compact('title', 'paye_setup', 'taxLaws'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(PayeSetupRequest $request, PayeSetUp $paye_setup)
    {
        try {
            $paye_setup->update($request->all());
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Component Updated Successful!!!');
    }

    public function delete(PayeSetUp $paye_setup)
    {
        try
        {
            DB::transaction(function() use ($paye_setup) {
                $paye_setup->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Paye Setup Deleted Successfully') ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Kra $kra
     * @return Response
     */
    public function destroy(PayeSetUp $paye_setup)
    {

        try
        {
            DB::transaction(function() use ($paye_setup) {
                $paye_setup->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Paye Tier Deleted Successfully') ;
    }
}
