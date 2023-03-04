<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\AccountTypeRequest;
use App\Models\AccountType;
use App\Repositories\AccountRepository;
use App\Repositories\AccountTypeRepository;
use App\Repositories\ActivityLogRepository;
use Yajra\DataTables\Facades\DataTables;

class AccountTypeController extends SecureController
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AccountTypeRepository
     */
    private $accountTypeRepository;

    protected $activity;

    protected $module = 'Account Type';

    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        AccountTypeRepository $accountTypeRepository,
        ActivityLogRepository $activity
    ) {
        $this->middleware('authorized:account.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:account.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:account.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:account.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->accountTypeRepository = $accountTypeRepository;
        $this->activity = $activity;

        view()->share('type', 'account_type');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('account_type.accounts');
        $accountTypes = $this->accountTypeRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('account_type.index', compact('title', 'accountTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('account_type.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|AccountRequest $request
     * @return Response
     */
    public function store(AccountTypeRequest $request)
    {
        $accountType = new AccountType($request->all());
        $accountType->company_id = session('current_company');
        $accountType->save();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $accountType->id,
            'activity'  => 'created',
        ]);

        return redirect('/account_type');
    }

    /**
     * Display the specified resource.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function show(AccountType $accountType)
    {
        $title = trans('account_type.details');
        $action = 'show';

        return view('layouts.show', compact('accountType', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Account $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(AccountType $accountType)
    {
        $title = trans('account_type.edit');

        return view('layouts.edit', compact('title', 'accountType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|AccountRequest $request
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function update(AccountTypeRequest $request, AccountType $accountType)
    {
        $accountType->update($request->all());

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $accountType->id,
            'activity'  => 'Updated',
        ]);

        return redirect('/account_type');
    }

    public function delete(AccountType $accountType)
    {
        $title = trans('account_type.delete');

        return view('/account_type/delete', compact('accountType', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function destroy(AccountType $accountType)
    {
        $accountType->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $accountType->id,
            'activity'  => 'Deleted',
        ]);

        return redirect('/account_type');
    }

    public function data()
    {
        $accountTypes = $this->accountTypeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($accountType) {
                return [
                    'id' => $accountType->id,
                    'title' => $accountType->title,
                    'Balancing Type' => $accountType->balancing_type,
                ];
            });

        return Datatables::make($accountTypes)
            ->addColumn('actions', '<a href="{{ url(\'/account_type/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/account_type/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/account_type/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
