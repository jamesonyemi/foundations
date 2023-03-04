<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\KpiCommentRequest;
use App\Http\Requests\Secure\LegalCaseRequest;
use App\Http\Requests\Secure\LegalCaseUpdateRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Models\Company;
use App\Models\Kpi;
use App\Models\KpiComment;
use App\Models\LegalCase;
use App\Models\LegalCaseCategory;
use App\Models\LegalCaseComment;
use App\Models\LegalFirm;
use App\Models\ProcurementCategory;
use App\Models\ProcurementMasterCategory;
use App\Models\Supplier;
use App\Notifications\KpiCommentResponsibleEmployeesEmail;
use App\Notifications\KpiCommentSupervisorEmail;
use App\Notifications\LegalCaseUpdateNotification;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Repositories\EmployeeRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Validator;
use Illuminate\Http\Request;

class LegalCaseController extends SecureController
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
        $this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'legalCase');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.categories');
        $legalCases = LegalCase::with(['legalCaseCategory', 'legalFirm'])->get();
        return view('legalCase.index', compact('title', 'legalCases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('procurement.new_category');
        $legalCaseCategories = LegalCaseCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();

        $legalFirms = LegalFirm::get()
            ->pluck('title', 'id')
            ->prepend('Select Legal Firm', '')
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


        return view('layouts.create', compact('title', 'legalFirms', 'legalCaseCategories', 'companies', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(LegalCaseRequest $request)
    {


        try
        {
            DB::transaction(function() use ($request) {

                $legalCase = LegalCase::firstOrCreate
                            (
                                [
                                    'employee_id' => session('current_employee'),
                                    'title' => $request['title'],
                                    'legal_case_category_id' => $request['legal_case_category_id'],
                                    'legal_firm_id' => $request['legal_firm_id'],
                                    'case_number' => $request['case_number'],
                                    'suite_number' => $request['suite_number'],
                                    'plaintif' => $request['plaintif'],
                                    'defendants' => $request['defendants'],
                                    'description' => $request['description'],
                                    'status' => 0,
                                ]
                            );

                $legalCase->companies()->attach($request->input('company_id'));
                $legalCase->stakeHolders()->attach($request->input('employee_id'));

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Case Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param LegalCase $legalCase
     * @return Response
     */
    public function show(LegalCase $legalCase)
    {
        $title = trans('procurement.category');

        $action = 'show';

        return view('layouts.show', compact('legalCase', 'title', 'action'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param LegalCase $legalCase
     * @return Response
     */
    public function edit(LegalCase $legalCase)
    {
        $title = trans('procurement.edit_category');

        $legalCaseCategories = LegalCaseCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select Category', 0)
            ->toArray();

        $legalFirms = LegalFirm::get()
            ->pluck('title', 'id')
            ->prepend('Select Legal Firm', '')
            ->toArray();

        $companies = Company::where('active', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();

        $company_legal_cases = $legalCase->companyIds()
            ->pluck('company_id')
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

        $legal_case_stakeholders = $legalCase->stakeHolderIds()
            ->pluck('employee_id')
            ->toArray();


        return view('layouts.edit', compact('title', 'legalCase', 'legalCaseCategories', 'legalFirms', 'companies', 'company_legal_cases', 'employees', 'legal_case_stakeholders'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LegalCase $legalCase
     * @param LegalCase $legalCase
     * @return Response
     */
    public function update(LegalCaseRequest $request, LegalCase $legalCase)
    {
        try
        {
            DB::transaction(function() use ($legalCase, $request) {
        $legalCase->update($request->except('company_id', 'employee_id'));
        $legalCase->companies()->sync($request->company_id);
        $legalCase->stakeHolders()->sync($request->employee_id);

            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }
        return response('Case Updated Successfully') ;
    }

    public function delete(LegalCase $legalCase)
    {
        if ($legalCase->comments->count() > 0)
            return response()->json(['exception'=>'Case has Comment associations and cannot be deleted']);

        try
        {
            DB::transaction(function() use ($legalCase) {
                $legalCase->delete();
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
    public function destroy(LegalCase $legalCase)
    {
        $legalCase->delete();
        return 'Legal Case Deleted';


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



    public function latestCaseUpdates(LegalCase $legalCase)
    {
        return view('legalCase.updates', compact('legalCase'));
    }




    public function addComment(LegalCaseUpdateRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                if (!empty($request->caseUpdate))
                {
                    $comment = new LegalCaseComment();
                    $comment->legal_case_id = $request->legal_case_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->caseUpdate;
                    $comment->save();

                }

            });

            //send email to stakeholders
            $legalCase = LegalCase::find($request->legal_case_id);
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



        $legalCase = LegalCase::find($request->legal_case_id);
        return view('legalCase.updates', compact('legalCase'));

    }

}
