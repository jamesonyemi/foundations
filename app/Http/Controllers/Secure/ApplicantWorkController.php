<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ApplicantWorkRequest;
use App\Models\Applicant;
use App\Models\Applicant_work;
use App\Repositories\ApplicantWorkRepository;
use App\Http\Requests\Secure\ApplicantSchoolRequest;
use Yajra\DataTables\Facades\DataTables;
use Sentinel;

class ApplicantWorkController extends SecureController
{
    /**
     * @var ApplicantWorkRepository
     */
    private $applicantWorkRepository;

    /**
     * BehaviorController constructor.
     * @param ApplicantWorkRepository $applicantWorkRepository
     */
    public function __construct(ApplicantWorkRepository $applicantWorkRepository)
    {
        parent::__construct();

        $this->applicantWorkRepository = $applicantWorkRepository;

        view()->share('type', 'applicant_work');

        $columns = ['title', 'start_date', 'end_date', 'position', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('applicant.work');
        $works = $this->applicantWorkRepository->getAllForApplicant(Sentinel::getUser()->id)
            ->get();
        return view('applicant_work.index', compact('title', 'works'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('applicant.new_work');
        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|ApplicantSchoolRequest $request
     * @return Response
     */
    public function store(ApplicantWorkRequest $request)
    {

        $applicant = Applicant::where('user_id', '=', Sentinel::getUser()->id)->get()->first();

        $applicant_work = new Applicant_work($request->all());
        $applicant_work->user_id = Sentinel::getUser()->id;
        $applicant_work->applicant_id = $applicant->id;
        $applicant_work->save();

        return redirect('/applicant_work');
    }

    /**
     * Display the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function show(Applicant_work $applicant_work)
    {
        $title = trans('applicant.details');
        $action = 'show';
        return view('layouts.show', compact('applicant_work', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(Applicant_work $applicant_work)
    {
        $title = trans('applicant.edit');
        return view('layouts.edit', compact('title', 'applicant_work'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|BehaviorAddEditRequest $request
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function update(ApplicantWorkRequest $request, Applicant_work $applicant_work)
    {
        $applicant_work->update($request->all());
        return redirect('/applicant_work');
    }

    public function delete(Applicant_work $applicant_work)
    {
        $title = trans('behavior.delete');
        return view('/applicant_work/delete', compact('applicant_work', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function destroy(Applicant_work $applicant_work)
    {
        $applicant_work->delete();
        return redirect('/applicant_work');
    }

    public function data()
    {
        $works = $this->applicantWorkRepository->getAllForApplicant(Sentinel::getUser()->id)
            ->get()
            ->map(function ($work) {
                return [
                    'id' => $work->id,
                    'title' => $work->name,
                    'start_date' => $work->start_date,
                    'end_date' => $work->end_date,
                    'position' => $work->position,
                ];
            });

        return Datatables::make($works)
            ->addColumn('actions', '<a href="{{ url(\'/applicant_work/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/applicant_work/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/applicant_work/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
