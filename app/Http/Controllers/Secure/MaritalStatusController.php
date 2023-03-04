<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\MaritalStatusRequest;
use App\Models\MaritalStatus;
use App\Repositories\MaritalStatusRepository;
use Auth;
use Yajra\DataTables\Facades\DataTables;

class MaritalStatusController extends SecureController
{
    /**
     * @var MaritalStatusRepository
     */
    private $maritalStatusRepository;

    /**
     * MaritalStatusController constructor.
     * @param MaritalStatusRepository $maritalStatusRepository
     */
    public function __construct(MaritalStatusRepository $maritalStatusRepository)
    {
        parent::__construct();

        $this->maritalStatusRepository = $maritalStatusRepository;

        view()->share('type', 'marital_status');

        $columns = ['name', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('marital_status.marital_status');

        return view('marital_status.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('marital_status.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  MaritalStatusRequest $request
     * @return Response
     */
    public function store(MaritalStatusRequest $request)
    {
        $maritalStatus = new MaritalStatus($request->all());
        $maritalStatus->save();

        return redirect('/marital_status');
    }

    /**
     * Display the specified resource.
     *
     * @param  MaritalStatus $maritalStatus
     * @return Response
     */
    public function show(MaritalStatus $maritalStatus)
    {
        $title = trans('marital_status.details');
        $action = 'show';

        return view('layouts.show', compact('maritalStatus', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  MaritalStatus $maritalStatus
     * @return Response
     */
    public function edit(MaritalStatus $maritalStatus)
    {
        $title = trans('marital_status.edit');

        return view('layouts.edit', compact('title', 'maritalStatus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  MaritalStatusRequest $request
     * @param  MaritalStatus $maritalStatus
     * @return Response
     */
    public function update(MaritalStatusRequest $request, MaritalStatus $maritalStatus)
    {
        $maritalStatus->update($request->all());

        return redirect('/marital_status');
    }

    public function delete(MaritalStatus $maritalStatus)
    {
        $title = trans('marital_status.delete');

        return view('/marital_status/delete', compact('maritalStatus', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MaritalStatus $maritalStatus
     *
     * @return Response
     */
    public function destroy(MaritalStatus $maritalStatus)
    {
        $maritalStatus->delete();

        return redirect('/marital_status');
    }

    public function data()
    {
        $maritalStatuss = $this->maritalStatusRepository->getAll()
            ->get()
            ->map(function ($maritalStatus) {
                return [
                    'id' => $maritalStatus->id,
                    'name' => $maritalStatus->name,
                ];
            });

        return Datatables::make($maritalStatuss)
            ->addColumn('actions', '<a href="{{ url(\'/marital_status/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/marital_status/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/marital_status/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
