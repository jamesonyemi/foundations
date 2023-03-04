<?php

namespace App\Http\Controllers\Secure;

use App\Events\KpiActivityCreatedEvent;
use App\Events\KpiApprovedEvent;
use App\Events\KpiCreated;
use App\Events\PostCommentCreatedEvent;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\ArticleCommentRequest;
use App\Http\Requests\Secure\bscWizardRequest;
use App\Http\Requests\Secure\KpiActivityRequest;
use App\Http\Requests\Secure\KpiCommentRequest;
use App\Http\Requests\Secure\KpiPerformanceReviewRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\KpiScoreRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\UpdateKpiWeightRequest;
use App\Http\Requests\Secure\WeightRequest;
use App\Mail\StudentApproveMail;
use App\Models\Article;
use App\Models\BscPerspective;
use App\Models\Employee;
use App\Models\EmployeeKpiPerspectiveScore;
use App\Models\EmployeeKpiScore;
use App\Models\EmployeeKpiSignOff;
use App\Models\EmployeeKpiTimeline;
use App\Models\EmployeeKpiTimelineSignOff;
use App\Models\EmployeeYearGrade;
use App\Models\Kpi;
use App\Models\EmployeeKpiActivity;
use App\Models\KpiComment;
use App\Models\KpiObjective;
use App\Models\KpiPerformanceReview;
use App\Models\KpiResponsibility;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Level;
use App\Models\PerformanceScoreGrade;
use App\Models\PerspectiveWeight;
use App\Models\CompanyYear;
use App\Models\ScoreCard;
use App\Models\Department;
use App\Models\User;
use App\Notifications\approveBscSignOffEmail;
use App\Notifications\ApproveSelfBscSignOffEmail;
use App\Notifications\CascadeKpiNotification;
use App\Notifications\ConferenceInvitationNotification;
use App\Notifications\DeleteKpiNotification;
use App\Notifications\KpiActivityDueDateNotification;
use App\Notifications\KpiCommentResponsibleEmployeesEmail;
use App\Notifications\KpiCommentSupervisorEmail;
use App\Notifications\KpiReviewNotification;
use App\Notifications\KpiSelfReviewSupervisorNotification;
use App\Notifications\OpenBscSignOffEmail;
use App\Notifications\OpenSelfBscSignOffEmail;
use App\Notifications\PostCommentCreatedEmail;
use App\Notifications\SendBscSignOffEmail;
use App\Notifications\SendEmail;
use App\Notifications\SendSelfBscSignOffEmail;
use App\Repositories\KraRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Helpers\Settings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Resources\BscPerspectives;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Validator;
use Sentinel;
use function PHPUnit\Framework\isEmpty;

