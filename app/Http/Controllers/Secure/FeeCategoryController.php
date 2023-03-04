<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FeeCategoryRequest;
use App\Models\FeeCategory;
use App\Repositories\FeeCategoryRepository;
use App\Repositories\FeesPeriodRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SessionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\AccountRepository;
use App\Repositories\ActivityLogRepository;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Session;
use Sentinel;

class FeeCategoryController extends SecureController
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
     * @var FeesPeriodRepository
     */
    private $feesPeriodRepository;
    /**
     * @var FeesPeriodRepository
     */
    private $currencyRepository;

    private $sectionRepository;
    private $sessionRepository;
    private $levelRepository;

    protected $activity;
    protected $module = 'Fees Categories';

    /**
     * FeeCategoryController constructor.
     * @param FeeCategoryRepository $feeCategoryRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        FeeCategoryRepository $feeCategoryRepository,
        CurrencyRepository $currencyRepository,
        SectionRepository $sectionRepository,
        SessionRepository $sessionRepository,
        LevelRepository $levelRepository,
        FeesPeriodRepository $feesPeriodRepository,
        ActivityLogRepository $activity
    ) {

        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->currencyRepository = $currencyRepository;
        $this->feeCategoryRepository = $feeCategoryRepository;
        $this->feesPeriodRepository = $feesPeriodRepository;
        $this->sectionRepository = $sectionRepository;
        $this->sessionRepository = $sessionRepository;
        $this->levelRepository = $levelRepository;
        $this->activity = $activity;

        $this->middleware('authorized:fee_category.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:fee_category.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:fee_category.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:fee_category.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'fee_category');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('fee_category.fee_categories');
        $feeCategorys = $this->feeCategoryRepository->getAllForSchool(session('current_company'))
            ->with('level', 'session', 'section')
            ->get();
        return view('fee_category.index', compact('title', 'feeCategorys'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('fee_category.new');

        $feesPeriods = $this->feesPeriodRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_fees_period'), '')
            ->toArray();

        $currencies = $this->currencyRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_currency'), '')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_session'), '')
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $accounts = $this->accountRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->code. ' ' .$item->title : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'feesPeriods', 'currencies', 'sections', 'accounts', 'levels', 'sessions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(FeeCategoryRequest $request)
    {
        $feeCategory = new FeeCategory($request->all());
        $feeCategory->company_id = session('current_company');
        $feeCategory->user_id = Sentinel::getUser()->id;
        $feeCategory->save();

        return redirect('/fee_category');
    }

    /**
     * Display the specified resource.
     *
     * @param  FeeCategory $feeCategory
     * @return Response
     */
    public function show(FeeCategory $feeCategory)
    {
        $title = trans('fee_category.details');
        $action = 'show';
        return view('layouts.show', compact('feeCategory', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param FeeCategory $feeCategory
     * @return Response
     */
    public function edit(FeeCategory $feeCategory)
    {

        $title = trans('fee_category.edit');

        $feesPeriods = $this->feesPeriodRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $currencies = $this->currencyRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();



        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_session'), '')
            ->toArray();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), '')
            ->toArray();

        $accounts = $this->accountRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->code. ' ' .$item->title : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'feeCategory', 'feesPeriods', 'currencies', 'sections', 'accounts', 'sessions', 'levels'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(FeeCategoryRequest $request, FeeCategory $feeCategory)
    {
        $feeCategory->update($request->all());
        $feeCategory->company_id = session('current_company');
        $feeCategory->user_id = Sentinel::getUser()->id;
        return redirect('/fee_category');
    }


    public function delete(FeeCategory $feeCategory)
    {
        $title = trans('fee_category.delete');
        return view('/fee_category/delete', compact('feeCategory', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(FeeCategory $feeCategory)
    {
        $feeCategory->delete();
        return redirect('/fee_category');
    }






    public function findFeeCategoryAmount(Request $request)
    {
        $feeAmount = FeeCategory::find($request->fee_category_id);

        return $feeAmount->amount;
    }


    public function data()
    {
        $feeCategorys = $this->feeCategoryRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($feeCategory) {
                return [
                    'id' => $feeCategory->id,
                    'title' => $feeCategory->title,
                    'department' => isset($feeCategory->section) ? $feeCategory->section->title : "",
                    'currency' => isset($feeCategory->currency) ? $feeCategory->currency->name : "",
                    'amount' => $feeCategory->amount,
                    'period' => isset($feeCategory->feesPeriod) ? $feeCategory->feesPeriod->name : "",
                    'debit_account' => isset($feeCategory->debitAccount) ? $feeCategory->debitAccount->account_with_number : "",
                    'credit_account' => isset($feeCategory->creditAccount) ? $feeCategory->creditAccount->account_with_number : "",
                ];
            });

        return Datatables::make($feeCategorys)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'fee_category.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/fee_category/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/fee_category/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'fee_category.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/fee_category/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
