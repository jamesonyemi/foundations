<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\QualificationRequest;
use App\Models\EmployeeCompetencyMatrix;
use App\Models\EmployeeQualification;
use App\Models\Qualification;
use App\Repositories\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualificationController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    public function __construct(

        EmployeeRepository $employeeRepository
    ) {
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        view()->share('type', 'qualification');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('qualification.qualification');
        $qualifications = Qualification::get();

        return view('qualification.index', compact('title', 'qualifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('qualification.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QualificationRequest|Request $request
     * @return Response
     */
    public function store(QualificationRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $qualification = new Qualification($request->all());
                $qualification->company_id = session('current_company');
                $qualification->save();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Qualification Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param Qualification $qualification
     * @return Response
     * @internal param int $id
     */
    public function show(Qualification $qualification)
    {
        $title = trans('qualification.details');
        $action = 'show';

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        $employee_qualifications = EmployeeQualification::where('qualification_id', $qualification->id)
            ->get()
            ->pluck('employee_id', 'employee_id')
            ->toArray();

        return view('layouts.show', compact('title', 'qualification', 'action', 'employees', 'employee_qualifications'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Qualification $qualification
     * @return Response
     */
    public function edit(Qualification $qualification)
    {
        $title = trans('qualification.edit');

        return view('layouts.edit', compact('title', 'qualification'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Qualification|Request $request
     * @param Qualification $qualification
     * @return Response
     */
    public function update(QualificationRequest $request, Qualification $qualification)
    {
        try {
            DB::transaction(function () use ($request, $qualification) {
                $qualification->update($request->all());
                $qualification->save();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Qualification Updated Successfully</div>');
    }

    public function delete(Qualification $qualification)
    {
        $title = trans('qualification.delete');

        return view('qualification.delete', compact('title', 'qualification'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Qualification $qualification
     * @return Response
     */
    public function destroy(Qualification $qualification)
    {
        { try {
            DB::transaction(function () use ($qualification) {
                $qualification->delete();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Qualification Deleted Successfully</div>');

    }
    }

    public function data()
    {
        $qualifications = Qualification::get()
            ->map(function ($qualification) {
                return [
                    'id' => $qualification->id,
                    'title' => $qualification->title,
                ];
            });

        return Datatables::make($qualifications)
            ->addColumn('actions', '<a href="{{ url(\'/qualification/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/qualification/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/qualification/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function addEmployees(Request $request)
    {
        $qualification_id = $request['qualification_id'];
        $employee_ids = $request['employee_id'];

        try {
            foreach ($employee_ids as $index => $employee_id) {
                EmployeeQualification::firstOrCreate(
                    [
                        'qualification_id' => $qualification_id,
                        'employee_id' => $employee_id,
                        'company_year_id' => session('current_company_year'),
                    ]);
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employees Added Successfully</div>');

        /* END OF ONE*/
    }
}
