<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KraRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PayeSetupRequest;
use App\Http\Requests\Secure\PayrollComponentRequest;
use App\Http\Requests\Secure\TaxLawRequest;
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

class TaxLawController extends SecureController
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

        view()->share('type', 'tax_law');
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
        $title = 'Payroll Tax Laws';
        $taxLaws = PrTaxLaw::get();

        return view('tax_law.index', compact('title', 'taxLaws'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Payroll Component';


        return view('paye_setup.modalForm', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KraRequest $request
     * @return Response
     */
    public function store(TaxLawRequest $request)
    {
        try {
            $taxLaw = new PrTaxLaw();
            $taxLaw->company_id = session('current_company');
            $taxLaw->title = $request->title;
            $taxLaw->code = $request->code;
            $taxLaw->period_type = $request->period_type;
            $taxLaw->description = $request->description;
            $taxLaw->block = $request->block;
            $taxLaw->max_basic_overtime = $request->max_basic_overtime;
            $taxLaw->monthly_basic_limit_overtime = $request->monthly_basic_limit_overtime;
            $taxLaw->non_graduating_overtime_tax = $request->non_graduating_overtime_tax;
            $taxLaw->annual_bonus_limit_percentage = $request->annual_bonus_limit_percentage;
            $taxLaw->annual_bonus_limit_amount = $request->annual_bonus_limit_amount;
            $taxLaw->non_resident_tax_rate_percentage = $request->non_resident_tax_rate_percentage;
            $taxLaw->save();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Tax Law Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param PayeSetUp $paye_setup
     * @return Response
     */
    public function show(PrTaxLaw $tax_law)
    {
        $title = 'Tax Law Details';
        $action = 'show';

        return view('tax_law._details', compact('tax_law', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Kra $kra
     * @return Response
     */
    public function edit(PrTaxLaw $tax_law)
    {
        $title = 'Edit Tax Laws';

        return view('tax_law.modalForm', compact('title', 'tax_law'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(TaxLawRequest $request, PrTaxLaw $tax_law)
    {
        try {
            $tax_law->update($request->all());
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Tax Law Updated Successful!!!');
    }

    public function delete(PrTaxLaw $tax_law)
    {
        try
        {
            DB::transaction(function() use ($tax_law) {
                $tax_law->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Tax Law Deleted Successfully') ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(PrTaxLaw $tax_law)
    {

        try
        {
            DB::transaction(function() use ($tax_law) {
                $tax_law->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Tax Law Deleted Successfully') ;
    }
}
