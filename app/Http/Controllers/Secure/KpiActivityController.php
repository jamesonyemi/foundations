<?php

namespace App\Http\Controllers\Secure;

use App\Events\KpiActivityApprovedEvent;
use App\Events\KpiActivityCreatedEvent;
use App\Events\KpiApprovedEvent;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\KpiActivityCommentRequest;
use App\Http\Requests\Secure\KpiActivityRequest;
use App\Http\Requests\Secure\KpiActivityRequest2;
use App\Http\Requests\Secure\KpiActivityRequest3;
use App\Http\Requests\Secure\KpiActivityUpdateRequest;
use App\Http\Requests\Secure\KpiCommentRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\CompetencyEmployeeResource;
use App\Http\Resources\KpiPerformanceResource;
use App\Models\Article;
use App\Models\BscPerspective;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\EmployeeKpiActivity;
use App\Models\KpiActivityComment;
use App\Models\KpiActivityDocument;
use App\Models\KpiActivityStatus;
use App\Models\KpiComment;
use App\Models\KpiObjective;
use App\Models\KpiPerformance;
use App\Models\KpiResponsibility;
use App\Models\KpiTimeline;
use App\Models\Level;
use App\Models\PerspectiveWeight;
use App\Models\SupplierDocument;
use App\Notifications\KpiActivityCommentEmployeeNotification;
use App\Notifications\KpiActivityCommentSupervisorNotification;
use App\Notifications\KpiCommentResponsibleEmployeesEmail;
use App\Notifications\KpiCommentSupervisorEmail;
use App\Notifications\KpiSelfReviewSupervisorNotification;
use App\Repositories\KraRepository;
use App\Repositories\EmployeeRepository;
use App\Models\SchoolDirection;
use App\Repositories\KpiRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Http\Resources\BscPerspectives;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Validator;

