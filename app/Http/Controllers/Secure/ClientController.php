<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\ClientRequest;

use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\ClientStatus;
use App\Models\Company;
use App\Models\District;
use App\Models\Region;
use App\Repositories\EmployeeRepository;
use App\Repositories\ClientRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Http\Request;

class ClientController extends SecureController
{
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * DirectionController constructor.
     *
     * @param EmployeeRepository $employeeRepository
     * @param ClientRepository $clientRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        ClientRepository $clientRepository,
    ) {
        $this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->clientRepository = $clientRepository;

        view()->share('type', 'client');
        view()->share('link', 'crm/client');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Clients';
        $clients = $this->clientRepository->getAll()->get();
        return view('client.index', compact('title', 'clients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Client';
        $clientCategories = ClientCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();

        $clientStatuses = ClientStatus::get()
            ->pluck('title', 'id')
            ->prepend('Select Status', 0)
            ->toArray();

        $regions = Region::get()
            ->pluck('title', 'id')
            ->prepend('Select Region', 0)
            ->toArray();


        $districts = District::get()
            ->pluck('title', 'id')
            ->prepend('Select District', 0)
            ->toArray();



        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Stake Holder', 0)
            ->toArray();


        return view('layouts.create', compact('title',  'employees', 'clientCategories', 'regions', 'districts', 'clientStatuses'));
    }


    public function modalCreate()
    {
        $title = 'New Client';
        $clientCategories = ClientCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();


        $clientStatuses = ClientStatus::get()
            ->pluck('title', 'id')
            ->prepend('Select Status', 0)
            ->toArray();



        $regions = Region::get()
            ->pluck('title', 'id')
            ->prepend('Select Region', 0)
            ->toArray();

        $districts = District::get()
            ->pluck('title', 'id')
            ->prepend('Select District', 0)
            ->toArray();



        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Stake Holder', 0)
            ->toArray();


        return view('client.modalForm', compact('title', 'employees', 'clientCategories', 'regions',  'districts', 'clientStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ClientRequest $request)
    {
        $validated = $request->validated();

        try
        {
            DB::transaction(function() use ($request, $validated) {

                $project = Client::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'company_id' => session('current_company'),
                                    'title' => $request['title'],
                                    'phone' => $request['phone'],
                                    'email' => $request['email'],
                                    'location' => $request['location'],
                                    'client_category_id' => $request['client_category_id'],
                                    'description' => $request['description'],
                                    'region_id' => $request['region_id'],
                                    'district_id' => $request['district_id'],
                                    'clientID' => Str::random(10),
                                    'client_status_id' => $request['client_status_id'],
                                ]
                            );


            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('Client Created Successfully') ;

    }

    /**
     * Display the specified resource.
     *
     * @param Client $client
     * @return Response
     */
    public function show(Client $client)
    {
        $title = $client->title;

        $action = 'show';

        return view('layouts.show', compact('client', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param LegalCase $legalCase
     * @return Response
     */
    public function edit(Client $client)
    {
        $title = 'Edit Project';

        $clientCategories = ClientCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();


        $clientStatuses = ClientStatus::get()
            ->pluck('title', 'id')
            ->prepend('Select Status', 0)
            ->toArray();



        $regions = Region::get()
            ->pluck('title', 'id')
            ->prepend('Select Region', 0)
            ->toArray();


        $districts = District::get()
            ->pluck('title', 'id')
            ->prepend('Select District', 0)
            ->toArray();



        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Stake Holder', 0)
            ->toArray();


        return view('layouts.edit', compact('title', 'client','employees', 'regions', 'districts', 'clientStatuses', 'clientCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LegalCase $legalCase
     * @param LegalCase $legalCase
     * @return Response
     */
    public function update(ClientRequest $request, Client $client)
    {
        $validated = $request->validated();
        try
        {
            DB::transaction(function() use ($client, $request, $validated) {
        $client->update($request->except('company_id', 'employee_id', 'project_components'));

                //STORE PROJECT COMPONENTS
                /*if ($validated['project_components'])
                {
                    foreach ($validated['project_components'] as $component)
                    {
                        if (!empty($component['project_artisan_id']))
                        {
                            $projectComponent = ProjectComponent::firstOrCreate
                            (
                                [
                                    'project_id' => $project->id,
                                    'project_artisan_id' => $component['project_artisan_id'],
                                    'description' => $component['description'],
                                    'cost' => $component['cost'],
                                ]
                            );

                        }
                    }

                }*/

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Client Updated Successfully') ;
    }

    public function delete(Project $project)
    {
        if ($project->comments->count() > 0)
            return response()->json(['exception'=>'Case has Comment associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($project) {
                $project->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Case Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return 'Project Deleted';


    }




    public function findRegionDistricts(Request $request)
    {
        $districts = District::where('region_id', $request->region_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select District', 0)
            ->toArray();
        return $districts;
    }



    public function latestCaseUpdates(Project $project)
    {
        return view('legalCase.updates', compact('project'));
    }




    public function addComment(ProjectCommentRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                if (!empty($request->caseUpdate))
                {
                    $comment = new ProjectComment();
                    $comment->project_id = $request->legal_case_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->caseUpdate;
                    $comment->save();

                }

            });

            //send email to stakeholders
            $legalCase = Project::find($request->project_id);
            foreach ($legalCase->stakeHolders as $employee)
            {
                if (GeneralHelper::validateEmail($employee->user->email))
                {
                    @Notification::send($employee->user, new LegalCaseUpdateNotification($employee->user, $legalCase));
                }
            }


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

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        $project = Project::find($request->legal_case_id);
        return view('project.updates', compact('project'));

    }

}
