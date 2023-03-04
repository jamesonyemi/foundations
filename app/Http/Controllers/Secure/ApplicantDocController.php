<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ApplicantWorkRequest;
use App\Models\Applicant;
use App\Models\Applicant_doc;
use App\Repositories\ApplicantDocRepository;
use App\Http\Requests\Secure\ApplicantDocRequest;
use Yajra\DataTables\Facades\DataTables;
use Sentinel;

class ApplicantDocController extends SecureController
{
    /**
     * @var ApplicantWorkRepository
     */
    private $applicantDocRepository;

    /**
     * BehaviorController constructor.
     * @param ApplicantWorkRepository $applicantWorkRepository
     */
    public function __construct(ApplicantDocRepository $applicantDocRepository)
    {
        parent::__construct();

        $this->applicantDocRepository = $applicantDocRepository;

        view()->share('type', 'applicant_doc');

        $columns = ['title', 'file', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('applicant.docs');
        $docs = $this->applicantDocRepository->getAllForApplicant(Sentinel::getUser()->id)
            ->get();
        return view('applicant_doc.index', compact('title', 'docs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('applicant.new_doc');
        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|ApplicantDocRequest $request
     * @return Response
     */
    public function store(ApplicantDocRequest $request)
    {

        $applicant = Applicant::where('user_id', '=', Sentinel::getUser()->id)->get()->first();

        $applicant_doc = new Applicant_doc($request->all());
        $applicant_doc->user_id = Sentinel::getUser()->id;
        $applicant_doc->applicant_id = $applicant->id;
        $applicant_doc->save();


        if ($request->hasFile('file') != "") {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $document = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/documents/';
            $file->move($destinationPath, $document);
            $applicant_doc->file = $document;

            $applicant_doc->save();
        }

        return redirect('/applicant_doc');
    }

    /**
     * Display the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function show(Applicant_doc $applicant_doc)
    {
        $title = trans('behavior.details');
        $action = 'show';
        return view('layouts.show', compact('applicant_doc', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function edit(Applicant_doc $applicant_doc)
    {
        $title = trans('behavior.edit');
        return view('layouts.edit', compact('title', 'applicant_doc'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|BehaviorAddEditRequest $request
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function update(ApplicantDocRequest $request, Applicant_doc $applicant_doc)
    {
        $applicant_doc->update($request->except('file'));
        $applicant_doc->save();

        if ($request->hasFile('file') != "") {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $document = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/documents/';
            $file->move($destinationPath, $document);
            $applicant_doc->file = $document;

            $applicant_doc->save();
        }
        return redirect('/applicant_doc');
    }

    public function delete(Applicant_doc $applicant_doc)
    {
        $title = trans('behavior.delete');
        return view('/applicant_doc/delete', compact('applicant_doc', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Behavior $behavior
     * @return Response
     * @internal param int $id
     */
    public function destroy(Applicant_doc $applicant_doc)
    {
        $applicant_doc->delete();
        return redirect('/applicant_doc');
    }

    public function data()
    {
        $docs = $this->applicantDocRepository->getAllForApplicant(Sentinel::getUser()->id)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->name,
                    'file' => $doc->file,
                ];
            });

        return Datatables::make($docs)
            ->addColumn('actions', '<a href="{{ url(\'/applicant_doc/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a target="_blank" href="{{ url(\'/uploads\documents/\'. $file .\'/\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ "View Document"}}</a>
                                     <a href="{{ url(\'/applicant_doc/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
