<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\ExcelfileValidator;
use App\Http\Requests\Secure\ImportRequest;
use App\Models\TimetablePeriod;
use App\Repositories\ExcelRepository;
use App\Repositories\TimetablePeriodRepository;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Secure\TimetablePeriodRequest;

class TimetablePeriodController extends SecureController
{
    /**
     * @var TimetablePeriodRepository
     */
    private $timetablePeriodRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;

    /**
     * TimetablePeriodController constructor.
     *
     * @param TimetablePeriodRepository $timetablePeriodRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        TimetablePeriodRepository $timetablePeriodRepository,
        ExcelRepository $excelRepository
    ) {

        parent::__construct();

        $this->timetablePeriodRepository = $timetablePeriodRepository;
        $this->excelRepository = $excelRepository;

        view()->share('type', 'timetable_period');

        $columns = ['start_at','end_at','title', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('timetable_period.timetable_periods');
        return view('timetable_period.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('timetable_period.new');
        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|TimetablePeriodRequest $request
     * @return Response
     */
    public function store(TimetablePeriodRequest $request)
    {
        $timetablePeriod = new TimetablePeriod($request->all());
        $timetablePeriod->company_id = session('current_company');
        $timetablePeriod->save();

        return redirect('/timetable_period');
    }

    /**
     * Display the specified resource.
     *
     * @param TimetablePeriod $timetablePeriod
     * @return Response
     * @internal param int $id
     */
    public function show(TimetablePeriod $timetablePeriod)
    {
        $title = trans('timetable_period.details');
        $action = 'show';
        return view('layouts.show', compact('timetablePeriod', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TimetablePeriod $timetablePeriod
     * @return Response
     * @internal param int $id
     */
    public function edit(TimetablePeriod $timetablePeriod)
    {
        $title = trans('timetable_period.edit');
        return view('layouts.edit', compact('title', 'timetablePeriod'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|TimetablePeriodRequest $request
     * @param TimetablePeriod $timetablePeriod
     * @return Response
     * @internal param int $id
     */
    public function update(TimetablePeriodRequest $request, TimetablePeriod $timetablePeriod)
    {
        $timetablePeriod->update($request->all());
        return redirect('/timetable_period');
    }

    public function delete(TimetablePeriod $timetablePeriod)
    {
        $title = trans('timetable_period.delete');
        return view('/timetable_period/delete', compact('timetablePeriod', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TimetablePeriod $timetablePeriod
     * @return Response
     * @internal param int $id
     */
    public function destroy(TimetablePeriod $timetablePeriod)
    {
        $timetablePeriod->delete();
        return redirect('/timetable_period');
    }

    public function data()
    {
        $timetablePeriods = $this->timetablePeriodRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($timetablePeriod) {
                return [
                    'id' => $timetablePeriod->id,
                    'start_at' => $timetablePeriod->start_at,
                    'end_at' => $timetablePeriod->end_at,
                    'title' => $timetablePeriod->title,
                ];
            });

        return Datatables::make($timetablePeriods)
            ->addColumn('actions', '<a href="{{ url(\'/timetable_period/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/timetable_period/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/timetable_period/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }

    public function getImport()
    {
        $title = trans('timetable_period.import_timetable_period');

        return view('timetable_period.import', compact('title'));
    }

    public function postImport(ImportRequest $request)
    {
        $title = trans('timetable_period.import_timetable_period');

        ExcelfileValidator::validate($request);

        $reader = $this->excelRepository->load($request->file('file'));

        $timetable_periods = $reader->all()->map(function ($row) {
            return [
                'title' => trim($row->title),
                'start_at' => date(Settings::get('time_format'), strtotime(trim($row->start))),
                'end_at' => date(Settings::get('time_format'), strtotime(trim($row->end))),
            ];
        });
        return view('timetable_period.import_list', compact('timetable_periods', 'title'));
    }

    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'title'=>$request->title[$item],
                'start_at'=>$request->start_at[$item],
                'end_at'=>$request->end_at[$item]
            ];
            TimetablePeriod::create($import_data);
        }

        return redirect('/timetable_period');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/timetable_periods.xlsx'));
    }
}
