<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\employeePostingGroupRequest;
use App\Models\EmployeePostingGroup;
use App\Models\ProcurementCategory;
use App\Models\PrTaxLaw;
use App\Repositories\AccountRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\EmployeePostingGroupRepository;
use App\Repositories\FeeCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeePostingGroupController extends SecureController
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var FeeCategoryRepository
     */
    private $feeCategoryRepository;

    /**
     * @var employeePostingGroupRepository
     */
    private $employeePostingGroupRepository;

    protected $activity;

    protected $module = 'Student Posting Group';

    /**
     * BehaviorController constructor.
     * @param
     */
    public function __construct(
        AccountRepository $accountRepository,
        EmployeePostingGroupRepository $employeePostingGroupRepository,
        FeeCategoryRepository $feeCategoryRepository,
        ActivityLogRepository $activity
    ) {
        $this->middleware('authorized:account.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:account.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:account.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:account.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->employeePostingGroupRepository = $employeePostingGroupRepository;
        $this->feeCategoryRepository = $feeCategoryRepository;
        $this->activity = $activity;

        view()->share('type', 'employee_posting_group');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('employee_posting_group.title');
        $employeePostingGroups = $this->employeePostingGroupRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('employee_posting_group.index', compact('title', 'employeePostingGroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('employee_posting_group.new');

        $accounts = $this->accountRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'title' => isset($item) ? $item->code.' '.$item->title : '',
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account'), 0)
            ->toArray();

        $prTaxLaws = PrTaxLaw::get()
            ->map(function ($item) {
                return [
                    'id'    => $item->id,
                    'title' => $item->title,
                ];
            })
            ->pluck('title', 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'accounts', 'prTaxLaws'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|AccountRequest $request
     * @return Response
     */
    public function store(employeePostingGroupRequest $request)
    {

        try
        {
            DB::transaction(function() use ($request) {

                $employeePostingGroup = EmployeePostingGroup::firstOrCreate
                (
                    [
                        'employee_id' => session('current_employee'),
                        'company_id' => session('current_company'),
                        'title' => $request['title'],
                        'code' => $request['code'],
                        'salary_account_id' => $request['salary_account_id'],
                        'income_tax_account_id' => $request['income_tax_account_id'],
                        'ssf_employee_expense_account_id' => $request['ssf_employee_expense_account_id'],
                        'ssf_total_payable_account_id' => $request['ssf_total_payable_account_id'],
                        'net_salary_payable_account_id' => $request['net_salary_payable_account_id'],
                        'pf_employer_expense_account_id' => $request['pf_employer_expense_account_id'],
                        'pf_total_payable_account_id' => $request['pf_total_payable_account_id'],
                        'pr_tax_law_id' => $request['pr_tax_law_id'],
                        'employment_tax_debit_account_id' => $request['employment_tax_debit_account_id'],
                        'employment_tax_credit_account_id' => $request['employment_tax_credit_account_id'],
                        'payslip_report' => 1,
                    ]
                );
                $this->activity->record([
                    'module'    => $this->module,
                    'module_id' => $employeePostingGroup->id,
                    'activity'  => 'created',
                ]);

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return 'Posting Group Added Successfully';


    }

    /**
     * Display the specified resource.
     *
     * @param employeePostingGroup $employeePostingGroup
     * @return Response
     * @internal param int $id
     */
    public function show(employeePostingGroup $employeePostingGroup)
    {
        $title = trans('employee_posting_group.details');
        $action = 'show';

        return view('layouts.show', compact('employeePostingGroup', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param employeePostingGroup $employeePostingGroup
     * @return Response
     * @internal param int $id
     */
    public function edit(employeePostingGroup $employeePostingGroup)
    {
        $title = trans('employee_posting_group.edit');

        $accounts = $this->accountRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'title' => isset($item) ? $item->code.' '.$item->title : '',
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account'), 0)
            ->toArray();

        $prTaxLaws = PrTaxLaw::get()
            ->map(function ($item) {
                return [
                    'id'    => $item->id,
                    'title' => $item->title,
                ];
            })
            ->pluck('title', 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'employeePostingGroup', 'accounts', 'prTaxLaws'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|EmployeePostingGroupRequest $request
     * @param employeePostingGroup $employeePostingGroup
     * @return Response
     * @internal param int $id
     */
    public function update(EmployeePostingGroupRequest $request, EmployeePostingGroup $employeePostingGroup)
    {
        try {
        $employeePostingGroup->update($request->except('fee_category_id', 'credit_account_id', 'debit_account_id', 'employee_posting_group_id', 'remove'));

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $employeePostingGroup->id,
            'activity'  => 'Updated',
        ]);

        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Employee Posting Group Updated Successfully');
    }

    public function delete(EmployeePostingGroup $employeePostingGroup)
    {
        $title = trans('employee_posting_group.delete');

        return view('/employee_posting_group/delete', compact('employeePostingGroup', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function destroy(employeePostingGroup $employeePostingGroup)
    {
        $employeePostingGroup->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $employeePostingGroup->id,
            'activity'  => 'Deleted',
        ]);

        return redirect('/employee_posting_group');
    }

    public function deleteFeeCategory(Request $request)
    {
        $item = employeePostingGroupFeeCategory::find($request->item_id)->first();
        $item->delete();

        $this->activity->record([
            'module'    => 'Student Posting Group Fee Category',
            'module_id' => $item->id,
            'activity'  => 'Deleted',
        ]);

        return 'Deleted Successfully';
    }

    public function data()
    {
        $employeePostingGroups = $this->employeePostingGroupRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($employeePostingGroup) {
                return [
                    'id' => $employeePostingGroup->id,
                    'title' => $employeePostingGroup->title,
                ];
            });

        return Datatables::make($employeePostingGroups)
            ->addColumn('actions', '<a href="{{ url(\'/employee_posting_group/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/employee_posting_group/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/employee_posting_group/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
