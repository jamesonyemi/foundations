<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\MarkSystemRequest;
use App\Models\MarkSystem;
use App\Repositories\MarkSystemRepository;
use Auth;
use Yajra\DataTables\Facades\DataTables;

class MarkSystemController extends SecureController
{
    /**
     * @var MarkSystemRepository
     */
    private $markSystemRepository;

    /**
     * MarkSystemController constructor.
     * @param MarkSystemRepository $markSystemRepository
     */
    public function __construct(MarkSystemRepository $markSystemRepository)
    {
        parent::__construct();

        $this->markSystemRepository = $markSystemRepository;

        view()->share('type', 'marksystem');

        $columns = ['title', 'grade_gpa', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('marksystem.mark_systems');

        $markSystems = $this->markSystemRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('marksystem.index', compact('title', 'markSystems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('marksystem.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(MarkSystemRequest $request)
    {
        $markSystem = new MarkSystem($request->all());
        $markSystem->company_id = session('current_company');
        $markSystem->save();

        return redirect('/marksystem');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(MarkSystem $markSystem)
    {
        $title = trans('marksystem.details');
        $action = 'show';

        return view('layouts.show', compact('markSystem', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(MarkSystem $markSystem)
    {
        $title = trans('marksystem.edit');

        return view('layouts.edit', compact('title', 'markSystem'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(MarkSystemRequest $request, MarkSystem $markSystem)
    {
        $markSystem->update($request->all());

        return redirect('/marksystem');
    }

    public function delete(MarkSystem $markSystem)
    {
        $title = trans('marksystem.delete');

        return view('/marksystem/delete', compact('markSystem', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(MarkSystem $markSystem)
    {
        $markSystem->delete();

        return redirect('/marksystem');
    }

    public function data()
    {
        $markSystems = $this->markSystemRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($markSystem) {
                return [
                    'id' => $markSystem->id,
                    'title' => $markSystem->title,
                    'grade_gpa' => $markSystem->grade_gpa,
                ];
            });

        return Datatables::make($markSystems)
            ->addColumn('actions', '<a href="{{ url(\'/marksystem/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/marksystem/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/marksystem/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
