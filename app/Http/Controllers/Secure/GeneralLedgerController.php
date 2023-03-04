<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\JournalRequest;
use App\Models\GeneralLedger;
use App\Models\Journal;
use App\Repositories\AccountRepository;
use App\Repositories\AccountTypeRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\JournalRepository;
use App\Repositories\StudentRepository;
use App\Repositories\VendorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class GeneralLedgerController extends SecureController
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AccountRepository
     */
    private $journalRepository;

    /**
     * @var AccountTypeRepository
     */
    private $vendorRepository;

    /**
     * @var AccountTypeRepository
     */
    private $accountTypeRepository;

    /**
     * @var StudentRepository
     */
    private $studentRepository;

    protected $activity;

    protected $module = 'journal';

    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        StudentRepository $studentRepository,
        AccountTypeRepository $accountTypeRepository,
        JournalRepository $journalRepository,
        VendorRepository $vendorRepository,
        ActivityLogRepository $activity
    ) {

       /* $this->middleware('authorized:account.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:account.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:account.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:account.delete', ['only' => ['delete', 'destroy']]);*/

        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->studentRepository = $studentRepository;
        $this->accountTypeRepository = $accountTypeRepository;
        $this->vendorRepository = $vendorRepository;
        $this->journalRepository = $journalRepository;
        $this->activity = $activity;

        view()->share('type', 'journal');

        $columns = ['id', 'Date', 'narration', 'account', 'amount', 'type', 'actions'];
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

        @$journals = @$this->journalRepository->getAllForSchool(session('current_company'))
            ->orderBy('id', 'DESC')
            ->get();

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

        $vendors = $this->vendorRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'title' => isset($item) ? $item->title : '',
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('account.select_account'), '')
            ->toArray();

        return view('journal.index', compact('title', 'accounts', 'journals', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('account_type.new');

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
        $journal = new Journal($request->except('amount'));
        $journal->company_id = session('current_company');
        $journal->company_year_id = session('current_company_year');
        $journal->semester_id = session('current_company_semester');
        $journal->user_id = Sentinel::getUser()->id;
        $journal->save();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $journal->id,
            'activity'  => 'created',
        ]);

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
        $title = trans('account_type.details');
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
        $title = trans('account_type.edit');

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
        $journal->user_id = Sentinel::getUser()->id;
        $journal->save();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $journal->id,
            'activity'  => 'Updated',
        ]);

        return redirect('/journal');
    }

    public function delete(Journal $journal)
    {
        $title = trans('account_type.delete');

        return view('/journal/delete', compact('accountType', 'title', 'journal'));
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

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $journal->id,
            'activity'  => 'Deleted',
        ]);

        return redirect('/journal');
    }

    /* public function data()
     {
         $journals = $this->journalRepository->getAllForSchool(session('current_company'))
             ->get()
             ->map(function ($journal) {
                 return [
                     'id' => $journal->id,
                     'Date' => $journal->journal_date,
                     'narration' => $journal->narration,
                     'account' => isset($journal->account) ? $journal->account->code. ' ' .$journal->account->title :  "",
                     'amount' => $journal->amount,
                     'type' => $journal->transaction_type,
                 ];
             });

         return Datatables::make($journals)
             ->addColumn('actions', '<a href="{{ url(\'/journal/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                             <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>

                                      <a href="{{ url(\'/journal/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                             <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
              ->rawColumns([ 'actions' ])->make();
     }*/

    public function ajaxAddJournal(JournalRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $journal = new Journal($request->except('amount'));
                $journal->user_id = Sentinel::getUser()->id;
                $journal->company_id = session('current_company');
                $journal->company_year_id = session('current_company_year');
                $journal->semester_id = session('current_company_semester');

                if ($request->transaction_type === 'credit') {
                    $journal->amount = $request->amount;
                }

                if ($request->transaction_type === 'debit') {
                    $journal->amount = -$request->amount;
                }

                $journal->save();
            });
        } catch (\Exception $e) {
            return $e;
        }

        $journals = $this->journalRepository->getAllForSchool(session('current_company'))
            ->orderBy('id', 'DESC')
            ->get();

        return view('/journal/list', compact('journals'))->with('status', 'Journal Successfully!');
        /*return 'Added Successfully';*/
    }

    public function ajaxPostJournal(Request $request)
    {
        $journals = $this->journalRepository->getAllForSchool(session('current_company'))
            ->orderBy('id', 'DESC')
            ->get();

        /*GET THE TOTAL SUM IN JOURNALS BEING POSTED TO CHECK IF BALANCED*/
        $journalsTotal = Journal::whereIn('id', $request->journal_id);
        $bal = $journalsTotal->sum('amount');

        if ($bal == 0) {
            try {
                DB::transaction(function () use ($request) {
                    if (isset($request->journal_id)) {
                        foreach ($request->get('journal_id') as $key => $journal_id) {
                            $journal = Journal::where('id', $journal_id)->first();

                            GeneralLedger::Create(['company_id' => session('current_company'),
                                'company_year_id' => session('current_company_year'),
                                'semester_id' => session('current_company_semester'),
                                'account_id' => $journal->account_id,
                                'amount' => $journal->amount,
                                'narration' => $journal->narration,
                                'transaction_type' => $journal->transaction_type,
                                'transaction_date' => $journal->journal_date,
                            ]);

                            $journal->delete();
                        }
                    }
                });
            } catch (\Exception $e) {
                return $e;
            }
        } else {
            return view('/journal/list', compact('journals'))->with('status', 'Journals not balanced!');
        }

        return view('/journal/list', compact('journals'))->with('status', 'Journal Successfully!');

        /*return $bal;*/
    }
}
