<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\MarkValueRequest;
use App\Models\MarkValue;
use App\Repositories\MarkSystemRepository;
use App\Repositories\MarkValueRepository;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkValueController extends SecureController
{
    /**
     * @var MarkValueRepository
     */
    private $markValueRepository;

    /**
     * @var MarkSystemRepository
     */
    private $markSystemRepository;

    /**
     * MarkValueController constructor.
     *
     * @param MarkValueRepository $markValueRepository
     * @param MarkSystemRepository $markSystemRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        MarkValueRepository $markValueRepository,
        MarkSystemRepository $markSystemRepository
    ) {
        parent::__construct();

        $this->markValueRepository = $markValueRepository;
        $this->markSystemRepository = $markSystemRepository;

        view()->share('type', 'markvalue');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('markvalue.markvalues');
        $markValues = $this->markValueRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('markvalue.index', compact('title', 'markValues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('markvalue.new');
        $mark_systems = $this->markSystemRepository
            ->getAll()
            ->pluck('title', 'id')
            ->prepend(trans('markvalue.select_mark_system'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'mark_systems'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(MarkValueRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $markValue = new MarkValue($request->all());
                $markValue->company_id = session('current_company');
                $markValue->save();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Grade Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(MarkValue $markValue)
    {
        $title = trans('markvalue.details');
        $action = 'show';

        return view('layouts.show', compact('markValue', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(MarkValue $markValue)
    {
        $title = trans('markvalue.edit');
        $mark_systems = $this->markSystemRepository
            ->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')
            ->prepend(trans('markvalue.select_mark_system'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'markValue', 'mark_systems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MarkValueRequest $request, MarkValue $markValue)
    {
        try {
            DB::transaction(function () use ($request, $markValue) {
                $markValue->update($request->all());
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Grade Updated Successfully</div>');
    }

    public function delete(MarkValue $markValue)
    {
        $title = trans('markvalue.delete');

        return view('/markvalue/delete', compact('markValue', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(MarkValue $markValue)
    {
        $markValue->delete();

        return 'Deleted';
    }

    public function data()
    {
        $markValues = $this->markValueRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($markValue) {
                return [
                    'id' => $markValue->id,
                    'mark_system' => isset($markValue->mark_system->id) ? $markValue->mark_system->title : '',
                    'max_score' => $markValue->max_score,
                    'min_score' => $markValue->min_score,
                    'grade' => $markValue->grade,
                ];
            });

        return Datatables::make($markValues)
            ->addColumn('actions', '<a href="{{ url(\'/markvalue/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/markvalue/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/markvalue/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function getImport()
    {
        $title = trans('markvalue.import_markvalue');

        return view('markvalue.import', compact('title'));
    }

    /*    public function postImport(ImportRequest $request)
        {
            $title = trans('markvalue.import_markvalue');

            ExcelfileValidator::validate($request);

            $reader = $this->excelRepository->load($request->file('file'));

            $markvalues = $reader->all()->map(function ($row) {
                return [
                    'max_score' => intval($row->max_score),
                    'min_score' => intval($row->min_score),
                    'grade' => trim($row->grade),
                ];
            });

            $mark_systems = $this->markSystemRepository->getAll()
                                                       ->get()->map(function ($section) {
                                                        return [
                                                        'text' => $section->title,
                                                        'id' => $section->id,
                                                        ];
                                                       })->pluck('text', 'id');
            return view('markvalue.import_list', compact('markvalues', 'mark_systems', 'title'));
        }*/

    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'grade'=>$request->grade[$item],
                'max_score'=>$request->max_score[$item],
                'min_score'=>$request->min_score[$item],
                'mark_system_id'=>$request->mark_system_id[$item],
            ];
            MarkValue::create($import_data);
        }

        return redirect('/markvalue');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/mark_values.xlsx'));
    }
}
