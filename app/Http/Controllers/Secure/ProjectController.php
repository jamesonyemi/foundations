<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\KpiCommentRequest;
use App\Http\Requests\Secure\LegalCaseRequest;
use App\Http\Requests\Secure\LegalCaseUpdateRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Http\Requests\Secure\ProjectCommentRequest;
use App\Http\Requests\Secure\ProjectRequest;
use App\Models\Company;
use App\Models\Kpi;
use App\Models\KpiActivityDocument;
use App\Models\KpiComment;
use App\Models\LegalCase;
use App\Models\LegalCaseCategory;
use App\Models\LegalCaseComment;
use App\Models\LegalFirm;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Project;
use App\Models\ProjectArtisan;
use App\Models\ProjectCategory;
use App\Models\ProjectComment;
use App\Models\ProjectComponent;
use App\Models\ProjectStatus;
use App\Models\StudyMaterial;
use App\Models\Supplier;
use App\Notifications\KpiCommentResponsibleEmployeesEmail;
use App\Notifications\KpiCommentSupervisorEmail;
use App\Notifications\LegalCaseUpdateNotification;
use App\Notifications\ProjectApproveNotification;
use App\Notifications\ProjectRejectNotification;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\EmployeeRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Http\Request;

class ProjectController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * DirectionController constructor.
     *
     * @param EmployeeRepository $employeeRepository
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {
        /*$this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);*/
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'project');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('project.projects');
        $projects = Project::get();
        return view('project.index', compact('title', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('project.new');

        return view('project._form', compact('title'));
    }

    public function modalCreate()
    {
        $title = trans('project.new');
        $projectCategories = ProjectCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();

        $projectStatuses = ProjectStatus::get()
            ->pluck('title', 'id')
            ->prepend('Select Project Status', 0)
            ->toArray();

        $artisans = ProjectArtisan::get()
            ->pluck('title', 'id')
            ->prepend('Select Artisan', '')
            ->toArray();

        $companies = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
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


        return view('project.modalForm', compact('title', 'artisans', 'projectCategories', 'companies', 'employees', 'projectStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ProjectRequest $request)
    {

        $project = new Project();
        if ($request->hasFile('header_report') != '') {
            $file = $request->file('header_report');
            $extension = $file->getClientOriginalExtension();
            $header_report = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $header_report);
            $project->header_report = $header_report;
        }

        if ($request->hasFile('mel_template') != '') {
            $file = $request->file('mel_template');
            $extension = $file->getClientOriginalExtension();
            $mel_template = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $mel_template);
            $project->mel_template = $mel_template;
        }

        if ($request->hasFile('financial_report') != '') {
            $file = $request->file('financial_report');
            $extension = $file->getClientOriginalExtension();
            $financial_report = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $financial_report);
            $project->financial_report = $financial_report;
        }

        if ($request->hasFile('nq_work_plan') != '') {
            $file = $request->file('nq_work_plan');
            $extension = $file->getClientOriginalExtension();
            $nq_work_plan = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $nq_work_plan);
            $project->nq_work_plan = $nq_work_plan;
        }

        if ($request->hasFile('file_file') != '') {
            $file = $request->file('file_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $picture);
            $project->image = $picture;
        }

        if ($request->hasFile('nq_budget') != '') {
            $file = $request->file('nq_budget');
            $extension = $file->getClientOriginalExtension();
            $nq_budget = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $nq_budget);
            $project->nq_budget = $nq_budget;
        }

        if ($request->hasFile('human_interest') != '') {
            $file = $request->file('human_interest');
            $extension = $file->getClientOriginalExtension();
            $human_interest = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/foundation/';
            $file->move($destinationPath, $human_interest);
            $project->human_interest = $human_interest;
        }


        $project->employee_id = session('current_employee');
        $project->company_id = session('current_company');
        $project->uploadByname = $request->uploadByname;
        $project->article_title = $request->article_title;
        $project->article_url = $request->article_url;
        $project->save();

        Flash::error('Upload Successfull');

        return redirect()->guest('/');

    }

    /**
     * Display the specified resource.
     *
     * @param Project $project
     * @return Response
     */
    public function show(Project $project)
    {
        $title = trans('project.project');

        $action = 'show';

        return view('project.show', compact('project', 'title', 'action'));
    }



   public function approve(Project $project)
    {
        $project->approval = 1;
        $project->save();

        if (GeneralHelper::validateEmail($project->employee->user->email)) {
            @Notification::send($project->employee->user, new ProjectApproveNotification($project->employee->user, $project));
        }

        Flash::error('Project Report Approved');

        return redirect()->guest('/');
    }


   public function reject(Project $project)
    {
        $project->approval = 0;
        $project->save();

        if (GeneralHelper::validateEmail($project->employee->user->email)) {
            @Notification::send($project->employee->user, new ProjectRejectNotification($project->employee->user, $project));
        }


        Flash::error('Project Report Rejected');

        return redirect()->guest('/');
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param LegalCase $legalCase
     * @return Response
     */
    public function edit(Project $project)
    {
        $title = 'Edit Project';

        $projectCategories = ProjectCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();

        $projectStatuses = ProjectStatus::get()
            ->pluck('title', 'id')
            ->prepend('Select Project Status', 0)
            ->toArray();

        $artisans = ProjectArtisan::get()
            ->pluck('title', 'id')
            ->prepend('Select Artisan', '')
            ->toArray();

        $companies = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
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

        $project_companies = $project->companyIds()
            ->pluck('company_id')
            ->toArray();

        $project_stakeholders = $project->stakeHolderIds()
            ->pluck('employee_id')
            ->toArray();


        return view('layouts.edit', compact('title', 'project', 'projectCategories', 'artisans', 'companies', 'employees', 'projectStatuses', 'project_companies', 'project_stakeholders'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LegalCase $legalCase
     * @param LegalCase $legalCase
     * @return Response
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $validated = $request->validated();
        try
        {
            DB::transaction(function() use ($project, $request, $validated) {
        $project->update($request->except('company_id', 'employee_id', 'project_components'));
        $project->companies()->sync($request->company_id);
        $project->stakeHolders()->sync($request->employee_id);

                //STORE PROJECT COMPONENTS
                if ($validated['project_components'])
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

                }

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Project Updated Successfully') ;
    }

    public function delete(Project $project)
    {


        try
        {
            DB::transaction(function() use ($project) {
                $project->delete();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return redirect('/');
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




    public function findProcurementCategory(Request $request)
    {
        $categories = ProcurementCategory::where('procurement_master_category_id', $request->procurement_master_category_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();
        return $categories;
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
