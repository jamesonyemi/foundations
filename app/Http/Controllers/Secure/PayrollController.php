<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\PayrollRequest;
use App\Http\Requests\Secure\PayrollSetupRequest;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeePostingGroup;
use App\Models\Kpi;
use App\Models\PayrollComponent;
use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodTransaction;
use App\Models\PayrollPeriodTransactionComponent;
use App\Models\PayrollSetup;
use App\Models\Position;
use App\Models\PrTaxLaw;
use App\Models\ScoreCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\AccountRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\EmployeePostingGroupRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;

class PayrollController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var EmployeePostingGroupRepository
     */

    private $employeePostingGroupRepository;


    protected $activity;
    protected $module = 'Payroll Period';


    /**
     * BehaviorController constructor.
     * @param
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        AccountRepository $accountRepository,
        EmployeePostingGroupRepository $employeePostingGroupRepository,
        ActivityLogRepository $activity
    ) {

        $this->middleware('authorized:account.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:account.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:account.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:account.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->accountRepository = $accountRepository;
        $this->employeePostingGroupRepository = $employeePostingGroupRepository;
        $this->activity = $activity;

        view()->share('type', 'payroll');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Payroll';

        $payrollPeriods = PayrollPeriod::where('company_id', session('current_company'))
            ->where('company_year_id', session('current_company_year'))
            ->get();

        return view('payroll.index', compact('title', 'payrollPeriods'));
    }


    public function PayRollFilter()
    {

        $payrollPeriods = PayrollPeriod::where('company_id', session('current_company'))
            ->where('company_year_id', session('current_company_year'))
            ->get();

        return view('payroll.payroll_filter', compact('payrollPeriods'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function payrollPeriod()
    {
        $title = trans('employee_posting_group.new');

        $payrollPeriods = PayrollPeriod::where('company_id', session('current_company'))
            ->where('company_year_id', session('current_company_year'))
            ->get();


        return view('payroll.payroll_period', compact('title', 'payrollPeriods'));
    }




    public function payrollSetup(Company $company)
    {
        $title = 'Payroll Setup';

        $payrollSetup = PayrollSetup::where('company_id', $company->id)->first();

        return view('payroll.payroll_setup', compact('title', 'company', 'payrollSetup'));
    }



    public function updatePayrollSetup(PayrollSetupRequest $request, PayrollSetup $payrollSetup)
    {
        try
        {
            DB::transaction(function() use ($request, $payrollSetup) {
                $payrollSetup->update($request->except('responsible_employee_id', 'supervisor_employee_id'));;

            });
        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">PAYROLL SETUP UPDATED SUCCESSFULLY</div>') ;

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param PayrollRequest $request
     * @return Response
     */
    public function processPayroll(PayrollRequest $request)
    {
        //Check if payroll already exists
        $Payroll = PayrollPeriod::where('company_year_id', session('current_company_year'))
            ->where('company_id', session('current_company'))
            ->where('period_month', $request->month)->where('period_year', $request->year)->first();
        if ($Payroll)
        {
            return response()->json(['exception'=>'Payroll Period Already Exists']);
        }


        try
        {
            DB::transaction(function() use ($request) {

                //Get payroll setup information
                $payrollSetup = PayrollSetup::first();

                //Close previous payroll period
                if (isset($payrollSetup->status))
                {
                    $previousPayrollPeriod = PayrollPeriod::where('company_id',  session('current_company'))->latest('id')->first();
                    $previousPayrollPeriod->status = 1;
                    $previousPayrollPeriod->save();
                }
                //Create new payroll period
                $dateObj   = \DateTime::createFromFormat('!m', $request->month);
                $monthName = $dateObj->format('F'); // March
                $payrollPeriod = New PayrollPeriod();
                $payrollPeriod->company_id = session('current_company');
                $payrollPeriod->company_year_id = session('current_company_year');
                $payrollPeriod->employee_id = session('current_employee');
                $payrollPeriod->title = $monthName;
                $payrollPeriod->period_month = $request->month;
                $payrollPeriod->period_year = $request->year;
                $payrollPeriod->save();


                //Get all the payroll employees
                $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
                    ->get();

                foreach ($employees as $employee)
                {
                    $payrollPeriodTransaction = new PayrollPeriodTransaction();
                    $payrollPeriodTransaction->employee_id = $employee->id;
                    $payrollPeriodTransaction->employee_name = $employee->user->full_name;
                    $payrollPeriodTransaction->department_id = $employee->department_id ?? '';
                    $payrollPeriodTransaction->department_name = $employee->department->title ?? '';
                    $payrollPeriodTransaction->position_id = $employee->position_id ?? '';
                    $payrollPeriodTransaction->position_name = $employee->position->title ?? '';
                    $payrollPeriodTransaction->bank_id = $employee->bank_id;
                    $payrollPeriodTransaction->bank_account_number = $employee->bank_account_number;
                    $payrollPeriodTransaction->company_year_id = session('current_company_year');
                    $payrollPeriodTransaction->transaction_code = 'BPAY';
                    $payrollPeriodTransaction->transaction_name = 'Basic Pay';
                    $payrollPeriodTransaction->group_text = 'BPAY';
                    $payrollPeriodTransaction->basic_pay = $employee->basic_pay;
                    $payrollPeriodTransaction->amount = $employee->basic_pay;
                    $payrollPeriodTransaction->payment_mode = $employee->payment_mode;
                    $payrollPeriodTransaction->mobile_money_network = $employee->mobileMoneyNetwork->title ?? '';
                    $payrollPeriodTransaction->mobile_money_number = $employee->mobile_money_number ?? '';
                    $payrollPeriodTransaction->period_month = $request->month;
                    $payrollPeriodTransaction->period_year = $request->year;
                    $payrollPeriodTransaction->payroll_period_id = $payrollPeriod->id;
                    $payrollPeriodTransaction->salary_grade = @$employee->salary_notch->salary_grade->title ?? '';
                    $payrollPeriodTransaction->salary_notch = @$employee->salary_notch->title ?? '';
                    $payrollPeriodTransaction->currency = $employee->currency->title ?? '';

                    //PAYS PF
                    if ($employee->pays_pf == 'Yes')
                    {
                        $payrollPeriodTransaction->social_security_number = $employee->social_security_number;
                        $payrollPeriodTransaction->employee_pf_amount = ($payrollSetup->pf_employee_percentage / 100) * $employee->basic_pay;
                        $payrollPeriodTransaction->employer_pf_amount = ($payrollSetup->pf_employer_percentage / 100) * $employee->basic_pay;
                    }

                    //PAYS SSF
                    if ($employee->pays_ssf == 'Yes')
                    {
                        $payrollPeriodTransaction->social_security_number = $employee->social_security_number;

                        //PAYS NEW SSF
                        if ($employee->social_security_scheme == 'New')
                        {
                            $payrollPeriodTransaction->employee_ssf_amount = ($payrollSetup->new_ssf_employee_percentage / 100) * $employee->basic_pay;
                            $payrollPeriodTransaction->employer_ssf_amount = ($payrollSetup->new_ssf_employer_percentage / 100) * $employee->basic_pay;
                        }

                        //PAYS OLD SSF
                        if ($employee->social_security_scheme == 'Old')
                        {
                            $payrollPeriodTransaction->employee_ssf_amount = ($payrollSetup->old_ssf_employee_percentage / 100) * $employee->basic_pay;
                            $payrollPeriodTransaction->employer_ssf_amount = ($payrollSetup->old_ssf_employer_percentage / 100) * $employee->basic_pay;
                        }
                    }
                    $payrollPeriodTransaction->save() ;



                    //Loop through other payroll components and append
                    foreach ($employee->payrollComponents as $component)
                    {

                        $payrollPeriodTransactionComponent = new PayrollPeriodTransactionComponent();
                        $payrollPeriodTransactionComponent->payroll_period_transaction_id = $payrollPeriodTransaction->id;
                        $payrollPeriodTransactionComponent->transaction_code = $component->payroll_component->code;
                        $payrollPeriodTransactionComponent->transaction_type = $component->payroll_component->transaction_type;
                        $payrollPeriodTransactionComponent->transaction_name = $component->payroll_component->title;
                        $payrollPeriodTransactionComponent->group_text = $component->payroll_component->description;

                        //IF COMPONENT AMOUNT OF CALCULATED ON BASIC SALARY
                        if ($component->payroll_component->employee_fixed_amount == 'Yes')
                        {
                            $payrollPeriodTransactionComponent->amount = $component->amount;
                        }

                        //IF COMPONENT AMOUNT OF CALCULATED ON BASIC SALARY
                        if ($component->payroll_component->calculate_from_basic_salary == 'Yes')
                        {
                            $payrollPeriodTransactionComponent->amount = ($component->payroll_component->basic_salary_percentage / 100) * $employee->basic_pay;
                        }
                        //IF COMPONENT AMOUNT IS TAXABLE
                        if ($component->payroll_component->taxable == 'Yes')
                        {
                            $payrollPeriodTransactionComponent->tax_amount = ($component->payroll_component->tax_percentage / 100) * $payrollPeriodTransactionComponent->amount;
                        }
                        $payrollPeriodTransactionComponent->save() ;
                    }

                }

            });

        }


        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        /*$this->activity->record([
            'module'    => $this->module,
            'module_id' => 1,
            'activity'  => 'created'
        ]);*/


        return 'Payroll Processed Successfully';
    }

    /**
     * Display the specified resource.
     *
     * @param employeePostingGroup $employeePostingGroup
     * @return Response
     * @internal param int $id
     */




    public function payslipIndex()
    {
        $title = 'Pay Slip';

        return view('payroll.payslip_index', compact('title'));
    }



    public function monthlyPaySlip(Request $request)
    {
        $date = Carbon::create($request->date);
        $title = 'Pay Slip';

        $employee = $this->currentEmployee;

        return view('payroll.monthly', compact('title', 'request', 'employee'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function bankSummaryReport()
    {
        $title = 'Payroll Bank Summary';

        $banks = Bank::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();


        return view('payroll_report.bank_summary_report', compact('title', 'banks'));
    }


    public function bankSummaryFilter(Request $request)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Payroll Bank Summary for the Period: '.$monthName.' '.$request->year.'';
//Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();

        if($request->bank_id > 0) {
            $banks = Bank::where('id', $request->bank_id)->get();
        }

        else {
            $banks = Bank::whereHas('payrollPeriodTransactions', function ($query) use ($request) {
                $query->where('payroll_period_transactions.period_year', $request->year)
                    ->where('payroll_period_transactions.period_month', $request->month);
            })->get();
        }


        return view('payroll_report.bank_summary_report_filter', compact('title', 'banks', 'payrollSetup', 'request'));
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function bankDetailReport()
    {
        $title = 'Payroll Bank Summary';

        $banks = Bank::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();


        return view('payroll_report.bank_detail_report', compact('title', 'banks'));
    }


    public function bankDetailFilter(Request $request)
    {
//Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Payroll Bank Details for the Period: '.$monthName.' '.$request->year.'';


        if($request->bank_id > 0) {
            $banks = Bank::where('id', $request->bank_id)->get();
        }

        else {
            $banks = Bank::whereHas('payrollPeriodTransactions', function ($query) use ($request) {
                $query->where('payroll_period_transactions.period_year', $request->year)
                    ->where('payroll_period_transactions.period_month', $request->month);
            })->get();
        }


        return view('payroll_report.bank_detail_report_filter', compact('title', 'banks', 'payrollSetup', 'request'));
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function chequeDetailReport()
    {

        $title = 'Payroll Cheque Summary';
//Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();
        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.cheque_detail_report', compact('title', 'employees', 'payrollSetup'));
    }


    public function chequeDetailFilter(Request $request)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Payroll Cheque Details for the Period: '.$monthName.' '.$request->year.'';

        //Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();


        if($request->employee_id > 0) {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('period_year', $request->year)
                ->where('period_month', $request->month)
                ->where('payment_mode', 'Cheque')
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.id', $request->employee_id);
                })->get();
        }

        else {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('period_year', $request->year)
                ->where('period_month', $request->month)
                ->where('payment_mode', 'Cheque')
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.company_id', session('current_company'));
                })->get()->unique('employee_id');
        }


        return view('payroll_report.cheque_detail_report_filter', compact('title', 'payrollPeriodTransactions', 'payrollSetup', 'request'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function mobileMoneyDetailReport()
    {
        $title = 'Payroll Mobile Money Details';

        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.mobile_money_detail_report', compact('title', 'employees'));
    }


    public function mobileMoneyDetailFilter(Request $request)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Mobile Money Details for the Period: '.$monthName.' '.$request->year.'';

        //Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();


        if($request->employee_id > 0) {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('period_year', $request->year)
                ->where('period_month', $request->month)
                ->where('payment_mode', 'Mobile Money')
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.id', $request->employee_id);
                })->get();
        }

        else {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('period_year', $request->year)
                ->where('period_month', $request->month)
                ->where('payment_mode', 'Mobile Money')
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.company_id', session('current_company'));
                })->get()->unique('employee_id');
        }


        return view('payroll_report.mobile_money_detail_report_filter', compact('title', 'payrollPeriodTransactions', 'payrollSetup', 'request'));
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function tier1DetailReport()
    {
        $title = 'Pension Scheme Tier 1 Report for the Period';


        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.tier1_detail_report', compact('title', 'employees'));
    }


    public function tier1DetailFilter(Request $request)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Pension Scheme Tier 1 Report for the Period: '.$monthName.' '.$request->year.'';

        //Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();


        if($request->employee_id > 0) {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('payroll_period_transactions.period_year', $request->year)
                ->where('payroll_period_transactions.period_month', $request->month)
                /*->where('transaction_code', 'SSF')*/
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.id', $request->employee_id);
                })->get();
        }

        else {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('payroll_period_transactions.period_year', $request->year)
                ->where('payroll_period_transactions.period_month', $request->month)
                /*->where('transaction_code', 'SSF')*/
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.company_id', session('current_company'));
                })->get()->unique('employee_id');
        }


        return view('payroll_report.tier1_detail_report_filter', compact('title', 'payrollPeriodTransactions', 'payrollSetup', 'request'));
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function tier2DetailReport()
    {
        $title = 'Pension Scheme Tier 2 Report for the Period';

        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.tier2_detail_report', compact('title', 'employees'));
    }


    public function tier2DetailFilter(Request $request)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Pension Scheme Tier 2 Report for the Period: '.$monthName.' '.$request->year.'';

        //Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();


        if($request->employee_id > 0) {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('payroll_period_transactions.period_year', $request->year)
                ->where('payroll_period_transactions.period_month', $request->month)
                ->where('transaction_code', 'SSF')
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.id', $request->employee_id);
                })->get();
        }

        else {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('payroll_period_transactions.period_year', $request->year)
                ->where('payroll_period_transactions.period_month', $request->month)
                ->where('transaction_code', 'SSF')
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.company_id', session('current_company'));
                })->get()->unique('employee_id');
        }


        return view('payroll_report.tier2_detail_report_filter', compact('title', 'payrollPeriodTransactions', 'payrollSetup', 'request'));

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function allowanceReport()
    {
        $title = 'Payroll Allowance Report';

        $components = PayrollComponent::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Component', 0)
            ->toArray();


        return view('payroll_report.allowance_report', compact('title', 'components'));
    }


    public function allowanceReportFilter(Request $request)
    {

        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Payroll Allowance Report for the Period: '.$monthName.' '.$request->year.'';


        if($request->payroll_component_id > 0) {
            $components = PayrollComponent::where('id', $request->payroll_component_id)->get();
        }

        else {
            $components = PayrollComponent::whereHas('payrollPeriodTransactions', function ($query) use ($request) {
                $query->where('payroll_period_transactions.period_year', $request->year)
                    ->where('payroll_period_transactions.period_month', $request->month);
            })->get();
        }


        return view('payroll_report.allowance_report_filter', compact('title', 'components', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function paySlipReport()
    {
        $title = 'Payroll Pay Slip';

        /*$banks = Bank::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();*/

        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.payslip_report', compact('title','employees'));
    }


    public function paySlipReportFilter(Request $request)
    {
        //Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();

        $employee = Employee::find($request->employee_id);

        return view('payroll_report.payslip_report_filter', compact('employee', 'request', 'payrollSetup'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function payrollDetailReport()
    {
        $title = 'Payroll Details for the Period';

        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.payroll_detail_report', compact('title', 'employees'));
    }


    public function payrollDetailReportFilter(Request $request)
    {

        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Payroll Details for the Period: '.$monthName.' '.$request->year.'';



        if($request->employee_id > 0) {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('period_year', $request->year)
                ->where('period_month', $request->month)
                /*->where('transaction_code', 'BPAY')*/
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.id', $request->employee_id);
                })->get();
        }

        else {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('period_year', $request->year)
                ->where('period_month', $request->month)
                /*->where('transaction_code', 'BPAY')*/
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.company_id', session('current_company'));
                })->get()->unique('employee_id');
        }


        return view('payroll_report.payroll_detail_report_filter', compact('title', 'payrollPeriodTransactions', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function departmentPayrollDetailReport()
    {
        $title = 'Department Payroll Report';

        $departments = Department::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();


        return view('payroll_report.department_detail_report', compact('title', 'departments'));
    }


    public function departmentPayrollDetailReportFilter(Request $request)
    {

        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Department Payroll Report for the period: '.$monthName.' '.$request->year.'';


        if($request->department_id > 0) {
            $departments = Department::where('id', $request->department_id)->get();
        }

        else {
            $departments = Department::whereHas('payrollPeriodTransactions', function ($query) use ($request) {
                $query->where('payroll_period_transactions.period_year', $request->year)
                    ->where('payroll_period_transactions.period_month', $request->month);
            })->get();
        }


        return view('payroll_report.department_detail_report_filter', compact('title', 'departments', 'request'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function jobTitlePayrollDetailReport()
    {
        $title = 'Department Payroll Report';

        $positions = Position::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();


        return view('payroll_report.job_title_detail_report', compact('title', 'positions'));
    }


    public function jobTitlePayrollDetailReportFilter(Request $request)
    {
        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Job Title Payroll Report for the period: '.$monthName.' '.$request->year.'';


        if($request->position_id > 0) {
            $positions = Position::where('id', $request->position_id)->get();
        }

        else {
            $positions = Position::whereHas('payrollPeriodTransactions', function ($query) use ($request) {
                $query->where('payroll_period_transactions.period_year', $request->year)
                    ->where('payroll_period_transactions.period_month', $request->month);
            })->get();
        }


        return view('payroll_report.job_title_detail_report_filter', compact('title', 'positions', 'request'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function providentFundDetailReport()
    {
        $title = 'Payroll Provident Fund Report';

        $employees = $this->employeeRepository->getAllForPayroll(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();


        return view('payroll_report.provident_fund_detail_report', compact('title', 'employees'));
    }


    public function providentFundDetailReportFilter(Request $request)
    {

        $dateObj   = \DateTime::createFromFormat('!m', $request->month);
        $monthName = $dateObj->format('F'); // March
        $title = 'Provident Fund Report for the Period: '.$monthName.' '.$request->year.'';

        //Get payroll setup information
        $payrollSetup = PayrollSetup::where('company_id', session('current_company'))->first();


        if($request->employee_id > 0) {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('payroll_period_transactions.period_year', $request->year)
                ->where('payroll_period_transactions.period_month', $request->month)
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.id', $request->employee_id);
                })->get();
        }

        else {
            $payrollPeriodTransactions = PayrollPeriodTransaction::where('payroll_period_transactions.period_year', $request->year)
                ->where('payroll_period_transactions.period_month', $request->month)
                ->whereHas('employee', function ($query) use ($request) {
                    $query->where('employees.company_id', session('current_company'));
                })->get()->unique('employee_id');
        }

        return view('payroll_report.provident_fund_detail_report_filter', compact('title', 'payrollPeriodTransactions', 'payrollSetup', 'request'));
    }




    public function DeletePayrollPeriod(PayrollPeriod $payrollPeriod)
    {

        if ($payrollPeriod->status == 0)
        {
            $payrollPeriod->payrollPeriodTransactions()->delete();
            $payrollPeriod->delete();
        }

        $this->activity->record([
            'module'    => 'Payroll Period',
            'module_id' => $payrollPeriod->id,
            'activity'  => 'Deleted'
        ]);
        return 'Deleted Successfully';
    }

}
