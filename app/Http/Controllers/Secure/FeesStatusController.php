<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests;
use App\Models\Invoice;
use App\Models\FeesStatus;
use App\Repositories\FeeCategoryRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\FeesStatusRepository;
use App\Repositories\StudentRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Settings;
use DB;
use App\Http\Requests\Secure\FeesStatusRequest;
use Illuminate\Support\Facades\App;

class FeesStatusController extends SecureController
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var InvoiceRepository
     */
    private $feesStatusRepository;

    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var FeeCategoryRepository
     */
    private $feeCategoryRepository;

    /**
     * InvoiceController constructor.
     * @param InvoiceRepository $invoiceRepository
     * @param StudentRepository $studentRepository
     * @param FeeCategoryRepository $feeCategoryRepository
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        StudentRepository $studentRepository,
        FeesStatusRepository $feesStatusRepository,
        FeeCategoryRepository $feeCategoryRepository
    ) {

        parent::__construct();

        $this->invoiceRepository = $invoiceRepository;
        $this->feesStatusRepository = $feesStatusRepository;
        $this->studentRepository = $studentRepository;
        $this->feeCategoryRepository = $feeCategoryRepository;

        $this->middleware('authorized:invoice.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:invoice.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:invoice.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:invoice.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'feesstatus');
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $title = trans('feesstatus.feesstatus');
        return view('feesstatus.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $title = trans('invoice.new');
        $this->generateParams();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FeesStatusRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(FeesStatusRequest $request)
    {
        foreach ($request['user_id'] as $user_id) {
            $feesStatus = new FeesStatus($request->except('user_id'));
            $feesStatus->user_id = $user_id;
            $feesStatus->company_id = session('current_company');
            $feesStatus->save();
        }
        return redirect('/feesstatus');
    }

    /**
     * Display the specified resource.
     *
     * @param  Invoice $invoice
     * @return Response
     */
    public function show(FeesStatus $feesstatus)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'landscape');
        $pdf->loadView('report.invoice', compact('feesstatus'));
        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Invoice $invoice
     * @return Response
     */
    public function edit(FeesStatus $feesstatus)
    {
        $title = trans('invoice.edit');
        $this->generateParams();

        return view('layouts.edit', compact('title', 'feesstatus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FeesStatusRequest $request
     * @param  Invoice $invoice
     *
     * @return Response
     */
    public function update(FeesStatusRequest $request, FeesStatus $feesstatus)
    {
        $feesstatus->update($request->all());
        return redirect('/feesstatus');
    }

    /**
     *
     *
     * @param Invoice $invoice
     * @return Response
     */
    public function delete(FeesStatus $feesstatus)
    {
        $title = trans('invoice.delete');
        return view('/feesstatus/delete', compact('feesstatus', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Invoice $invoice
     * @return Response
     */
    public function destroy(FeesStatus $feesstatus)
    {
        $feesstatus->delete();
        return redirect('/feesstatus');
    }

    public function data()
    {
        $one_school = (Settings::get('account_one_school')=='yes')?true:false;
        if ($one_school &&  $this->user->inRole('accountant')) {
            $feesStatuses = $this->feesStatusRepository->getAllStudentsForSchool(session('current_company'));
        } else {
            $feesStatuses = $this->feesStatusRepository->getAll();
        }
        $feesStatuses = $feesStatuses->with('user')
            ->get()
            ->map(function ($feesStatus) {
                return [
                    "id" => $feesStatus->id,
                    "sID" => isset($feesStatus->student) ? $feesStatus->student->sID : "",
                    "name" => isset($feesStatus->user) ? $feesStatus->user->full_name : "",
                    "Year" => isset($feesStatus->academicYear) ? $feesStatus->academicYear->title : "",
                    "semester" => isset($feesStatus->semester) ? $feesStatus->semester->title : "",
                    "total_fees" => $feesStatus->total_fees,
                    "paid" => $feesStatus->paid,
                    "amount" => $feesStatus->amount,
                ];
            });
        return Datatables::make($feesStatuses)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'invoice.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/feesstatus/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a target="_blank" href="{{ url(\'/feesstatus/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'invoice.delete\', Sentinel::getUser()->permissions)))
                                   <!--  <a href="{{ url(\'/feesstatus/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make(false);
    }

    /**
     * @return mixed
     */
    private function generateParams()
    {
        $one_school = (Settings::get('account_one_school')=='yes')?true:false;
        if ($one_school && $this->user->inRole('accountant')) {
            $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                                ->with('user')
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        "id"   => $item->user_id,
                                        "name" => isset($item->user) ? $item->user->full_name : "",
                                    ];
                                })->pluck("name", 'id')->toArray();
        } else {
            $students = $this->studentRepository->getAllForSchoolYear(session('current_company_year'))
                                ->with('user')
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        "id"   => $item->user_id,
                                        "name" => isset($item->user) ? $item->user->full_name : "",
                                    ];
                                })->pluck("name", 'id')->toArray();
        }
        view()->share('students', $students);

        $fee_categories = $this->feeCategoryRepository->getAll()
            ->get()
            ->map(function ($item) {
                return [
                    "id" => $item->id,
                    "title" => $item->title,
                ];
            })->pluck("title", 'id')->toArray();
        view()->share('fee_categories', $fee_categories);
    }
}
