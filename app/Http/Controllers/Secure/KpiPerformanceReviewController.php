<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Http\Requests\Secure\KpiPerformanceReviewRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\CompetencyEmployeeResource;
use App\Http\Resources\KpiPerformanceReviewResource;
use App\Models\BscPerspective;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\KpiPerformanceReview;
use App\Models\KpiTimeline;
use App\Models\Level;
use App\Repositories\KraRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\SectionRepository;
use App\Models\SchoolDirection;
use App\Repositories\KpiRepository;
use App\Helpers\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class KpiPerformanceReviewController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
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
        SectionRepository $sectionRepository,
        KpiRepository $kpiRepository,
        KraRepository $kraRepository
    ) {

        parent::__construct();
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;
        $this->kpiRepository = $kpiRepository;
        $this->kraRepository = $kraRepository;

        view()->share('type', 'kpi_performance_review');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kpi.review');

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();

        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->where('active_review', 1)
            ->first('id');


        return view('kpi_performance_review.index', compact('title',  'reviewQuarterId', 'kpitimelines', 'perspectives'));
    }


    public function allIndex()
    {
        $title = trans('kpi.review');
        /*$subordinates = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user', 'section')
            ->get();*/

        $subordinates = $this->employeeRepository->getAllForSchool(session('current_company'))->where('status', 1)
            ->with('user', 'section')
            ->get();

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();

        return view('kpi_performance_review.allReviewList', compact('title', 'subordinates', 'perspectives', 'kpitimelines'));
    }

    public function subordinatesindex()
    {
        $title = trans('kpi.review');
        $subordinates = $this->employeeRepository->getAllForEmployeeSubordinates(session('current_company'), session('current_employee'))
            ->with('user')
            ->get();

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();

        return view('kpi_performance_review.subordinateReviewList', compact('title', 'subordinates', 'kpitimelines', 'perspectives'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kpi.new_review');

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

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();


        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))
                ->get()
                ->map(function ($item) {
                    return [
                        "id"   => $item->id,
                        "name" => isset($item->kpiObjective) ? $item->kpiObjective->full_title. '  ' . ' | ' .$item->title . ' | '. ' | ' .$item->weight . ' | ' : "",
                    ];
                })
                ->pluck('name', 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        }

        else
        {
        $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->kpiObjective) ? $item->kpiObjective->full_title. '  '. ' | ' .$item->title . ' | '. ' | ' .$item->weight . ' | ' : "",
                ];
            })
            ->pluck('name', 'id')
            ->prepend('Select KPI', 0)
            ->toArray();
        }

        return view('layouts.create', compact('title', 'kpis', 'employees', 'kpitimelines', 'perspectives'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|KpiPerformanceReviewRequest $request
     * @return Response
     */
    public function store(KpiPerformanceReviewRequest $request)
    {

        $kpi_ids = $request['kpi_id'];
        $kpi_timeline_ids = $request['kpi_timeline_id'];
        $self_ratings = $request['self_rating'];
        $comment = $request['comment'];
        try
        {
            if (isset($request['kpi_id'])){
                foreach ($kpi_ids as $index => $kpi_id)
                {
                    if (!empty($kpi_id) )
                    {
                        $kpi_performance_review = new KpiPerformanceReview();
                        $kpi_performance_review->kpi_id = $kpi_id;
                        $kpi_performance_review->employee_id = session('current_employee');
                        $kpi_performance_review->kpi_timeline_id = $kpi_timeline_ids[$index];
                        $kpi_performance_review->self_rating = $self_ratings[$index];
                        $kpi_performance_review->comment = $comment[$index];
                        $kpi_performance_review->save();
                    }
                }

            }

            /* END OF ONE*/


        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }


        if ($kpi_performance_review->save())
        {

            return response('<div class="alert alert-success">KPI REVIEW CREATED Successfully</div>') ;
        }
        else
        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param Kpi $kpi
     * @return Response
     */
    public function show(KpiPerformanceReview $kpi_performance_review)
    {
        $title = trans('kpi.details');
        $action = 'show';
        return view('layouts.show', compact('kpi_performance_review', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param KpiPerformanceReview $kpi_performance_review
     * @return Response
     */
    public function edit(KpiPerformanceReview $kpi_performance_review)
    {
        $title = trans('kpi.edit_review');

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Responsibility', '')
            ->toArray();

        $kpitimelines = KpiTimeline::where('company_id',session('current_company'))
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();

        if (session('current_employee')) {
            $kpis = $this->kpiRepository->getAllForEmployee(session('current_company'), session('current_employee'))
                ->get()
                ->map(function ($item) {
                    return [
                        "id"   => $item->id,
                        "name" => isset($item->kpiObjective) ? $item->kpiObjective->full_title. '  ' . ' | ' .$item->title . ' | '. ' | ' .$item->weight . ' | ' : "",
                    ];
                })
                ->pluck('name', 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        }

        else
        {
            $kpis = $this->kpiRepository->getAllForSchool(session('current_company'))
                ->get()
                ->map(function ($item) {
                    return [
                        "id"   => $item->id,
                        "name" => isset($item->kpiObjective) ? $item->kpiObjective->full_title. '  '. ' | ' .$item->title . ' | '. ' | ' .$item->weight . ' | ' : "",
                    ];
                })
                ->pluck('name', 'id')
                ->prepend('Select KPI', 0)
                ->toArray();
        }

        return view('layouts.edit', compact('title', 'kpi_performance_review', 'kpis', 'employees', 'kpitimelines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|KpiPerformanceReviewRequest $request
     * @param KpiPerformanceReview $kpi_performance_review
     * @return Response
     */
    public function update(KpiPerformanceReviewRequest $request, KpiPerformanceReview $kpi_performance_review)
    {
        try
        {
            $kpi_performance_review->update($request->all());
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }


        if ($kpi_performance_review->save())
        {

            return response('<div class="alert alert-success">KPI REVIEW UPDATED Successfully</div>') ;
        }
        else
        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }

    }

    public function delete(KpiPerformanceReview $kpi_performance_review)
    {
        $title = trans('level.delete');
        return view('kpi_performance_review.delete', compact('kpi_performance_review', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  KpiPerformanceReview $kpi_performance_review
     * @return Response
     */
    public function destroy(KpiPerformanceReview $kpi_performance_review)
    {
        $kpi_performance_review->delete();

    }

    public static function data()
    {
        return new KpiPerformanceReviewResource(KpiPerformanceReview::whereHas('kpi', function ($q) {
            $q->where('kpi_performance_reviews.employee_id', session('current_employee'))
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get());

    }


    public function subordinates()
    {

        return new CompetencyEmployeeResource($this->employeeRepository->getAllForEmployeeSubordinates(session('current_company'), session('current_employee'))
            ->with('user', 'section')
            ->get());

    }

     public function all()
    {

        return new CompetencyEmployeeResource($this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user', 'section')
            ->get());

    }

   /* public function subordinates()
    {

        return new EmployeeResource($this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user', 'section')
            ->get());

    }*/

    public function subordinateReview(Employee $employee)
    {
        $title = 'Review '.$employee->user->full_name.'';


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', 0)
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();



        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->where('active_review', 1)
            ->first('id');


        return view('kpi_performance_review.subordinateReview', compact('title', 'reviewQuarterId', 'kpitimelines', 'employee', 'perspectives' ));
    }



    public function subordinateReviewModal(Employee $employee)
    {
        $title = 'Review '.$employee->user->full_name.'';


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', 0)
            ->toArray();


        $perspectives = BscPerspective::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Perspective', '')
            ->toArray();



        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->where('active_review', 1)
            ->first('id');


        return view('kpi_performance_review.subordinateReviewModal', compact('title', 'reviewQuarterId', 'kpitimelines', 'employee', 'perspectives' ));
    }

    public function bsc(Employee $employee)
    {
        $title = 'BSC entries for '.$employee->user->full_name.'';

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

        $perspectives = BscPerspective::all();
/*
        $financialKpis = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 1)
            ->with('kpi', 'responsibilities')->get()->unique('kpi_id');

        $krasCustomer = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 2)
            ->with('kpi', 'responsibilities')->get()->unique('kpi_id');

        $krasInternal = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 3)
            ->with('kpi', 'responsibilities')->get()->unique('kpi_id');

        $krasLearning = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 4)
            ->with('kpi', 'responsibilities')->get()->unique('kpi_id');

        $krasLiving = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 5)
            ->with('kpi', 'responsibilities')->get()->unique('kpi_id');

        $krasPersonal = $this->kpiRepository
            ->getAllForSchoolYearSchoolEmployee(session('current_company'), session('current_company_year'), $employee->id, 6)
            ->with('kpi', 'responsibilities')->get()->unique('kpi_id');*/

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();
        return view('kpi_performance_review.bsc', compact('title',  'kpitimelines', 'employee', 'perspectives'));
    }


}
