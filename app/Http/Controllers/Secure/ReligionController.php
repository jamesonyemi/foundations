<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ReligionRequest;
use App\Models\Religion;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReligionController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'religion');

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
        $title = trans('religion.religion');

        return view('religion.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('religion.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ReligionRequest|Request $request
     * @return Response
     */
    public function store(ReligionRequest $request)
    {
        $religion = new Religion($request->all());
        $religion->save();

        return redirect('/religion');
    }

    /**
     * Display the specified resource.
     *
     * @param Religion $religion
     * @return Response
     * @internal param int $id
     */
    public function show(Religion $religion)
    {
        $title = trans('religion.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'religion', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Religion $religion
     * @return Response
     */
    public function edit(Religion $religion)
    {
        $title = trans('religion.edit');

        return view('layouts.edit', compact('title', 'religion'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Religion|Request $request
     * @param Religion $religion
     * @return Response
     */
    public function update(ReligionRequest $request, Religion $religion)
    {
        $religion->update($request->all());
        $religion->save();

        return redirect('/religion');
    }

    public function delete(Religion $religion)
    {
        $title = trans('religion.delete');

        return view('religion.delete', compact('title', 'religion'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Religion $religion
     * @return Response
     */
    public function destroy(Religion $religion)
    {
        $religion->delete();

        return redirect('/religion');
    }

    public function data()
    {
        $religions = Religion::get()
            ->map(function ($religion) {
                return [
                    'id' => $religion->id,
                    'title' => $religion->name,
                ];
            });

        return Datatables::make($religions)
            ->addColumn('actions', '<a href="{{ url(\'/religion/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/religion/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/religion/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
