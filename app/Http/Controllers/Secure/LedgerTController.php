<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\JournalRequest;
use App\Models\Journal;
use App\Repositories\AccountRepository;
use App\Repositories\JournalRepository;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class LedgerTController extends SecureController
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AccountTypeRepository
     */
    private $journalRepository;

    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        JournalRepository $journalRepository
    ) {
        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->journalRepository = $journalRepository;

        view()->share('type', 'journal');

        $columns = ['narration',  'account', 'amount', 'user', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('journal.journals');

        return view('journal.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('journal.new');

        $accounts = $this->accountRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account_type'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|AccountRequest $request
     * @return Response
     */
    public function store(JournalRequest $request)
    {
        $journal = new Journal($request->all());
        $journal->company_id = session('current_company');
        $journal->company_year_id = session('current_company_year');
        $journal->user_id = Sentinel::getUser()->id;
        $journal->save();

        return redirect('/journal');
    }

    /**
     * Display the specified resource.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function show(Journal $journal)
    {
        $title = trans('journal.details');
        $action = 'show';

        return view('layouts.show', compact('journal', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Account $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(Journal $journal)
    {
        $title = trans('journal.edit');

        $accounts = $this->accountRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'journal', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|AccountRequest $request
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function update(JournalRequest $request, Journal $journal)
    {
        $journal->update($request->all());

        return redirect('/journal');
    }

    public function delete(Journal $journal)
    {
        $title = trans('account.delete');

        return view('/journal/delete', compact('journal', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function destroy(Journal $journal)
    {
        $journal->delete();

        return redirect('/journal');
    }

    public function data()
    {
        $journals = $this->journalRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($journal) {
                return [
                    'id' => $journal->id,
                    'narration' => $journal->narration,
                    'account' => $journal->account->title,
                    'amount' => $journal->amount,
                    'user' => $journal->user->full_name,
                ];
            });

        return Datatables::make($journals)
            ->addColumn('actions', '<a href="{{ url(\'/journal/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/journal/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/journal/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
