<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\NoticeTypeRequest;
use App\Models\NoticeType;
use App\Repositories\NoticeTypeRepository;
use Yajra\DataTables\Facades\DataTables;

class NoticeTypeController extends SecureController
{
    /**
     * @var NoticeTypeRepository
     */
    private $noticeTypeRepository;

    /**
     * NoticeTypeController constructor.
     * @param NoticeTypeRepository $noticeTypeRepository
     */
    public function __construct(NoticeTypeRepository $noticeTypeRepository)
    {
        parent::__construct();

        $this->noticeTypeRepository = $noticeTypeRepository;

        view()->share('type', 'noticetype');

        $columns = ['title', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('noticetype.noticetypes');

        return view('noticetype.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('noticetype.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(NoticeTypeRequest $request)
    {
        $noticeType = new NoticeType($request->all());
        $noticeType->company_id = session('current_company');
        $noticeType->save();

        return redirect('/noticetype');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(NoticeType $noticeType)
    {
        $title = trans('noticetype.details');
        $action = 'show';

        return view('layouts.show', compact('noticeType', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(NoticeType $noticeType)
    {
        $title = trans('noticetype.edit');

        return view('layouts.edit', compact('title', 'noticeType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(NoticeTypeRequest $request, NoticeType $noticeType)
    {
        $noticeType->update($request->all());

        return redirect('/noticetype');
    }

    public function delete(NoticeType $noticeType)
    {
        $title = trans('noticetype.delete');

        return view('/noticetype/delete', compact('noticeType', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(NoticeType $noticeType)
    {
        $noticeType->delete();

        return redirect('/noticetype');
    }

    public function data()
    {
        $noticeTypes = $this->noticeTypeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($noticeType) {
                return [
                    'id' => $noticeType->id,
                    'title' => $noticeType->title,
                ];
            });

        return Datatables::make($noticeTypes)
            ->addColumn('actions', '<a href="{{ url(\'/noticetype/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/noticetype/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/noticetype/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
