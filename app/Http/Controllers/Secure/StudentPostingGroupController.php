<?php

namespace App\Http\Controllers\Secure;

use Illuminate\Http\Request;
use App\Models\StudentPostingGroup;
use App\Repositories\FeeCategoryRepository;
use App\Models\StudentPostingGroupFeeCategory;
use App\Repositories\AccountRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\StudentPostingGroupRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Secure\StudentPostingGroupRequest;

class StudentPostingGroupController extends SecureController
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
     * @var StudentPostingGroupRepository
     */

    private $studentPostingGroupRepository;


    protected $activity;
    protected $module = 'Student Posting Group';


    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        StudentPostingGroupRepository $studentPostingGroupRepository,
        FeeCategoryRepository $feeCategoryRepository,
        ActivityLogRepository $activity
    ) {

       /* $this->middleware('authorized:account.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:account.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:account.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:account.delete', ['only' => ['delete', 'destroy']]);*/

        parent::__construct();

        $this->accountRepository = $accountRepository;
        $this->studentPostingGroupRepository = $studentPostingGroupRepository;
        $this->feeCategoryRepository = $feeCategoryRepository;
        $this->activity = $activity;

        view()->share('type', 'student_posting_group');

        $columns = ['title', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('student_posting_group.title');
        return view('student_posting_group.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('student_posting_group.new');

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
            ->prepend(trans('account.select_account'), 0)
            ->toArray();


        $fee_categories = $this->feeCategoryRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"    => $item->id,
                    "title" => isset($item->section) ? $item->section->title. ' (' .$item->title .') (' .$item->currency->title .')': "",
                ];
            })
            ->pluck("title", 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'accounts', 'fee_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|AccountRequest $request
     * @return Response
     */
    public function store(StudentPostingGroupRequest $request)
    {
        $studentPostingGroup = new StudentPostingGroup($request->except('fee_category_id', 'credit_account_id', 'debit_account_id', 'student_posting_group_id', 'remove'));
        $studentPostingGroup->company_id = session('current_company');
        $studentPostingGroup->save();

        if (isset($request->fee_category_id)) {
            foreach ($request->get('fee_category_id') as $key => $fee_category_id) {
                StudentPostingGroupFeeCategory::firstOrCreate(['fee_category_id' => $fee_category_id,
                    'student_posting_group_id' => $studentPostingGroup->id,
                    'company_id' => session('current_company'),
                    'credit_account_id' => $request->get('credit_account_id')[$key],
                    'debit_account_id' => $request->get('debit_account_id')[$key]
                ]);

            }
        }

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $studentPostingGroup->id,
            'activity'  => 'created'
        ]);

        return redirect('/student_posting_group');
    }

    /**
     * Display the specified resource.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function show(StudentPostingGroup $studentPostingGroup)
    {
        $title = trans('student_posting_group.details');
        $action = 'show';
        return view('layouts.show', compact('studentPostingGroup', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Account $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(StudentPostingGroup $studentPostingGroup)
    {
        $title = trans('student_posting_group.edit');

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
            ->prepend(trans('account.select_account'), 0)
            ->toArray();

        $fee_categories = $this->feeCategoryRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"    => $item->id,
                    "title" => isset($item->section) ? $item->section->title. ' (' .$item->title .') (' .$item->currency->name .')': "",
                ];
            })
            ->pluck("title", 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'studentPostingGroup', 'accounts', 'fee_categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|AccountRequest $request
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function update(StudentPostingGroupRequest $request, StudentPostingGroup $studentPostingGroup)
    {
        $studentPostingGroup->update($request->except('fee_category_id', 'credit_account_id', 'debit_account_id', 'student_posting_group_id', 'remove'));

        if (isset($request->fee_category_id)){
        foreach ( $request->get( 'fee_category_id' ) as $key => $fee_category_id ) {
            StudentPostingGroupFeeCategory::firstOrCreate( [ 'fee_category_id'  => $fee_category_id,
                'student_posting_group_id' => $studentPostingGroup->id,
                'company_id'  => session('current_company'),
                'credit_account_id'   => $request->get( 'credit_account_id' )[ $key ],
                'debit_account_id'   => $request->get( 'debit_account_id' )[ $key ]
            ] );

        }
        }

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $studentPostingGroup->id,
            'activity'  => 'Updated'
        ]);

        return redirect('/student_posting_group');
    }

    public function delete(StudentPostingGroup $studentPostingGroup)
    {
        $title = trans('student_posting_group.delete');
        return view('/student_posting_group/delete', compact('studentPostingGroup', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account $account
     * @return Response
     * @internal param int $id
     */
    public function destroy(StudentPostingGroup $studentPostingGroup)
    {
        $studentPostingGroup->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $studentPostingGroup->id,
            'activity'  => 'Deleted'
        ]);
        return redirect('/student_posting_group');
    }


    public function deleteFeeCategory(Request $request)
    {
        $item = StudentPostingGroupFeeCategory::find($request->item_id)->first();
        $item->delete();

        $this->activity->record([
            'module'    => 'Student Posting Group Fee Category',
            'module_id' => $item->id,
            'activity'  => 'Deleted'
        ]);
        return 'Deleted Successfully';
    }

    public function data()
    {
        $studentPostingGroups = $this->studentPostingGroupRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($studentPostingGroup) {
                return [
                    'id' => $studentPostingGroup->id,
                    'title' => $studentPostingGroup->title,
                ];
            });

        return Datatables::make($studentPostingGroups)
            ->addColumn('actions', '<a href="{{ url(\'/student_posting_group/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/student_posting_group/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/student_posting_group/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
