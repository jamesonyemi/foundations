<?php

namespace App\Http\Controllers\Secure;

use App\Events\KpiCreated;
use App\Events\PostCommentCreatedEvent;
use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\KpiObjectiveRequest;
use App\Http\Requests\Secure\KraRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Models\BscPerspective;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\Kra;
use App\Models\Level;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\SectionRepository;
use App\Models\SchoolDirection;
use App\Repositories\KraRepository;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class KpiObjectiveController extends SecureController
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

        view()->share('type', 'kpi_objective');
        view()->share('link', 'performance_planning/kpi_objective');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kra.objective');
        $objectives= KpiObjective::whereHas('kra', function ($q) {
            $q->where('kras.company_year_id', session('current_company_year'));
        })->with('kpis')->get();
        return view('kpi_objective.index', compact('title', 'objectives'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kpi.new_kpi_objective');
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


        $kras = $this->kraRepository
            ->getAllForSchoolYearSchoolKpi(session('current_company'), session('current_company_year'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend('Select KRA', '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'kras'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KraRequest $request
     * @return Response
     */
    public function store(Request $request)
    {
        $employee = Employee::find(session('current_employee'));
        $kra_ids = $request['kra_id'];
        $titles = $request['title'];

        $rules = [];

        foreach($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
            $rules["kra_id.{$key}"] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {

            try
            {
                foreach ($kra_ids as $index => $kra_id)
                {
                    if (!empty($kra_id) )
                    {
                        $kpiObjective = new KpiObjective();
                        $kpiObjective->kra_id = $kra_id;
                        $kpiObjective->title = $titles[$index];
                        /*$kpiObjective->section_id = $employee->section_id;*/
                        $kpiObjective->created_employee_id =  session('current_employee');
                        $kpiObjective->company_year_id =  session('current_company_year');
                        $kpiObjective->save();
                    }
                }
            }

            catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }


            return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;


        }
        return response()->json(['error'=>$validator->errors()->all()]);

    }




    /**
     * Display the specified resource.
     *
     * @param KpiObjective $kpi_objective
     * @return Response
     */
    public function show(KpiObjective $kpi_objective)
    {
        $title = $kpi_objective->title .' '. trans('kra.objective_details');
        $action = 'show';

        return view('layouts.show', compact('kpi_objective', 'title', 'action'));
    }


    public function bscPerspectiveShow(BscPerspective $perspective)
    {
        $title = trans('kra.details');
        $action = 'show';
        return view('kpi_objective._bscPerspectiveDetails', compact('perspective', 'title', 'action'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param KpiObjective $kpi_objective
     * @return Response
     */
    public function edit(KpiObjective $kpi_objective)
    {
        $title = trans('kpi.edit_objective');

        $kras = $this->kraRepository
            ->getAllForSchoolYearSchoolKpi(session('current_company'), session('current_company_year'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend('Select KRA', '')
            ->toArray();


        return view('layouts.edit', compact('title', 'kpi_objective', 'kras'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KraRequest $request
     * @param KpiObjective $kpi_objective
     * @return Response
     */
    public function update(KpiObjectiveRequest $request, KpiObjective $kpi_objective)
    {
        try
        {
        $kpi_objective->update($request->all());

    }

catch (\Exception $e) {

return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
}


if ($kpi_objective->save())
{

    return response('<div class="alert alert-success">KRA Updated Successfully</div>') ;
}
else
{
    return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
}

    }

    public function delete(KpiObjective $kpi_objective)
    {
        $title = trans('kra.delete');

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



        $supervisors = Employee::find(session('current_employee'))->supervisors2
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Supervisor', 0)
            ->toArray();
        return view('kpi_objective.delete', compact('kpi_objective', 'title', 'employees', 'supervisors'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Kra $kra
     * @return Response
     */
    public function destroy(KpiObjective $kpi_objective)
    {
        $kpi_objective->delete();
    }



    public function approve(Request $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                $kpi = KpiObjective::find($request->kpi_objective_id);
                $kpi->approved = 1;
                $kpi->save();

                //Send email to the student approved
                /*$when = now()->addMinutes(3);
                Mail::to($student->user->email)
                    ->later($when, new StudentApproveMail($student));*/

            });

        } catch (\Exception $e) {
            return $e;

        }
        /*session(['student_id' => '']);*/
        return 'Kpi Approved';

    }

}
