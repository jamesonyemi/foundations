<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\HolidayRequest;
use App\Models\Holiday;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Yajra\DataTables\Facades\DataTables;

class HolidayController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('authorized:holiday.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:holiday.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:holiday.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:holiday.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'holiday');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('holiday.holiday');
        $holiday = Holiday::get();

        return view('holiday.index', compact('title', 'holiday'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('holiday.new');

        return view('holiday._form', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(HolidayRequest $request)
    {
        $holiday = new Holiday($request->all());
        $holiday->company_id = session('current_company');
        $holiday->save();

        return response('<div class="alert alert-success">EVENT CREATED Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Holiday $holiday)
    {
        $title = trans('holiday.details');
        $action = 'show';

        return view('layouts.show', compact('holiday', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Holiday $holiday)
    {
        $title = trans('holiday.edit');

        return view('holiday._form', compact('title', 'holiday'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(HolidayRequest $request, Holiday $holiday)
    {
        $holiday->update($request->all());

        return response('<div class="alert alert-success">Holiday Updated Successfully</div>');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Holiday $holiday)
    {
        $title = trans('holiday.delete');

        return view('/holiday/delete', compact('holiday', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect('/holiday');
    }


}