class KpiActivityController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;
    /**
     * @var KpiRepository
     */
    private $kpiRepository;
    /**
     * @var KraRepository
     */
    private $kraRepository;

    /**
     * DirectionController constructor.
     *
     * @param KpiRepository $kpiRepository
     * @param EmployeeRepository $employeeRepository
     * @param KraRepository $kraRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        KpiRepository $kpiRepository,
        KraRepository $kraRepository
    ) {

        parent::__construct();

        $this->employeeRepository = $employeeRepository;
        $this->kpiRepository = $kpiRepository;
        $this->kraRepository = $kraRepository;

        view()->share('type', 'kpi_activity');
        view()->share('link', 'kpi_activity');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kpi.kpis_activity');

        $activities= EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'));
        })->get();
        return view('kpi_activity.index', compact('title',  'activities'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kpi.new_activity');

        $kpis= KpiResponsibility::whereHas('kpi')->where('responsible_employee_id', session('current_employee'))->orWhere('owner_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->with('kpi', 'responsibilities')->get()->unique('kpi_id')
            ->pluck('kpi.full_title', 'kpi.id')
            ->prepend('Select Kpi', '')
            ->toArray();;


        return view('layouts.create', compact('title', 'kpis'));
    }


    public function createModal()
    {
        $title = trans('kpi.new_activity');

        $kpis= KpiResponsibility::whereHas('kpi')->where('responsible_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->with('kpi', 'responsibilities')->get()->unique('kpi_id')
            ->map(function ($item) {
                return [
                    "id"   => $item->kpi_id,
                    "name" => $item->kpi->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select KPI', '')
            ->toArray();

        $kpiStatus = KpiActivityStatus::all()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Status', '')
            ->toArray();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Employee', 0)
            ->toArray();


        $subsidiaries = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();



        return view('kpi_performance._createActivityModalExecute', compact('title', 'kpis', 'kpiStatus', 'employees', 'subsidiaries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(KpiActivityRequest2 $request)
    {
                try
                {
                    $kpiActivity = new EmployeeKpiActivity();
                    $kpiActivity->kpi_id = $request->kpi_id;
                    $kpiActivity->employee_id = session('current_employee');
                    $kpiActivity->title = $request->title;
                    $kpiActivity->due_date = $request->due_date;
                    $kpiActivity->kpi_activity_status_id = 1;
                    $kpiActivity->save();

                    /*event(new KpiActivityCreatedEvent($kpiActivity));*/
                }

                catch (\Exception $e) {
                    return response()->json(['exception'=>$e->getMessage()]);
                }


                    return response('<div class="alert alert-success">KPI Activity Successfully</div>') ;




    }

    public function storeExecuteCreate(KpiActivityRequest3 $request)
    {
        $validated = $request->validated();
        if ($request->kpi_activity_status_id == 3 AND $request->comment == '<p><br></p>')
        {
            return response()->json(['exception'=>'Kindly provide activity report']);
        }
                try
                {
                    $kpi_activity = new EmployeeKpiActivity();
                    $kpi_activity->kpi_id = $request->kpi_id;
                    $kpi_activity->employee_id = session('current_employee');
                    $kpi_activity->employee_for_id = $request->employee_for_id;
                    $kpi_activity->title = $request->title;
                    $kpi_activity->comment = $request->comment;
                    $kpi_activity->due_date = $request->due_date;
                    $kpi_activity->kpi_activity_status_id = $request->kpi_activity_status_id;
                    $kpi_activity->save();



                    //STORE ADDITIONAL DOCUMENTS
                    foreach ($validated['kt_docs_repeater_basic'] as $upload)
                    {
                        if (!empty($upload['document_title']) AND !empty($upload['file']))
                        {
                            $file = $upload['file'];
                            $extension = $file->getClientOriginalExtension();
                            $document = Str::random(8) . '.' . $extension;
                            $destinationPath = public_path().'/uploads/documents/';
                            $file->move($destinationPath, $document);
                            $kpiActivityDocument = new KpiActivityDocument();
                            $kpiActivityDocument->employee_kpi_activity_id = $kpi_activity->id;
                            $kpiActivityDocument->document_title = $upload['document_title'];
                            $kpiActivityDocument->file = $document;
                            $kpiActivityDocument->save();

                        }
                    }

                    $kpi_activity->companies()->attach($request->input('company_id'));
                    /*event(new KpiActivityCreatedEvent($kpiActivity));*/
                }

                catch (\Exception $e) {
                    return response()->json(['exception'=>$e->getMessage()]);
                }


                    return response('<div class="alert alert-success">KPI Activity Successfully</div>') ;
    }


    public function calPerspectivePercentage(KpiRequest $request)
    {
        $weights = $request['weight'];

        $percentage=array_sum($weights);
        if ($percentage<=100)
        return response('<div class="alert alert-success">'.$percentage.'</div>') ;
        else
        return response('<div class="alert alert-danger">'.$percentage.'</div>') ;

    }



    public function calculateWeights(Request $request)
    {
        $weights = $request['weight'];

        $percentage=array_sum($weights);
        if ($percentage<=5)
            return response('<div class="alert alert-success">'.$percentage.'</div>') ;
        else
            return response('<div class="alert alert-danger">'.$percentage.'</div>') ;

    }


    /**
     * Display the specified resource.
     *
     * @param EmployeeKpiActivity $kpi_activity
     * @return Response
     */
    public function show(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity_details');
        $action = 'show';
        return view('layouts.show', compact('kpi_activity', 'title', 'action'));
    }

    public function modalShow(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.kpis_activity_details');
        $action = 'show';
        return view('kpi_activity.modalShow', compact('kpi_activity', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EmployeeKpiActivity $kpi_activity
     * @return Response
     */
    public function edit(EmployeeKpiActivity $kpi_activity)
    {
        $title = trans('kpi.edit_kpi_activity');

        /*$employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Responsibility', 0)
            ->toArray();*/

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Employee', '')
            ->toArray();

        $subsidiaries = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();

        $company_daily_Activity = $kpi_activity->companyIds()
            ->pluck('company_id')
            ->toArray();


        $kpis= KpiResponsibility::whereHas('kpi')->where('responsible_employee_id', session('current_employee'))->orWhere('owner_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kras.company_year_id', session('current_company_year'));
        })->with('kpi', 'responsibilities')->get()->unique('kpi_id')
            ->pluck('kpi.full_title', 'kpi.id')
            ->prepend('Select Kpi', '')
            ->toArray();;

        $kpiObjectives = Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->/*where('kras.company_id', session('current_company'))
                ->*/where('kras.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi', '')
            ->toArray();

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'kpi_activity', 'kpiObjectives','employees', 'kpitimelines', 'kpis', 'subsidiaries', 'company_daily_Activity'));
    }




    public function latestKpiActivityComments(EmployeeKpiActivity $employeeKpiActivity)
    {
        return view('kpi_activity.kpi_activity_comments', compact('employeeKpiActivity'));
    }






    public function addActivityComment(KpiActivityCommentRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                if (!empty($request->activityComment))
                {
                    $comment = new KpiActivityComment();
                    $comment->employee_kpi_activity_id = $request->employee_kpi_activity_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->activityComment;
                    $comment->save();

                    //send email to supervisors
                    /*foreach (Kpi::find($comment->employee_kpi_activity->kpi_id)->kpiSupervisors as $supervisor)
                    {
                        $when = now()->addMinutes(1);
                        if (GeneralHelper::validateEmail($supervisor->user->email))
                        {
                            @Notification::send($supervisor->employee->user, new KpiActivityCommentSupervisorNotification($supervisor->employee->user, $comment, $comment->employee));
                        }
                    }*/

                   //send email to activity owner
                    if ($comment->employee_kpi_activity->employee_id != session('current_employee'))
                    {
                        $when = now()->addMinutes(1);
                        if (GeneralHelper::validateEmail($comment->employee_kpi_activity->employee->user->email))
                        {
                            @Notification::send($comment->employee_kpi_activity->employee->user, new KpiActivityCommentEmployeeNotification($comment->employee_kpi_activity->employee->user, $comment, $comment->employee));
                        }
                    }


                }

            });



            //Send email to responsible employees
            /*foreach (Kpi::find($request->kpi_id)->kpiResponsibleEmployees as $kpiResponsibleEmployee)
            {
                if (GeneralHelper::validateEmail($kpiResponsibleEmployee->user->email))
                {
                    $when = now()->addMinutes(1);
                    Mail::to($kpiResponsibleEmployee->user->email)
                        ->later($when, new KpiCommentResponsibleEmployeesEmail($kpiResponsibleEmployee->user));
                }
            }*/

        }


        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }



        $employeeKpiActivity = EmployeeKpiActivity::find($request->employee_kpi_activity_id);
        return view('kpi_activity.kpi_activity_comments', compact('employeeKpiActivity'));

    }



    public function modalEdit(EmployeeKpiActivity $kpi_activity, KpiResponsibility $kpiResponsibility, )
    {
        $title = trans('kpi.edit_kpi_activity');

        return view('kpi_activity._modalForm', compact('title', 'kpi_activity', 'kpiResponsibility'));
    }




    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiActivityRequest $request
     * @param EmployeeKpiActivity $kpi_activity
     * @return Response
     */
    public function update(KpiActivityUpdateRequest $request, EmployeeKpiActivity $kpi_activity)
    {
        $validated = $request->validated();
        if ($request->kpi_activity_status_id == 3 AND $request->comment == '<p><br></p>')
        {
            return response()->json(['exception'=>'Kindly provide activity report']);
        }
        try
        {
            $kpi_activity->update($request->all());
            $kpi_activity->employee_id = session('current_employee');
            if ($request->hasFile('document') != "") {
                $file = $request->file('document');
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) . '.' . $extension;

                $destinationPath = public_path() . '/uploads/documents/';
                $file->move($destinationPath, $document);

                $kpi_activity->document = $document;
            }
            $kpi_activity->save();

            $kpi_activity->companies()->sync($request->company_id);

         //STORE ADDITIONAL DOCUMENTS
            foreach ($validated['kt_docs_repeater_basic'] as $upload)
            {
                if (!empty($upload['document_title']) AND !empty($upload['file']))
                {
                    $file = $upload['file'];
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) . '.' . $extension;
                    $destinationPath = public_path().'/uploads/documents/';
                    $file->move($destinationPath, $document);
                    $kpiActivityDocument = new KpiActivityDocument();
                    $kpiActivityDocument->employee_kpi_activity_id = $kpi_activity->id;
                    $kpiActivityDocument->document_title = $upload['document_title'];
                    $kpiActivityDocument->file = $document;
                    $kpiActivityDocument->save();

                }
            }

        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }


        $kpiResponsibility = KpiResponsibility::where('kpi_id', $request->kpi_id)->first();
        $KpiActivities = Kpi::find($request->kpi_id)->kpiActivities->where('employee_id', session('current_employee'));
        return view('kpi.activities', compact('KpiActivities', 'kpiResponsibility'));


    }

    public function delete(EmployeeKpiActivity $kpi_activity)
    {
        $kpi = Kpi::find($kpi_activity->kpi_id);
        $kpiResponsibility = KpiResponsibility::where('kpi_id', $kpi_activity->kpi_id)->first();
        $kpi_activity->delete();
        $KpiActivities = $kpi->kpiActivities->where('employee_id', session('current_employee'));
        return view('kpi.activities', compact('kpi',  'KpiActivities', 'kpiResponsibility'));
    }




    public function deleteActivityDocument(KpiActivityDocument $kpi_activity_document)
    {
        File::delete(public_path().'/uploads/documents/'.$kpi_activity_document->file);
        $kpi_activity_document->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  EmployeeKpiActivity $kpi_activity
     * @return Response
     */
    public function destroy(EmployeeKpiActivity $kpi_activity)
    {
        $kpi_activity->delete();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        DB::table("products")->whereIn('id',explode(",",$ids))->delete();
        return response()->json(['success'=>"Products Deleted successfully."]);
    }




    public function EmailApprove(EmployeeKpiActivity $kpi_activity)
    {
        if ($kpi_activity->kpi->supervisor_employee_id != session('current_employee'))
        {
            Flash::error('You are not authorized to approve this kpi activity');
            return redirect('/');
        }

        if ($kpi_activity->approved == 1)
        {
            Flash::warning('KPI Activity Already Approved');
            return redirect('/');
        }
        try
        {
            DB::transaction(function() use ($kpi_activity) {
                $kpi_activity->approved = 1;
                $kpi_activity->approved_date = now();
                $kpi_activity->save();

            });

        } catch (\Exception $e) {
            Flash::error('KPI Activity not found');
            return redirect('/');

        }
        Flash::success('KPI Activity '.$kpi_activity->title. ' Approved Successfully');
        /*event(new KpiActivityApprovedEvent($kpi_activity));*/
        return redirect('/');

    }


    public function data()
    {
        return new CompetencyEmployeeResource($this->employeeRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'programme')
            ->get());
    }


    public function findSectionLevel(Request $request)
    {
        $directions = $this->levelRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();
        return $directions;
    }


/*    public static function perspectives()
    {
        $data = [];

        $bscPerspectives = BscPerspective::where('company_id', session('current_company'))->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->kras as $kra) {

                array_push($orders, [
                        "RecordID" => $kra->id,
                        "OrderID" => $kra->title,
                        "Country" => $kra->title,
                        "ShipCountry" => $kra->title,
                        "ShipCity" => $kra->title,
                        "ShipName" => $kra->title,
                        "ShipAddress" => $kra->title,
                        "CompanyAgent" => $kra->title,
                        "Status" => 5,
                        "Type" => 1,
                    ]
                );
            }

            array_push($data, [
                "data" => $orders,


            ]
            );


        }
        return json_encode($data, JSON_UNESCAPED_SLASHES);

    }*/




    public static function perspectives()
    {
        return new BscPerspectives(BscPerspective::where('company_id', session('current_company'))->get());

    }


    public function kpiActivities()
    {



        $activities = EmployeeKpiActivity::where('employee_id', '=', session('current_employee'))->whereHas('kpi.kpiResponsibilities', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpi_responsibilities.responsible_employee_id', session('current_employee'));
        })->get();


        return view('dashboard._todolist', compact('activities'));
    }

}
