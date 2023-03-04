<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\MarkTypeRequest;
use App\Models\MarkType;
use App\Repositories\MarkTypeRepository;
use Auth;
use Yajra\DataTables\Facades\DataTables;

class MarkTypeController extends SecureController
{
    /**
     * @var MarkTypeRepository
     */
    private $markTypeRepository;

    /**
     * MarkTypeController constructor.
     * @param MarkTypeRepository $markTypeRepository
     */
    public function __construct(MarkTypeRepository $markTypeRepository)
    {
        parent::__construct();

        $this->markTypeRepository = $markTypeRepository;

        view()->share('type', 'marktype');

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
        $title = trans('marktype.marktypes');

        return view('marktype.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('marktype.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(MarkTypeRequest $request)
    {
        $markType = new MarkType($request->all());
        $markType->company_id = session('current_company');
        $markType->save();

        return redirect('/marktype');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(MarkType $markType)
    {
        $title = trans('marktype.details');
        $action = 'show';

        return view('layouts.show', compact('markType', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(MarkType $markType)
    {
        $title = trans('marktype.edit');

        return view('layouts.edit', compact('title', 'markType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(MarkTypeRequest $request, MarkType $markType)
    {
        $markType->update($request->all());

        return redirect('/marktype');
    }

    public function delete(MarkType $markType)
    {
        $title = trans('marktype.delete');

        return view('/marktype/delete', compact('markType', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(MarkType $markType)
    {
        $markType->delete();

        return redirect('/marktype');
    }

    public function data()
    {
        $markTypes = $this->markTypeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($markType) {
                return [
                    'id' => $markType->id,
                    'title' => $markType->title,
                ];
            });

        return Datatables::make($markTypes)
            ->addColumn('actions', '<a href="{{ url(\'/marktype/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/marktype/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/marktype/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
