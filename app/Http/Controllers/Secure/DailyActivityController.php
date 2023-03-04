<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\DailyActivityRequest;
use App\Models\Company;
use App\Models\DailyActivity;
use App\Models\Employee;
use App\Models\EmployeeKpiActivity;
use App\Models\KpiResponsibility;
use App\Models\ProcurementCategorySupplier;
use App\Models\UserDocument;
use App\Notifications\SendSMS;
use App\Repositories\DailyActivityRepository;
use App\Repositories\SectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Psy\Util\Str;
use Sentinel;

class DailyActivityController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var DailyActivityRepository
     */
    private $dailyActivityRepository;

    protected $module = 'dailyActivity';

    /**
     * EmployeeController constructor.
     * @param DailyActivityRepository $dailyActivityRepository
     */
    public function __construct(
        DailyActivityRepository $dailyActivityRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();
        $this->dailyActivityRepository = $dailyActivityRepository;
        $this->sectionRepository = $sectionRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'dailyActivity');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('dailyActivity.dailyActivities');
        $dailyActivities = $this->dailyActivityRepository->getAllMine(session('current_employee'))
            ->get();

        return view('dailyActivity.index', compact('title', 'dailyActivities'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function filter(Request $request)
    {
        $date = Carbon::create($request->ddate);
        $title = trans('dailyActivity.dailyActivities');

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function ($query) use ($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        if ($request->department_id > 0) {
            $dailyActivities = $this->dailyActivityRepository->getAllForSchoolDepartmentDay(session('current_company'), $request->department_id, $date)
                ->get();
        } elseif ($request->employee_id == 'all') {
            $dailyActivities = $this->dailyActivityRepository->getAllForSchoolDay(session('current_company'), $date)
                ->get();
        } else {
            $dailyActivities = $this->dailyActivityRepository->getForEmployee($request->employee_id, $date)
                ->get();
        }

        return view('dailyActivity.load', compact('title', 'dailyActivities', 'request', 'employees'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexAll()
    {
        $title = trans('dailyActivity.dailyActivities');
        $dailyActivities = $this->dailyActivityRepository->getAllForSchool(session('current_company'))
            ->get();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('All Employees', 'all')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('dailyActivity.all', compact('title', 'dailyActivities', 'employees', 'sections'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('DailyActivity.new');


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

        return view('layouts.create', compact('title', 'employees', 'subsidiaries', 'kpis'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DailyActivityRequest $request
     * @return Response
     */
    public function store(DailyActivityRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $dailyActivity = new DailyActivity();
                $dailyActivity->company_year_id = session('current_company_year');
                $dailyActivity->employee_id = session('current_employee');
                $dailyActivity->employee_for_id = $request->employee_for_id ?? '';
                $dailyActivity->title = $request->title;
                $dailyActivity->description = $request->description;
                $dailyActivity->kpi_id = $request->kpi_id ?? '';
                $dailyActivity->save();

                /*Send email to notify Supervisors*/
                /*@GeneralHelper::sendNewEmployee_email($user,$employee);*/

                if ($request->hasFile('file') != '') {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/documents/';
                    $file->move($destinationPath, $document);
                    $dailyActivity->file = $document;
                    $dailyActivity->save();
                }

                $dailyActivity->companies()->attach($request->input('company_id'));
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Daily Activity Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function show(DailyActivity $dailyActivity)
    {
        $title = $dailyActivity->title;
        $action = 'show';

        return view('layouts.show', compact('dailyActivity', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function edit(DailyActivity $dailyActivity)
    {
        $title = 'Edit '.$dailyActivity->title.'';

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

        $company_daily_Activity = $dailyActivity->companyIds()
            ->pluck('company_id')
            ->toArray();

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

        return view('layouts.edit', compact('title', 'dailyActivity', 'employees', 'subsidiaries', 'company_daily_Activity', 'kpis'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DailyActivityRequest $request
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function update(DailyActivityRequest $request, DailyActivity $dailyActivity)
    {
        try {
            $dailyActivity->title = $request->title;
            $dailyActivity->description = $request->description;
            $dailyActivity->employee_for_id = $request->employee_for_id;
            $dailyActivity->kpi_id = $request->kpi_id ?? '';
            $dailyActivity->save();

            if ($request->hasFile('file') != '') {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);
                $dailyActivity->file = $document;
                $dailyActivity->save();
            }

            $dailyActivity->companies()->sync($request->company_id);

            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $dailyActivity->id,
                'activity'  => 'updated',
            ]);
            /* Flash::success("Employee Information Updated Successfully");*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    /**
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function delete(DailyActivity $dailyActivity)
    {
        $dailyActivity->delete();

        return 'Deleted';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function destroy(DailyActivity $dailyActivity)
    {
        $dailyActivity->delete();

        return 'Deleted';
    }
}
