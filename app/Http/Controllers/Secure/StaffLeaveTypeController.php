<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\StaffLeaveTypeRequest;
use App\Models\StaffLeaveType;
use App\Repositories\StaffLeaveTypeRepository;
use Yajra\DataTables\Facades\DataTables;

class StaffLeaveTypeController extends SecureController
{
    /**
     * @var StaffLeaveTypeRepository
     */
    private $staffLeaveTypeRepository;

    /**
     * StaffLeaveTypeController constructor.
     *
     * @param StaffLeaveTypeRepository $staffLeaveTypeRepository
     */
    public function __construct(StaffLeaveTypeRepository $staffLeaveTypeRepository)
    {
        parent::__construct();

        $this->staffLeaveTypeRepository = $staffLeaveTypeRepository;

        view()->share('type', 'staff_leave_type');

        $columns = ['title', 'max_days', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('staff_leave_type.staff_leave_types');

        return view('staff_leave_type.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('staff_leave_type.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(StaffLeaveTypeRequest $request)
    {
        $staffLeaveType = new StaffLeaveType($request->all());
        $staffLeaveType->company_id = session('current_company');
        $staffLeaveType->save();

        return redirect('/staff_leave_type');
    }

    /**
     * Display the specified resource.
     *
     * @param  StaffLeaveType $staffLeaveType
     * @return Response
     */
    public function show(StaffLeaveType $staffLeaveType)
    {
        $title = trans('staff_leave_type.details');
        $action = 'show';

        return view('layouts.show', compact('staffLeaveType', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StaffLeaveType $staffLeaveType
     * @return Response
     */
    public function edit(StaffLeaveType $staffLeaveType)
    {
        $title = trans('staff_leave_type.edit');

        return view('layouts.edit', compact('title', 'staffLeaveType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  StaffLeaveType $staffLeaveType
     * @return Response
     */
    public function update(StaffLeaveTypeRequest $request, StaffLeaveType $staffLeaveType)
    {
        $staffLeaveType->update($request->all());

        return redirect('/staff_leave_type');
    }

    public function delete(StaffLeaveType $staffLeaveType)
    {
        $title = trans('staff_leave_type.delete');

        return view('/staff_leave_type/delete', compact('staffLeaveType', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StaffLeaveType $staffLeaveType
     * @return Response
     */
    public function destroy(StaffLeaveType $staffLeaveType)
    {
        $staffLeaveType->delete();

        return redirect('/staff_leave_type');
    }

    public function data()
    {
        $staffLeaveTypes = $this->staffLeaveTypeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($staffLeaveType) {
                return [
                    'id' => $staffLeaveType->id,
                    'title' => $staffLeaveType->title,
                    'max_days' => $staffLeaveType->max_days,
                ];
            });

        return Datatables::make($staffLeaveTypes)
            ->addColumn('actions', '<a href="{{ url(\'/staff_leave_type/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/staff_leave_type/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/staff_leave_type/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
