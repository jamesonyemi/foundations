<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\ScoreGradeRequest;
use App\Models\ScoreGrade;
use App\Repositories\MarkSystemRepository;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class scoreGradeController extends SecureController
{
    /**
     * @var MarkSystemRepository
     */
    private $markSystemRepository;

    /**
     * scoreGradeController constructor.
     *
     * @param MarkSystemRepository $markSystemRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        MarkSystemRepository $markSystemRepository
    ) {
        parent::__construct();

        $this->markSystemRepository = $markSystemRepository;

        view()->share('type', 'scoreGrade');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Performance Score Grades';
        $scoreGrades = ScoreGrade::where('company_id', session('current_company'))
            ->get();

        return view('scoreGrade.index', compact('title', 'scoreGrades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('scoreGrade.new');
        $mark_systems = $this->markSystemRepository
            ->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')
            ->prepend(trans('scoreGrade.select_mark_system'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'mark_systems'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(ScoreGradeRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $scoreGrade = new ScoreGrade($request->all());
                $scoreGrade->company_id = session('current_company');
                $scoreGrade->save();
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
    public function show(scoreGrade $scoreGrade)
    {
        $title = trans('scoreGrade.details');
        $action = 'show';

        return view('layouts.show', compact('scoreGrade', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(scoreGrade $scoreGrade)
    {
        $title = 'Edit Score Grade';
        $mark_systems = $this->markSystemRepository
            ->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')
            ->prepend('Select', 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'scoreGrade', 'mark_systems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(scoreGradeRequest $request, scoreGrade $scoreGrade)
    {
        try {
            DB::transaction(function () use ($request, $scoreGrade) {
                $scoreGrade->update($request->all());
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Grade Updated Successfully</div>');
    }

    public function delete(scoreGrade $scoreGrade)
    {
        $title = trans('scoreGrade.delete');

        return view('/scoreGrade/delete', compact('scoreGrade', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(scoreGrade $scoreGrade)
    {
        $scoreGrade->delete();

        return 'Deleted';
    }

    public function data()
    {
        $scoreGrades = $this->scoreGradeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($scoreGrade) {
                return [
                    'id' => $scoreGrade->id,
                    'mark_system' => isset($scoreGrade->mark_system->id) ? $scoreGrade->mark_system->title : '',
                    'max_score' => $scoreGrade->max_score,
                    'min_score' => $scoreGrade->min_score,
                    'grade' => $scoreGrade->grade,
                ];
            });

        return Datatables::make($scoreGrades)
            ->addColumn('actions', '<a href="{{ url(\'/scoreGrade/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/scoreGrade/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/scoreGrade/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function getImport()
    {
        $title = trans('scoreGrade.import_scoreGrade');

        return view('scoreGrade.import', compact('title'));
    }

    /*    public function postImport(ImportRequest $request)
        {
            $title = trans('scoreGrade.import_scoreGrade');

            ExcelfileValidator::validate($request);

            $reader = $this->excelRepository->load($request->file('file'));

            $scoreGrades = $reader->all()->map(function ($row) {
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
            return view('scoreGrade.import_list', compact('scoreGrades', 'mark_systems', 'title'));
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
            scoreGrade::create($import_data);
        }

        return redirect('/scoreGrade');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/mark_values.xlsx'));
    }
}
