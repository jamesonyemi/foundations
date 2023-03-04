<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\BehaviorAddEditRequest;
use App\Models\Behavior;
use App\Repositories\BehaviorRepository;
use Yajra\DataTables\Facades\DataTables;

class BehaviorController extends SecureController
{
    /**
     * @var BehaviorRepository
     */
    private $behaviorRepository;

    /**
     * BehaviorController constructor.
     * @param BehaviorRepository $behaviorRepository
     */
    public function __construct(BehaviorRepository $behaviorRepository)
    {
        parent::__construct();

        $this->behaviorRepository = $behaviorRepository;

        view()->share('type', 'behavior');

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
        $title = trans('behavior.behaviors');

        return view('behavior.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('behavior.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|BehaviorAddEditRequest $request
     * @return Response
     */
    public function store(BehaviorAddEditRequest $request)
    {
        $behavior = new Behavior($request->all());
        $behavior->company_id = session('current_company');
        $behavior->save();

        return redirect('/behavior');
    }

    /**
     * Display the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function show(Behavior $behavior)
    {
        $title = trans('behavior.details');
        $action = 'show';

        return view('layouts.show', compact('behavior', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(Behavior $behavior)
    {
        $title = trans('behavior.edit');

        return view('layouts.edit', compact('title', 'behavior'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|BehaviorAddEditRequest $request
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function update(BehaviorAddEditRequest $request, Behavior $behavior)
    {
        $behavior->update($request->all());

        return redirect('/behavior');
    }

    public function delete(Behavior $behavior)
    {
        $title = trans('behavior.delete');

        return view('/behavior/delete', compact('behavior', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function destroy(Behavior $behavior)
    {
        $behavior->delete();

        return redirect('/behavior');
    }

    public function data()
    {
        $behaviors = $this->behaviorRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($behavior) {
                return [
                    'id' => $behavior->id,
                    'title' => $behavior->title,
                ];
            });

        return Datatables::make($behaviors)
            ->addColumn('actions', '<a href="{{ url(\'/behavior/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/behavior/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/behavior/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
