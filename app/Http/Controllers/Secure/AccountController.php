<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\AccountRequest;
use App\Models\Account;
use App\Repositories\AccountRepository;
use App\Repositories\AccountTypeRepository;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends SecureController
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AccountTypeRepository
     */
    private $accountTypeRepository;

    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        AccountTypeRepository $accountTypeRepository
    ) {
        $this->middleware('authorized:account.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:account.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:account.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:account.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->accountTypeRepository = $accountTypeRepository;

        view()->share('type', 'financial_account');

        $columns = ['code', 'title', 'type', 'balance', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('account.accounts');

        $accountTypes = $this->accountTypeRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $financialAccounts = $this->accountRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('financial_account.index', compact('title', 'accountTypes', 'financialAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('account.new');

        $accountTypes = $this->accountTypeRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account_type'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'accountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|AccountRequest $request
     * @return Response
     */
    public function store(AccountRequest $request)
    {
        $account = new Account($request->all());
        $account->company_id = session('current_company');
        $account->save();

        return redirect('/financial_account');
    }

    /**
     * Display the specified resource.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function show(Account $financialAccount)
    {
        $title = trans('account.details');
        $action = 'show';

        return view('layouts.show', compact('financialAccount', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Account $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(Account $financialAccount)
    {
        $title = trans('account.edit');

        $accountTypes = $this->accountTypeRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account_type'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'financialAccount', 'accountTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|AccountRequest $request
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function update(AccountRequest $request, Account $financialAccount)
    {
        $financialAccount->update($request->all());

        return redirect('/financial_account');
    }

    public function delete(Account $financialAccount)
    {
        $title = trans('account.delete');

        return view('/financial_account/delete', compact('financialAccount', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function destroy(Account $financialAccount)
    {
        $financialAccount->delete();

        return redirect('/financial_account');
    }

    public function data()
    {
        $financialAccounts = $this->accountRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'title' => $account->title,
                    'type' => $account->type->title,
                    'balance' => $account->balance,
                    /*'balance' => $account->journal->sum('amount'),*/
                ];
            });

        return Datatables::make($financialAccounts)
            ->addColumn('actions', '<a href="{{ url(\'/financial_account/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/financial_account/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/financial_account/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