class KpiController extends SecureController
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

        view()->share('type', 'kpi');
        view()->share('link', 'performance_planning/kpi');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kpi.kpis');


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $bscPerspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();


        return view('kpi.index', compact('title', 'bscPerspectives', 'kpitimelines'));
    }

  public function bsc_score_index()
    {
        $title = trans('kpi.kpis');

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $bscPerspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();

        $sections = Department::whereHas('employees', function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();


        return view('bsc_score.index', compact('title', 'bscPerspectives', 'kpitimelines', 'sections'));
    }


    public function loadData(Request $request)
    {
        $title = trans('kpi.kpis');

        $employee = $this->currentEmployee;

        if($request->perspective_id > 0) {
            $perspectives = BscPerspective::where('id', $request->perspective_id)->get();
        }

        else {
            $perspectives = BscPerspective::all();
        }


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();



        return view('kpi.bsc', compact('title', 'perspectives', 'employee', 'kpitimelines', 'request'));
    }


    public function loadBscScoreData(Request $request)
    {
        $title = trans('kpi.kpis');

        $employee = $this->currentEmployee;

        $sections = Department::whereHas('employees', function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        if($request->department_id > 0) {
            $section_list = Department::where('id', $request->department_id)->with('employees')->get();
        }

        else {
            $section_list = Department::whereHas('employees', function ($query) {
                $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
            })->with('employees.user', 'employees.position' )->get();
        }


        return view('bsc_score.bsc', compact('title', 'sections', 'employee', 'request', 'section_list'));
    }



    public function subordinatesSignOff()
    {
        $title = trans('kpi.review');
        $subordinates = $this->employeeRepository->getAllForEmployeeSubordinates(session('current_company'), session('current_employee'))
            ->with('user')
            ->get();

        return view('kpi._subordinatesSignOff', compact('title', 'subordinates'));
    }


    public function subordinatesSignOffBsc(Employee $employee)
    {
        $title = $employee->user->full_name;

        $perspectives = BscPerspective::all();

        return view('kpi.bscSignOff', compact('title', 'perspectives', 'employee'));
    }


    public function subordinatesSignOffBscModal(Employee $employee)
    {
        $title = $employee->user->full_name;

        $perspectives = BscPerspective::all();

        return view('kpi.bscSignOffModal', compact('title', 'perspectives', 'employee'));
    }




    public function unapproved()
    {
        $title = 'Unapproved KPIs';
        $kpis = $this->kpiRepository->getUnapprovedForSchool(session('current_company'), session('current_employee'))->get();
        return view('kpi.unapproved', compact('title', 'kpis'));
    }



    public function wizard()
    {

        $title = trans('kpi.kpis');

        $employee= Employee::find(session('current_employee'));
        $bscPerspectives= BscPerspective::get();
        $kras= $this->kraRepository
            ->getAllForSchoolYearSchoolKpi(session('current_company'), session('current_company_year'))
            ->get();


        $objectives= KpiObjective::whereHas('kra', function ($q) use ($employee) {
            $q->where('kras.company_year_id', session('current_company_year'));
        })->get();

        $kpis= Kpi::where('employee_id', session('current_employee'))->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $activities= EmployeeKpiActivity::whereHas('kpi', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.employee_id', session('current_employee'));
        })->get();

        $supervisors = Employee::find(session('current_employee'))->supervisors2
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select', '')
            ->toArray();

        $financialKpis = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 1)
            ->get();

        $krasCustomer = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 2)
            ->get();

        $krasInternal = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 3)
            ->get();

        $krasLearning = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 4)
            ->get();

        $krasLiving = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 5)
            ->get();

        $krasPersonal = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 6)
            ->get();

        return view('kpi.wizard', compact('title', 'bscPerspectives', 'kras', 'objectives', 'kpis', 'activities', 'employee', 'employees', 'supervisors', 'financialKpis', 'krasCustomer', 'krasInternal', 'krasLearning', 'krasLiving', 'krasPersonal'));
    }


    public function processWizard(bscWizardRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        if ($this->bscYear->bsc_open)
        {
        //PERSPECTIVE
        $weights = $request['weight'];
        $bsc_perspective_ids = $request['bsc_perspective_id'];
        $percentage=array_sum($weights);

        if ($percentage == 100)
        {
                foreach ($weights as $index => $weight)
                {
                    PerspectiveWeight::updateOrCreate(
                        ['bsc_perspective_id' => $bsc_perspective_ids[$index], 'company_id' => session('current_company'), 'company_year_id' => session('current_company_year'), 'employee_id' => session('current_employee')],
                        ['weight' => $weight]
                    );
                }
        }

       /* if (count($validated['kpis'])>0)
        {
            $employee->kpiActivities()->delete();
            $employee->yearKpis()->delete();
        }*/



        //STORE KRAS
        /*if (Sentinel::hasAccess('key_result_areas'))
        {
        foreach ($validated['kras'] as $kra)
        {
            if (!empty($kra['bsc_perspective_id']) AND !empty($kra['kra']))
                (
                Kra::firstOrCreate(
                    [
                        'company_id' => session('current_company'),
                        'bsc_perspective_id' => $kra['bsc_perspective_id'],
                        'title' => $kra['kra'],
                        'company_year_id' => session('current_company_year'),
                        'created_employee_id' => session('current_employee'),
                        'approved' => 1,
                    ],

                )
                );
        }
        }*/




        //STORE OBJECTIVES
     /*   if (Sentinel::hasAccess('objectives'))
        {
                foreach ($validated['objectives'] as $objective)
        {
            if (!empty($objective['kra_id']) AND !empty($objective['objective']))
                (
                KpiObjective::firstOrCreate(
                    [
                        'kra_id' => $objective['kra_id'],
                        'created_employee_id' => session('current_employee'),
                        'title' => $objective['objective'],
                        'company_year_id' => session('current_company_year'),
                        'section_id' => $employee->section_id,
                        'approved' => 1,
                    ],

                )
                );
        }
        }*/


        //STORE KPIS
       /*  foreach ($validated['kpis'] as $kpi)
        {
            if (!empty($kpi['objective_id']) AND !empty($kpi['kpi']))
                (
                    Kpi::firstOrCreate(
                        [
                            'company_id' => session('current_company'),
                            'kpi_objective_id' => $kpi['objective_id'],
                            'employee_id' => session('current_employee'),
                            'title' => $kpi['kpi'],
                            'company_year_id' => session('current_company_year'),
                            'owner_employee_id' => session('current_employee'),
                            'responsible_employee_id' => $kpi['responsible_employee_id'],
                            'supervisor_employee_id' => $kpi['supervisor_employee_id'],
                            'approved' => 1,
                            'q1' => isset($kpi['q1'][0]) ? $kpi['q1'][0] : 0,
                            'q2' => isset($kpi['q2'][0]) ? $kpi['q2'][0] : 0,
                            'q3' => isset($kpi['q3'][0]) ? $kpi['q3'][0] : 0,
                            'q4' => isset($kpi['q4'][0]) ? $kpi['q4'][0] : 0,
                        ],

                    )
                );
        }*/

        }

        //STORE KPI ACTIVITIES
      /*   foreach ($validated['activities'] as $activity)
        {
            if (!empty($activity['kpi_id']) AND !empty($activity['activity']))
                (
                    EmployeeKpiActivity::firstOrCreate(
                        [
                            'kpi_id' => $activity['kpi_id'],
                            'title' => $activity['activity'],
                            'status_id' => 1,
                            'due_date' => $activity['due_date'],
                            'approved' => 1,
                            'approved_date' => null,
                        ],

                    )
                );
        }*/



        return 'Good';
    }


    public function processWizardGetKpi(Request $request, Employee $employee)
    {

        $kpis = Kpi::where('employee_id', $employee->id)->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select', '')
            ->toArray();
        return $kpis;
    }


    public function processWizardGetObjectives(Request $request, Employee $employee)
    {

        $objectives= KpiObjective::whereHas('kra', function ($q) use ($employee) {
            $q->where('kras.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select', '')
            ->toArray();
        return $objectives;
    }




    public function processWizardGetKras(Request $request, Employee $employee)
    {

        $kras= $this->kraRepository
            ->getAllForSchoolYearSchoolKpi(session('current_company'), session('current_company_year'))
            ->get()
            ->pluck('full_title', 'id')
            ->prepend('Select', '')
            ->toArray();
        return $kras;
    }




    public function sendBscWizard(Request $request)
    {

        if (!empty($request->employee_id) )
            (
            ScoreCard::firstOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'company_year_id' => session('current_company_year'),
                    'approved' => 0,
                    'approved_date' => null,
                ],

            )
            );

        return 'Bsc Sent for approval';
    }






    public function performanceScore()
    {
        $title = trans('kpi.kpis');

        return view('kpi.performanceScore', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    public function create()
    {
        $title = trans('kpi.new');

        $perspectives = BscPerspective::all();

        $employee= Employee::find(session('current_employee'));
        /*$employees = $this->employeeRepository->getAllForKpiResponsibilities(session('current_company'))*/
        $employees = $employee->subordinates2()
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


        $kpitimelines = KpiTimeline::get();

        $supervisors = $employee->supervisors2
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Supervisor', '')
            ->toArray();



        $objectives= KpiObjective::whereHas('kra', function ($q) use ($employee) {
            $q->where('kras.company_year_id', session('current_company_year'))
              ->join('perspective_weights', 'perspective_weights.bsc_perspective_id', '=', 'kras.bsc_perspective_id')
              ->join('employees', 'employees.id', '=', 'perspective_weights.employee_id')
              ->where('perspective_weights.employee_id', $employee->id)
              ->where('perspective_weights.company_year_id', session('current_company_year'))
              ->where('perspective_weights.weight', '>', 0);
        })->get();




        return view('layouts.create', compact('title', 'employees', 'kpitimelines', 'supervisors', 'objectives', 'perspectives'));
    }

    public function modalCreate()
    {
        $title = trans('kpi.new');

        $perspectives = BscPerspective::all();

        $employee= Employee::find(session('current_employee'));
        /*$employees = $this->employeeRepository->getAllForKpiResponsibilities(session('current_company'))*/
        $employees = $employee->subordinates2()
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


        $kpitimelines = KpiTimeline::get();

        $supervisors = $employee->supervisors2
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Supervisor', '')
            ->toArray();



        $objectives= KpiObjective::whereHas('kra', function ($q) use ($employee) {
            $q->where('kras.company_year_id', session('current_company_year'))
              ->join('perspective_weights', 'perspective_weights.bsc_perspective_id', '=', 'kras.bsc_perspective_id')
              ->join('employees', 'employees.id', '=', 'perspective_weights.employee_id')
              ->where('perspective_weights.employee_id', $employee->id)
              ->where('perspective_weights.company_year_id', session('current_company_year'))
              ->where('perspective_weights.weight', '>', 0);
        })->get();




        return view('kpi.modalForm', compact('title', 'employees', 'kpitimelines', 'supervisors', 'objectives', 'perspectives'));
    }



    public function perspectiveWeightCreate(Employee $employee)
    {
        $title = 'BSC Perspectives Weight';
        $perspectives = BscPerspective::all();

        return view('kpi._perspectiveWeights', compact('title', 'employee', 'perspectives'));
    }



    public function perspectiveWeightStore(WeightRequest $request)
    {

        if ($this->currentEmployee->bsc_signed)
        {
            return response()->json(['exception'=>'BSC signed Off. Perspective wights cannot be changed']);
        }


        try
        {
        if ($this->bscYear->bsc_open)
        {
            //PERSPECTIVE
            $weights = $request['weight'];
            $bsc_perspective_ids = $request['bsc_perspective_id'];
            $percentage=array_sum($weights);

            if ($percentage == 100)
            {
                foreach ($weights as $index => $weight)
                {
                    PerspectiveWeight::updateOrCreate(
                        ['bsc_perspective_id' => $bsc_perspective_ids[$index], 'company_id' => session('current_company'), 'company_year_id' => session('current_company_year'), 'employee_id' => $request['employee_id']],
                        ['weight' => $weight]
                    );
                }
            }

            if ($percentage > 100)
            {

                return response()->json(['exception'=>'Percentage more than 100']);
            }
            if ($percentage < 100)
            {
                return response()->json(['exception'=>'Percentage less than 100']);
            }
        }

        else
            return response()->json(['exception'=>'Performance Period Closed']);
        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">WEIGHTS SET Successfully</div>') ;


    }




    public function appraiserPerspectiveWeightStore(WeightRequest $request)
    {
        try
        {
            if ($this->bscYear->bsc_open)
            {
                //PERSPECTIVE
                $weights = $request['weight'];
                $bsc_perspective_ids = $request['bsc_perspective_id'];
                $percentage=array_sum($weights);

                if ($percentage == 100)
                {
                    foreach ($weights as $index => $weight)
                    {
                        PerspectiveWeight::updateOrCreate(
                            ['bsc_perspective_id' => $bsc_perspective_ids[$index], 'company_id' => session('current_company'), 'company_year_id' => session('current_company_year'), 'employee_id' => $request['employee_id']],
                            ['weight' => $weight]
                        );
                    }
                }

                if ($percentage > 100)
                {

                    return response()->json(['exception'=>'Percentage more than 100']);
                }
                if ($percentage < 100)
                {
                    return response()->json(['exception'=>'Percentage less than 100']);
                }
            }

            else
                return response()->json(['exception'=>'Performance Period Closed']);
        }

        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">WEIGHTS SET Successfully</div>') ;


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiRequest $request
     * @return Response
     */
    public function store(KpiRequest $request)
    {
        $validated = $request->validated();
        $totalWeights = $validated["kpis"][0]['kpi_weight'];
        $kpiObjectiveId = $validated["kpis"][0]['kpi_objective_id'];


        if ($kpiObjectiveId == 0)
        {
            return response()->json(['exception'=>'KPI Objective required']);
        }

        $kpiObjective = KpiObjective::find($kpiObjectiveId);

        $percentage = KpiResponsibility::where('responsible_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->sum('weight');


        $perspectiveWeight = PerspectiveWeight::whereEmployeeId(session('current_employee'))->whereCompanyYearId(session('current_company_year'))->whereBscPerspectiveId($kpiObjective->kra->bsc_perspective_id)->first()->weight;

        $usedPerspectiveWeight = KpiResponsibility::where('responsible_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) use($kpiObjective) {
            $q->where('kpis.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', $kpiObjective->kra->bsc_perspective_id);
        })->sum('weight');

        if (($usedPerspectiveWeight + $totalWeights) > $perspectiveWeight)
        {
            return response()->json(['exception'=>'Total KPI weight exceeds ' .$perspectiveWeight. ' for this Perspective']);
        }



        if ($percentage + $totalWeights > 100)
        {
            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }


        if (!$this->bscYear->bsc_open)
        {
            return response()->json(['exception'=>'Planning period closed for this performance year']);
        }


        try
        {
            DB::transaction(function() use ($request) {
        $validated = $request->validated();

            //STORE KPIS
            foreach ($validated['kpis'] as $kpi)
            {
                if (!empty($kpi['kpi_objective_id']) AND !empty($kpi['kpi']))
                {
                    $theKpi = Kpi::firstOrCreate
                    (
                        [
                            'company_id' => session('current_company'),
                            'kpi_objective_id' => $kpi['kpi_objective_id'],
                            'employee_id' => session('current_employee'),
                            'title' => $kpi['kpi'],
                            'company_year_id' => session('current_company_year'),
                            'approved' => 1,

                        ]
                    );

                        KpiResponsibility::firstOrCreate
                        (
                            [
                            'kpi_id' => $theKpi->id,
                            'responsible_employee_id' => session('current_employee'),
                            'supervisor_employee_id' => $kpi['supervisor_employee_id'],
                            'weight' => $kpi['kpi_weight']
                            ]
                        );

                        //STORE ASSOCIATED KPI PERFORMANCE REVIEW TABLE
                            foreach ($kpi['timeline'] as $kpi_timeline)
                            {
                                KpiPerformanceReview::firstOrCreate
                                (
                                    [
                                        'kpi_id' => $theKpi->id,
                                        'employee_id' => session('current_employee'),
                                        'kpi_timeline_id' => $kpi_timeline,
                                    ],
                                    /*[
                                        'self_rating' => 0,
                                        'agreed_rating' => 0,
                                        'self_score' => 0,
                                        'score' => 0,
                                    ],*/
                                );
                            }


                        //STORE ASSOCIATED KPI TIMELINES

                            foreach ($kpi['timeline'] as $kpi_timeline)
                            {
                                EmployeeKpiTimeline::firstOrCreate
                                (
                                    [
                                        'kpi_id' => $theKpi->id,
                                        'employee_id' => session('current_employee'),
                                        'kpi_timeline_id' => $kpi_timeline
                                    ]
                                );
                            }




                    /*}

                    else*/
                    /*Cascading*/
                    if (isset($kpi['responsible_employee_id']))
                    {
                    foreach ($kpi['responsible_employee_id'] as $responsible_employee_id)
                    {
                        $employee = Employee::find($responsible_employee_id);
                        /*if ($employee->totalYearweight(session('current_company_year')) < 100)*/
                        if ($employee->bsc_open)
                        {
                            KpiResponsibility::firstOrCreate
                            (
                                [
                                    'kpi_id' => $theKpi->id,
                                    'responsible_employee_id' => $responsible_employee_id,
                                    'supervisor_employee_id' => session('current_employee'),
                                    'weight' => $kpi['kpi_weight']
                                ]
                            );



                        //STORE ASSOCIATED KPI PERFORMANCE REVIEW TABLE

                            foreach ($kpi['timeline']  as $kpi_timeline)
                            {
                                KpiPerformanceReview::firstOrCreate
                                (
                                    [
                                        'kpi_id' => $theKpi->id,
                                        'employee_id' => $responsible_employee_id,
                                        'kpi_timeline_id' => $kpi_timeline,
                                    ],
                                    [
                                        /*'self_rating' => 0,*/
                                        /*'agreed_rating' => 0,*/
                                        /*'self_score' => 0,
                                        'score' => 0,*/
                                    ],
                                );
                            }



                        //STORE ASSOCIATED KPI TIMELINES

                            foreach ($kpi['timeline'] as $kpi_timeline)
                            {
                                EmployeeKpiTimeline::firstOrCreate
                                (
                                    [
                                        'kpi_id' => $theKpi->id,
                                        'employee_id' => $responsible_employee_id,
                                        'kpi_timeline_id' => $kpi_timeline
                                    ]
                                );
                            }
                        }


                    }
                    }

                }
            }


            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }


        return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;
    }




    public function signOffBsc(Request $request)
    {

        $employee = Employee::find($request->employee_id);


        if ($employee->totalYearweight(session('current_company_year')) > 100)
        {

            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }

        if ($employee->totalYearweight(session('current_company_year')) < 100)
        {

            return response()->json(['exception'=>'Total KPI weight less than 100']);
        }

        EmployeeKpiSignOff::firstOrCreate
        (
            [
                'employee_id' => $employee->id,
                'company_year_id' => session('current_company_year'),
            ],
            [
                'status' => 0,
                'self_sign_off_date' => now(),
            ]
        );


        //send email to supervisors
        foreach ($employee->supervisors as $supervisor)
        {
            $when = now()->addMinutes(1);
            if (GeneralHelper::validateEmail($supervisor->employee->user->email))
            {
                Mail::to($supervisor->employee->user->email)
                    ->later($when, new SendBscSignOffEmail($supervisor->employee->user, $employee));
            }
        }

        //Send email to user
        if (GeneralHelper::validateEmail($employee->user->email))
        {
            $when = now()->addMinutes(1);
            Mail::to($employee->user->email)
                ->later($when, new SendSelfBscSignOffEmail($employee->user));
        }


        return 'BSC sent for approval';

    }


    public function approveSignOffBsc(Request $request)
    {

        $employee = Employee::find($request->employee_id);


        if ($employee->totalYearweight(session('current_company_year')) > 100)
        {

            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }

        if ($employee->totalYearweight(session('current_company_year')) < 100)
        {

            return response()->json(['exception'=>'Total KPI weight less than 100']);
        }

        EmployeeKpiSignOff::updateOrCreate
        (
            [
                'employee_id' => $employee->id,
                'company_year_id' => session('current_company_year'),
            ],
            [
                'status' => 1,
                'supervisor_sign_off_date' => now(),
            ]
        );


        //send email to supervisors
        foreach ($employee->supervisors as $supervisor)
        {
            $when = now()->addMinutes(1);
            if (GeneralHelper::validateEmail($supervisor->employee->user->email))
            {
                Mail::to($supervisor->employee->user->email)
                    ->later($when, new approveBscSignOffEmail($supervisor->employee->user, $employee));
            }
        }

        //Send email to user
        if (GeneralHelper::validateEmail($employee->user->email))
        {
            $when = now()->addMinutes(1);
            Mail::to($employee->user->email)
                ->later($when, new ApproveSelfBscSignOffEmail($employee->user));
        }


        return 'BSC Approved And Signed Off';

    }




    public function openSignOffBsc(Request $request)
    {

        $employee = Employee::find($request->employee_id);


       /* if ($employee->totalYearweight(session('current_company_year')) > 100)
        {

            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }

        if ($employee->totalYearweight(session('current_company_year')) < 100)
        {

            return response()->json(['exception'=>'Total KPI weight less than 100']);
        }*/

        EmployeeKpiSignOff::updateOrCreate
        (
            [
                'employee_id' => $employee->id,
                'company_year_id' => session('current_company_year'),
            ],
            [
                'status' => 0
            ]
        );


        //send email to supervisors
        foreach ($employee->supervisors as $supervisor)
        {
            $when = now()->addMinutes(1);
            if (GeneralHelper::validateEmail($supervisor->employee->user->email))
            {
                Mail::to($supervisor->employee->user->email)
                    ->later($when, new OpenBscSignOffEmail($supervisor->employee->user, $employee));
            }
        }

        //Send email to user
        if (GeneralHelper::validateEmail($employee->user->email))
        {
            $when = now()->addMinutes(1);
            Mail::to($employee->user->email)
                ->later($when, new OpenSelfBscSignOffEmail($employee->user));
        }


        return 'BSC Opened';

    }


    public function calPerspectivePercentage(Request $request)
    {
        $weights = $request['kpi_weight'];

        $percentage=array_sum($weights);
        if ($percentage<=100)
        return response($percentage) ;
        else
        return response($percentage) ;

    }



    public function latestKpiComments(Kpi $kpi)
    {
        return view('kpi.comments', compact('kpi'));
    }






    public function addComment(KpiCommentRequest $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                if (!empty($request->newsComment))
                {
                    $comment = new KpiComment();
                    $comment->kpi_id = $request->kpi_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->newsComment;
                    $comment->save();

                }

            });

            //send email to supervisors
            foreach (Kpi::find($request->kpi_id)->kpiSupervisors as $supervisor)
            {
                $when = now()->addMinutes(1);
                if (GeneralHelper::validateEmail($supervisor->user->email))
                {
                    Mail::to($supervisor->employee->user->email)
                        ->later($when, new KpiCommentSupervisorEmail($supervisor->user));
                }
            }

            //Send email to responsible employees
            foreach (Kpi::find($request->kpi_id)->kpiResponsibleEmployees as $kpiResponsibleEmployee)
            {
                if (GeneralHelper::validateEmail($kpiResponsibleEmployee->user->email))
                {
                    $when = now()->addMinutes(1);
                    Mail::to($kpiResponsibleEmployee->user->email)
                        ->later($when, new KpiCommentResponsibleEmployeesEmail($kpiResponsibleEmployee->user));
                }
            }

        }


        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }



        $kpi = Kpi::find($request->kpi_id);
        return view('kpi.comments', compact('kpi'));

    }




    public function updateKpiWeights(UpdateKpiWeightRequest $request)
    {
        $totalWeights = array_sum($request['kpi_weight']);

        if ($totalWeights > 100)
        {
            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }


        try
        {
            DB::transaction(function() use ($request) {
                $kpi_weights = $request['kpi_weight'];
                $kpi_responsibility_ids = $request['kpi_responsibility_id'];

                foreach ($kpi_responsibility_ids as $index => $kpi_responsibility_id)
                {

                    $kpi_responsibility = KpiResponsibility::find($kpi_responsibility_id);
                    $kpi_responsibility->weight = $kpi_weights[$index];
                    $kpi_responsibility->save();

                }


            });

        }

        catch (\Exception $e) {


            return response()->json(['exception'=>$e->getMessage()]);
        }


        //send email to supervisors
        /* foreach ($this->currentEmployee->supervisors as $supervisor)
         {
             $when = now()->addMinutes(1);
             if (GeneralHelper::validateEmail($supervisor->employee->user->email))
             {
                 @Notification::send($supervisor->employee->user, new KpiSelfReviewSupervisorNotification($supervisor->employee->user, $this->quarter, $this->currentEmployee));

             }
         }*/
        return response('<div class="alert alert-success">KPI Weights Updated Successfull</div>') ;

    }




    public function calKpiScore(KpiPerformanceReviewRequest $request)
    {
        if ($this->currentEmployee->totalYearweight(session('current_company_year')) > 100)
        {

            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }

        if ($this->currentEmployee->totalYearweight(session('current_company_year')) < 100)
        {

            return response()->json(['exception'=>'Total KPI weight less than 100']);
        }

        $signed = EmployeeKpiSignOff::where('employee_id', session('current_employee'))->where('company_year_id', session('current_company_year'))->first();

        if (!isset($signed->status) OR $signed->status != 1)
        {
            return response()->json(['exception'=>'BSC not signed Off. Kindly Submit for approval and sign off']);
        }


        try
        {
            DB::transaction(function() use ($request) {
        $kpi_weights = $request['kpi_weight'];
        $perspective_weights = $request['perspective_weight'];
        $weights = $request['weight'];
        $kpi_ids = $request['kpi_id'];
        $period_status = $request['period_status'];
        /*$comments = $request['comment'];*/
        $percentage = array_sum($weights) / count($weights);

        foreach ($kpi_ids as $index => $kpi_id)
        {

            KpiPerformanceReview::updateOrCreate(
                [
                    'employee_id' =>  session('current_employee'),
                    'kpi_timeline_id' =>  $request->timeline_id,
                    'kpi_id' =>  $kpi_id,
                ],

                [
                    'self_rating' => $weights[$index],
                    'period_status' => $period_status[$index],
                ]
            );

            @$averageRating = @KpiPerformanceReview::where('kpi_id', $kpi_id)->where('employee_id', session('current_employee'))->get()->average('self_rating');
            $score = (@$averageRating / 5) * $kpi_weights[$index];

            /*$score = ($weights[$index] / 5) * $perspective_weights[$index];*/
            $kpiScore = EmployeeKpiScore::updateOrCreate(
                [
                    'employee_id' =>  session('current_employee'),
                    'company_year_id' =>  session('current_company_year'),
                    'kpi_id' =>  $kpi_id,
                ],

                [
                    'self_score' => $score
                ]
            );

        }



        EmployeeKpiTimelineSignOff::updateOrCreate(

            [
                'employee_id' =>      session('current_employee'),
                'company_year_id' =>  session('current_company_year'),
                'kpi_timeline_id' =>  $request->kpi_timeline_id,
            ],

            [
                'self' => 1
            ]
        );


            });

        }

        catch (\Exception $e) {


            return response()->json(['exception'=>$e->getMessage()]);
        }

        $dt = Carbon::now();
        $reviewQuarter = KpiTimeline::find($request->kpi_timeline_id);


        //send email to supervisors
        foreach ($this->currentEmployee->supervisors as $supervisor)
        {
            $when = now()->addMinutes(1);
            if (GeneralHelper::validateEmail($supervisor->employee->user->email))
            {
                @Notification::send($supervisor->employee->user, new KpiSelfReviewSupervisorNotification($supervisor->employee->user, $reviewQuarter, $this->currentEmployee));

            }
        }
        return response('<div class="alert alert-success">Review Successfull</div>') ;

    }

public function calSubordinateKpiScore(KpiPerformanceReviewRequest $request)
    {
        $employee = Employee::find($request->employee_id);
        if ($employee->totalYearweight(session('current_company_year')) > 100)
        {
            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }

        if ($employee->totalYearweight(session('current_company_year')) < 100)
        {
            return response()->json(['exception'=>'Total KPI weight less than 100']);
        }

        $signed = EmployeeKpiSignOff::where('employee_id', $request->employee_id)->where('company_year_id', session('current_company_year'))->first();

        if (!isset($signed->status) OR $signed->status != 1)
        {
            return response()->json(['exception'=>'BSC not signed Off and cannot be scored']);
        }



        try
        {
            DB::transaction(function() use ($request) {
                $kpi_weights = $request['kpi_weight'];
                $perspective_weights = $request['perspective_weight'];
                $weights = $request['weight'];
                $kpi_ids = $request['kpi_id'];
                $percentage = array_sum($weights) / count($weights);
                $comments = $request['comment'];



                foreach ($kpi_ids as $index => $kpi_id)
                {
                    if (!empty($weights[$index]))
                    {


                 $review = KpiPerformanceReview::updateOrCreate(
                            [
                                'kpi_id' =>  $kpi_id,
                                'employee_id' =>  $request->employee_id,
                                'kpi_timeline_id' =>  $request->kpi_timeline_id,
                            ],

                            [
                                'comment' =>  $comments[$index],
                                'agreed_rating' =>  $weights[$index],
                            ]
                        );
                }

                    @$averageRating = @KpiPerformanceReview::where('kpi_id', $kpi_id)->where('employee_id', $request->employee_id)->whereHas('kpi', function ($q) {
                        $q->where('kpis.company_year_id', session('current_company_year'));
                    })->average('agreed_rating');
                    $score = (@$averageRating / 5) * $kpi_weights[$index];

                    $kpiScore = EmployeeKpiScore::updateOrCreate(
                        [
                            'employee_id' =>  $request->employee_id,
                            'company_year_id' =>  session('current_company_year'),
                            'kpi_id' =>  $kpi_id,
                        ],

                        [
                            'score' => $score
                        ]
                    );

                }


                EmployeeKpiTimelineSignOff::updateOrCreate(

                    [
                        'employee_id' =>      $request->employee_id,
                        'company_year_id' =>  session('current_company_year'),
                        'kpi_timeline_id' =>  $request->kpi_timeline_id,
                    ],

                    [
                        'supervisor' => 1
                    ]
                );


            });

            $employeeScore = number_format(EmployeeKpiScore::where('employee_id', $request->employee_id)->where('company_year_id', session('current_company_year'))->get()->sum('score'), 2) ?? 0;

            $grade_id =  @PerformanceScoreGrade::where(function ($q) use ($employeeScore){
                $q->where('min_score', '<=', $employeeScore);
                $q->where('max_score', '>=', $employeeScore);
            })->first()->id;

            EmployeeYearGrade::updateOrCreate(

                [
                    'employee_id' =>      $request->employee_id,
                    'company_year_id' =>  session('current_company_year'),
                ],

                [
                    'performance_score_grade_id' => $grade_id,
                    'performance_score' => $employeeScore
                ]
            );

        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        $dt = Carbon::now();
        $reviewQuarter = KpiTimeline::find($request->kpi_timeline_id);

        //send email to user
        if (GeneralHelper::validateEmail($employee->user->email))
        {
            @Notification::send($employee->user, new KpiReviewNotification($employee->user, $reviewQuarter));
        }
        return response('<div class="alert alert-success">Review Successful</div>') ;
    }


    /**
     * Display the specified resource.
     *
     * @param Kpi $kpi
     * @return Response
     */
    public function show(Kpi $kpi)
    {
        $title = trans('kpi.details');
        $action = 'show';
        /*$KpiActivities = $kpiResponsibility->kpi->kpiActivities;*/
        return view('layouts.show', compact('kpi', 'title', 'action'));
    }


    public function showKpiResponsibility(KpiResponsibility $kpiResponsibility)
    {
        $title = trans('kpi.details');
        $action = 'show';
        /*$KpiActivities = $kpiResponsibility->kpi->kpiActivities->where('employee_id', $kpiResponsibility->responsible_employee_id)->whereBetween('due_date', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ]);*/

        $KpiActivities = $kpiResponsibility->kpi->kpiActivities->where('employee_id', $kpiResponsibility->responsible_employee_id);
        return view('layouts.show', compact('kpiResponsibility', 'title', 'KpiActivities', 'action'));
    }


    public function showModalKpiResponsibility(KpiResponsibility $kpiResponsibility)
    {
        $title = trans('kpi.details');
        $action = 'show';

        $KpiActivities = $kpiResponsibility->kpi->kpiActivities->where('employee_id', $kpiResponsibility->responsible_employee_id);
        return view('kpi.modalShow', compact('kpiResponsibility', 'title', 'KpiActivities', 'action'));
    }



    public function showModalKpiReviewComment(KpiPerformanceReview $kpiPerformanceReview)
    {
        $title = 'Supervisor Review Comment';
        $action = 'show';

        return view('kpi.modalShowReviewComment', compact('kpiPerformanceReview', 'title', 'action'));
    }



  public function show2(Kpi $kpi)
    {
        $title = trans('kpi.details');
        $due_date = Carbon::parse(now());
        $action = 'show';
        $KpiActivities = $kpi->kpiActivities->where('employee_id', session('current_employee'));
        return view('kpi.show2', compact('kpi', 'title', 'action', 'KpiActivities'));
    }




    public function addKpiActivities(KpiActivityRequest $request)
    {
            try
            {
                $recurrences = [
                    'None'     => [
                        'times'     => 1,
                        'function'  => ''
                    ],
                    'Quarterly'     => [
                        'times'     => 4,
                        'function'  => 'addQuarter'
                    ],
                    'Weekly'    => [
                        'times'     => 52,
                        'function'  => 'addWeek'
                    ],
                    'Monthly'    => [
                        'times'     => 12,
                        'function'  => 'addMonth'
                    ]
                ];
                $due_date = Carbon::parse($request->due_date);

                $recurrence = $recurrences[$request->recurrence];

                if($recurrence AND $recurrence['times'] > 1)
                    for($i = 0; $i < $recurrence['times']; $i++)
                    {
                        $due_date->{$recurrence['function']}();
                        if ($due_date->format('Y') == now()->format('Y'))
                            (
                        EmployeeKpiActivity::firstOrCreate([
                            'employee_id'     => session('current_employee'),
                            'kpi_id'          => $request->kpi_id,
                            'title'          => $request->title,
                            'due_date'    => $due_date,
                            'recurrence'    => $request->recurrence,
                            'kpi_activity_status_id'    => 1,
                        ])
                    );
                    }

                else

                    EmployeeKpiActivity::firstOrCreate([
                        'employee_id'     => session('current_employee'),
                        'kpi_id'          => $request->kpi_id,
                        'title'           => $request->title,
                        'due_date'        => $request->due_date,
                        'recurrence'      => $request->recurrence,
                        'kpi_activity_status_id'    => 1,
                    ]);


            }

            catch (\Exception $e) {
                return response()->json(['error'=>$e->getMessage()]);
            }

            /*event(new KpiActivityCreatedEvent($kpiActivity));*/
           /* return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;*/
            $kpiResponsibility = KpiResponsibility::where('kpi_id', $request->kpi_id)->first();
            $KpiActivities = Kpi::find($request->kpi_id)->kpiActivities->where('employee_id', session('current_employee'));
            return view('kpi.activities', compact('KpiActivities', 'kpiResponsibility'));





        /* END OF ONE*/


    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function autocomplete(Request $request)
    {
        $data = Kpi::select("title")
            ->where('title', 'LIKE', '%'. $request->get('query'). '%')
            ->get();

        return response()->json($data);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param Kpi $kpi
     * @return Response
     */
    public function edit(Kpi $kpi)
    {
        $title = trans('kpi.edit' ).' '. $kpi->title;

        $perspectives = BscPerspective::all();

        /*$employees = $this->employeeRepository->getAllForKpiResponsibilities(session('current_company'))
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

        $employees = $this->currentEmployee->subordinates2()
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

        $kpi_responsibilities = $kpi->kpiResponsibilities()->where('supervisor_employee_id', session('current_employee'))
            ->get()
            ->pluck('responsible_employee_id', 'responsible_employee_id')
            ->prepend('Select Responsibility', 0)
            ->toArray();


        $kpi_responsibility_supervisors = $kpi->kpiResponsibilities()->where('responsible_employee_id', session('current_employee'))
            ->get()
            ->pluck('supervisor_employee_id', 'supervisor_employee_id')
            ->prepend('Select Supervisor', 0)
            ->toArray();



        $supervisors = $this->currentEmployee->supervisors2
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Supervisor', '')
            ->toArray();


        $kpiObjectives = KpiObjective::whereHas('kra', function ($q) {
            $q->where('kras.company_year_id', session('current_company_year'));
        })->get()
            ->pluck('full_title', 'id')
            ->prepend('Select Kpi Objective', 0)
            ->toArray();

        $kpitimelines = KpiTimeline::get();
        $thisKpiTimelines = $kpi->employee_kpiTimelines()->where('employee_id', session('current_employee'))->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->kpi_timeline_id,
                ];
            })->pluck('id')
            ->toArray();
       /* dd($thisKpiTimelines);*/

        return view('kpi.modalForm', compact('title', 'kpi', 'kpiObjectives','employees', 'kpitimelines', 'supervisors', 'kpi_responsibilities', 'perspectives', 'kpi_responsibility_supervisors', 'thisKpiTimelines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiRequest $request
     * @param Kpi $kpi
     * @return Response
     */
    public function update(KpiRequest $request, Kpi $kpi)
    {

        $validated = $request->validated();
        $totalWeights = $validated['kpi_weight'];

if ($kpi->employee_id == session('current_employee'))
{

        if ($request->kpi_objective_id == 0)
        {
            return response()->json(['exception'=>'KPI Objective required']);
        }
}
        $perspectiveWeight = PerspectiveWeight::whereEmployeeId(session('current_employee'))->whereCompanyYearId(session('current_company_year'))->whereBscPerspectiveId($kpi->kpiObjective->kra->bsc_perspective_id)->first()->weight ?? '';

        $totalPerspectiveWeight = PerspectiveWeight::whereEmployeeId(session('current_employee'))->whereCompanyYearId(session('current_company_year'))->sum('weight');

       /* if ($totalPerspectiveWeight < 100)
        {
            return response()->json(['exception'=>'Your Total Perspective Weights is less than 100']);
        }*/

        $usedPerspectiveWeight = KpiResponsibility::where('responsible_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) use($kpi) {
            $q->where('kras.company_year_id', session('current_company_year'))
              ->where('kras.bsc_perspective_id', $kpi->kpiObjective->kra->bsc_perspective_id);
        })->sum('weight');

        /*if (($totalWeights) > $perspectiveWeight)
        {

            return response()->json(['exception'=>'Total KPI weight exceeds ' .$perspectiveWeight. ' for this Perspective']);
        }*/

        $percentage = KpiResponsibility::where('responsible_employee_id', session('current_employee'))->whereHas('kpi.kpiObjective.kra', function ($q) use($kpi) {
            $q->where('kpis.company_year_id', session('current_company_year'))->where('kpi_id', '!=',$kpi->id );
        })->sum('weight');



        /*if ($percentage + $totalWeights > 100)
        {
            return response()->json(['exception'=>'Total KPI weight more than 100']);
        }*/

        try
        {
            DB::transaction(function() use ($request, $kpi, $validated) {
        if ($kpi->employee_id == session('current_employee') )
        {
            $kpi->update($request->except('responsible_employee_id', 'supervisor_employee_id'));

            $kpi->save();
        }



            @$kpi->kpiResponsibilities()->where('kpi_responsibilities.supervisor_employee_id', session('current_employee'))->delete();

             /*@$kpi->kpiResponsibilities()->join('employee_kpi_sign_offs', 'employee_kpi_sign_offs.employee_id', '=', 'kpi_responsibilities.responsible_employee_id')
            ->where('kpi_responsibilities.supervisor_employee_id', session('current_employee'))
            ->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
            ->where('employee_kpi_sign_offs.status', '!=', 1)->delete();*/
            /*Cascading*/

               /* if ($kpi->employee_id == session('current_employee') )
                {*/

                    KpiResponsibility::updateOrCreate
                    (
                        [
                        'kpi_id' => $kpi->id,
                        'responsible_employee_id' => session('current_employee'),
                       ],

                        [
                            'weight' => $request->kpi_weight,
                            'supervisor_employee_id' => $request->supervisor_employee_id,
                        ]
                    );
                /*}*/

                if ($kpi->employee_id == session('current_employee') )
                {
                    //STORE ASSOCIATED KPI TIMELINES
                    if (isset($validated['timeline']))

                    $kpi->kpiTimelines()->where('employee_id', session('current_employee'))->delete();

                    {
                        foreach ($validated['timeline'] as $kpi_timeline)
                        {
                            EmployeeKpiTimeline::firstOrCreate
                            (
                                [
                                    'kpi_id' => $kpi->id,
                                    'employee_id' => session('current_employee'),
                                    'kpi_timeline_id' => $kpi_timeline
                                ]
                            );
                        }
                    }
                }

        if (isset($request['responsible_employee_id']))
        {
            foreach ($request['responsible_employee_id'] as $responsible_employee_id)
            {
                /*$kpi->kpiResponsibilities()->where('responsible_employee_id', $responsible_employee_id)->delete();*/
                /*@$kpi->kpiResponsibilities()->join('employee_kpi_sign_offs', 'employee_kpi_sign_offs.employee_id', '=', 'kpi_responsibilities.responsible_employee_id')
                    ->where('kpi_responsibilities.supervisor_employee_id', session('current_employee'))
                    ->where('employee_kpi_sign_offs.status', '!=', 1)
                    ->where('employee_kpi_sign_offs.company_year_id',  session('current_company_year'))
                    ->delete();*/

                $employee = Employee::find($responsible_employee_id);
                /*if ($employee->totalYearweight(session('current_company_year')) < 100)*/
                if ($employee->bsc_open)
                {
                        KpiResponsibility::firstOrCreate
                        (
                            [
                                'kpi_id' => $kpi->id,
                                'responsible_employee_id' => $responsible_employee_id,
                            ],

                            [
                                'supervisor_employee_id' => session('current_employee'),
                                'weight' => $request->kpi_weight
                            ]
                        );

                        //STORE ASSOCIATED KPI PERFORMANCE REVIEW TABLE
                        /*$kpi->kpiPerformanceReview()->where('employee_id', $responsible_employee_id)
                            ->where('self_score', '')->where('agreed_rating', '')->delete();*/

                        /*$kpi->kpiPerformanceReview()->join('employee_kpi_sign_offs', 'employee_kpi_sign_offs.employee_id', '=', 'kpi_performance_reviews.employee_id')
                            ->where('kpi_performance_reviews.employee_id', $responsible_employee_id)
                            ->where('employee_kpi_sign_offs.status', '!=', 1)->delete();*/

                            foreach ($validated['timeline'] as $kpi_timeline)
                            {
                                KpiPerformanceReview::firstOrCreate
                                (
                                    [
                                        'kpi_id' => $kpi->id,
                                        'employee_id' => $responsible_employee_id,
                                        'kpi_timeline_id' => $kpi_timeline,
                                    ]
                                );
                            }




                        //STORE ASSOCIATED KPI TIMELINES
                        $kpi->kpiTimelines()->where('employee_id', $responsible_employee_id)->delete();
                            foreach ($validated['timeline'] as $kpi_timeline)
                            {
                                EmployeeKpiTimeline::firstOrCreate
                                (
                                    [
                                        'kpi_id' => $kpi->id,
                                        'employee_id' => $responsible_employee_id,
                                        'kpi_timeline_id' => $kpi_timeline
                                    ]
                                );
                            }

                    //Send email to responsible employees

                    if (GeneralHelper::validateEmail($employee->user->email))
                    {
                        @Notification::send($employee->user, new CascadeKpiNotification($employee->user, $kpi));
                    }

            }
            }
        }


            });
        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">KPI UPDATED SUCCESSFULLY</div>') ;

    }

    /*public function delete(Kpi $kpi)
    {
        $title = trans('kpi.delete');
        $kpiResponsibility = $kpi->kpiActivities;
        return view('kpi.delete', compact('kpiResponsibility', 'kpi', 'title', 'kpiResponsibility'));
    }*/

    /**
     * Remove the specified resource from storage.
     *
     * @param  Kpi $kpi
     * @return Response
     */




    public function findPerspectiveObjectives(Request $request)
    {
        $objectives= KpiObjective::whereHas('kra', function ($q) use ($request){
            $q->where('kras.company_year_id', session('current_company_year'))
                ->where('kras.bsc_perspective_id', $request->perspective_id)
                ->join('perspective_weights', 'perspective_weights.bsc_perspective_id', '=', 'kras.bsc_perspective_id')
                ->join('employees', 'employees.id', '=', 'perspective_weights.employee_id')
                ->where('perspective_weights.employee_id', session('current_employee'))
                ->where('perspective_weights.company_year_id', session('current_company_year'))
                ->where('perspective_weights.weight', '>', 0);
        })->get()
            ->pluck('full_title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();

        return $objectives;
    }




    public function delete(Kpi $kpi)
    {
        try
        {
        $signed = $kpi->kpiResponsibilities()->join('employee_kpi_sign_offs', 'employee_kpi_sign_offs.employee_id', '=', 'kpi_responsibilities.responsible_employee_id')
            ->where('kpi_responsibilities.supervisor_employee_id', session('current_employee'))
            ->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
            ->where('employee_kpi_sign_offs.status', '=', 1)->count();

        if ($signed  > 0)
        {
            return response()->json(['exception'=>'This KPI is cascaded and signed off and cannot be deleted']);
        }

        if ($signed  == 0)
        {

        DB::transaction(function() use ($kpi) {
        $kpi->kpiResponsibilities()->delete();
        $kpi->kpiActivities()->delete();
        $kpi->kpiTimelines()->delete();
        $kpi->competencyGaps()->delete();
        $kpi->kpiPerformanceReview()->delete();
        $kpi->comments()->delete();
        $kpi->delete();
        });

       //Send email to responsible employees
            foreach ($kpi->kpiResponsibleEmployees as $kpiResponsibleEmployee)
            {
                if (GeneralHelper::validateEmail($kpiResponsibleEmployee->user->email))
                {
                    @Notification::send($kpiResponsibleEmployee->user, new DeleteKpiNotification($kpiResponsibleEmployee->user, $kpi));
                }
            }
        };
        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">KPI DELETED SUCCESSFULLY</div>') ;
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

    public function data()
    {
        $levels = $this->levelRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'section' => $level->section->title,
                ];
            });

        return Datatables::make($levels)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.edit\', Sentinel::getUser()->permissions)))
										<a href="{{ url(\'/levels/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.show\', Sentinel::getUser()->permissions)))
                                    	<a href="{{ url(\'/levels/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @endif
                                     @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.delete\', Sentinel::getUser()->permissions)))
                                     	<a href="{{ url(\'/levels/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }


    public function findKpiActivities(Request $request)
    {
        $kpi = Kpi::find($request->kpi);
        $title = $kpi->title.' Activities';
        $KpiActivities = $kpi->kpiActivities;
        return view('kpi.activities', compact('KpiActivities', 'title'));
    }

    public function approve(Request $request)
    {
        try
        {
            DB::transaction(function() use ($request) {
                $kpi = Kpi::find($request->kpi_id);
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


    public function EmailApprove(Kpi $kpi)
    {
        if ($kpi->supervisor_employee_id != session('current_employee'))
        {
            Flash::error('You are not authorized to approve this kpi');
            return redirect('/');
        }

        if ($kpi->approved == 1)
        {
            Flash::warning('KPI Already Approved');
            return redirect('/');
        }
        try
        {
            DB::transaction(function() use ($kpi) {
                $kpi->approved = 1;
                $kpi->save();

            });

        } catch (\Exception $e) {
            Flash::error('KPI not found');
            return redirect('/');

        }
        Flash::success('KPI '.$kpi->title. ' Approved Successfully');
        /*event(new KpiApprovedEvent($kpi));*/
        return redirect('/');

    }


    public static function perspectives()
    {
        return new BscPerspectives(BscPerspective::where('company_id', session('current_company'))->get());

    }


    public function topTalents(Request $request)
    {
        $title = trans('kpi.kpis');
        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user', 'section')
            ->get()
            ->filter(function($item) use ($request) {
                return $item->standing === $request->talent;
            });;

        return view('kpi.top_talents', compact('title', 'employees'));
        /*return $request->talent;*/
    }


    public function correctEmployeeKpiTimeLines()
    {
        set_time_limit(0);
        try
        {

        /*$kpis = Kpi::where('employee_id', session('current_employee'))->where('company_year_id', 19)->chunk(20, function ($kpis){*/
        $kpis = KpiResponsibility::whereHas('kpi', function ($q)  {
            $q->where('kpis.company_year_id', session('current_company_year'))
                /*->where('kpi_responsibilities.responsible_employee_id', session('current_employee'))*/;
        })->chunk(100, function ($kpisResponsibilities){

            foreach ($kpisResponsibilities as $kpisResponsibility)
            {
                if ($kpisResponsibility->kpi->q1 == 1)
                {
                    EmployeeKpiTimeline::firstOrCreate
                    (

                        [
                            'kpi_id' => $kpisResponsibility->kpi->id,
                            'employee_id' => $kpisResponsibility->responsible_employee_id,
                            'kpi_timeline_id' => 1
                        ]
                    );
                }
                if ($kpisResponsibility->kpi->q2 == 1)
                {
                    EmployeeKpiTimeline::firstOrCreate
                    (

                        [
                            'kpi_id' => $kpisResponsibility->kpi->id,
                            'employee_id' => $kpisResponsibility->responsible_employee_id,
                            'kpi_timeline_id' => 2
                        ]
                    );
                }
                if ($kpisResponsibility->kpi->q3 == 1)
                {
                    EmployeeKpiTimeline::firstOrCreate
                    (

                        [
                            'kpi_id' => $kpisResponsibility->kpi->id,
                            'employee_id' => $kpisResponsibility->responsible_employee_id,
                            'kpi_timeline_id' => 3
                        ]
                    );
                }
                if ($kpisResponsibility->kpi->q4 == 1)
                {
                    EmployeeKpiTimeline::firstOrCreate
                    (

                        [
                            'kpi_id' => $kpisResponsibility->kpi->id,
                            'employee_id' => $kpisResponsibility->responsible_employee_id,
                            'kpi_timeline_id' => 4
                        ]
                    );
                }
            }
        });
        }
        catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">  KPI TIMELINES CORRECTED SUCCESSFULLY</div>') ;


    }

}
