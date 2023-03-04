<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Http\Requests\Secure\KraRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Models\BscPerspective;
use App\Models\Holiday;
use App\Models\Kpi;
use App\Models\Kra;
use App\Models\Level;
use App\Repositories\EmployeeRepository;
use App\Repositories\SectionRepository;
use App\Models\SchoolDirection;
use App\Repositories\KraRepository;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Validator;
use DataTables;

class KraController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;
    /**
     * @var KraRepository
     */
    private $kraRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * DirectionController constructor.
     *
     * @param KraRepository $kraRepository
     * @param SectionRepository $sectionRepository
     * @param EmployeeRepository $employeeRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        KraRepository $kraRepository,
        EmployeeRepository $employeeRepository,
        SectionRepository $sectionRepository
    ) {

        parent::__construct();

        $this->kraRepository = $kraRepository;
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'kra');
        view()->share('link', 'performance_planning/kra');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kra.kras');
        $kras= $this->kraRepository->getAllForSchool(session('current_company'))
            ->with('kpiObjectives')
            ->get();

        return view('kra.index', compact('title', 'kras'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kra.new');
        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Responsibility', 0)
            ->toArray();


        $bscPerspectives= BscPerspective::get()
            ->pluck('title', 'id')
            ->prepend('Select Perspective', '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'bscPerspectives'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KraRequest $request
     * @return Response
     */
    public function store(Request $request)
    {
        $bsc_perspective_ids = $request['bsc_perspective_id'];
        $titles = $request['title'];

        $rules = [];

        foreach($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
            $rules["bsc_perspective_id.{$key}"] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);


        if ($validator->passes()) {

            try
            {
                foreach ($bsc_perspective_ids as $index => $bsc_perspective_id)
                {
                    if (!empty($bsc_perspective_id) )
                    {
                        $kra = new Kra();
                        $kra->bsc_perspective_id = $bsc_perspective_id;
                        $kra->title = $titles[$index];
                        $kra->company_id = session('current_company');
                        $kra->company_year_id = session('current_company_year');
                        $kra->created_employee_id = session('current_employee');
                        if(session('current_company_sector') > 0)
                        {
                            $kra->sector_id = session('current_company_sector') ;
                        }

                        $kra->save();
                    }
                }
            }

            catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }


            return response('<div class="alert alert-success">KRA CREATED Successfully</div>') ;


        }
        return response()->json(['error'=>$validator->errors()->all()]);

    }

    /**
     * Display the specified resource.
     *
     * @param Kra $kra
     * @return Response
     */
    public function show(Kra $kra)
    {
        $title = trans('kra.details');
        $action = 'show';
        return view('layouts.show', compact('kra', 'title', 'action'));
    }



    public function data(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->kraRepository->getAllForSchool(session('current_company'))
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){

                    $btn = '<a href="javascript:;"  onclick="showRecord('.$row->id.')" class="btn btn-sm btn-clean btn-icon" title="Show Details">
	                             <i class="fa fa-eye text-primary mr-5"></i>

	                             <a href="javascript:;"  onclick="Edit('.$row->id.')" class="btn btn-sm btn-clean btn-icon" title="Show Details">
	                             <i class="fa fa-pencil-ruler text-warning mr-5"></i>
	                             <a href="javascript:;"  onclick="Delete('.$row->id.')" class="btn btn-sm btn-clean btn-icon" title="Show Details">
	                             <i class="fa fa-trash text-danger mr-5"></i>

	                             ';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $title = trans('kra.kras');
        return view('kra.index', compact('title'));
    }



    public function bscPerspectiveShow(BscPerspective $perspective)
    {
        $title = trans('kra.details');
        $action = 'show';
        return view('kra._bscPerspectiveDetails', compact('perspective', 'title', 'action'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Kra $kra
     * @return Response
     */
    public function edit(Kra $kra)
    {
        $title = trans('kra.edit');

        $bscPerspectives= BscPerspective::get()
            ->pluck('title', 'id')
            ->prepend('Select Department', '')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'kra', 'sections', 'bscPerspectives'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KraRequest $request
     * @param Kra $kra
     * @return Response
     */
    public function update(KraRequest $request, Kra $kra)
    {
        try
        {
        $kra->update($request->all());

    }

catch (\Exception $e) {

return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
}


if ($kra->save())
{

    return response('<div class="alert alert-success">KRA Updated Successfully</div>') ;
}
else
{
    return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
}

    }

    public function delete(Kra $kra)
    {
        $title = trans('kra.delete');
        return view('kra.delete', compact('kra', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Kra $kra
     * @return Response
     */
    public function destroy(Kra $kra)
    {
        $kra->delete();
    }

}
