<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\KraRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PayrollComponentRequest;
use App\Models\BscPerspective;
use App\Models\Holiday;
use App\Models\Kpi;
use App\Models\Kra;
use App\Models\Level;
use App\Models\PayrollComponent;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KraRepository;
use App\Repositories\SectionRepository;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class PayrollComponentController extends SecureController
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

        view()->share('type', 'payroll_component');
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
        $title = 'Payroll Components';
        $payrollComponents = PayrollComponent::where('company_id', session('current_company'))
            ->get();

        return view('payroll_component.index', compact('title', 'payrollComponents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Payroll Component';

        return view('payroll_component.modalForm', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KraRequest $request
     * @return Response
     */
    public function store(PayrollComponentRequest $request)
    {
        try {
            $kra = new PayrollComponent();
            $kra->title = $request->title;
            $kra->code = $request->code;
            $kra->company_id = session('current_company');
            $kra->description = $request->description;
            $kra->balance_type = $request->balance_type;
            $kra->transaction_type = $request->transaction_type;
            $kra->frequency = $request->frequency;
            $kra->calculate_from_basic_salary = $request->calculate_from_basic_salary;
            $kra->basic_salary_percentage = $request->basic_salary_percentage;
            $kra->employee_fixed_amount = $request->employee_fixed_amount;
            $kra->taxable = $request->taxable;
            $kra->tax_percentage = $request->tax_percentage;
            $kra->loan = $request->loan;
            $kra->save();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Payroll Component Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param Kra $kra
     * @return Response
     */
    public function show(PayrollComponent $payrollComponent)
    {
        $title = 'Payroll Component Details';
        $action = 'show';

        return view('layouts.show', compact('payrollComponent', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Kra $kra
     * @return Response
     */
    public function edit(PayrollComponent $payrollComponent)
    {
        $title = trans('kra.edit');

        $bscPerspectives = BscPerspective::get()
            ->pluck('title', 'id')
            ->prepend('Select Department', '')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        return view('payroll_component.modalForm', compact('title', 'payrollComponent', 'sections', 'bscPerspectives'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KraRequest $request
     * @param Kra $kra
     * @return Response
     */
    public function update(PayrollComponentRequest $request, PayrollComponent $payrollComponent)
    {
        try {
            $payrollComponent->update($request->all());
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Component Updated Successful!!!');
    }

    public function delete(PayrollComponent $payrollComponent)
    {
        try
        {
            DB::transaction(function() use ($payrollComponent) {
                $payrollComponent->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Component Deleted Successfully') ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Kra $kra
     * @return Response
     */
    public function destroy(PayrollComponent $payrollComponent)
    {

        try
        {
            DB::transaction(function() use ($payrollComponent) {
                $payrollComponent->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Component Deleted Successfully') ;
    }
}
