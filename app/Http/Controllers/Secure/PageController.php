<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\PageRequest;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Http\Request;

class PageController extends SecureController
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * SchoolController constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        parent::__construct();

        $this->pageRepository = $pageRepository;

        view()->share('type', 'pages');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('pages.pages');

        return view('pages.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('pages.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PageRequest $request
     * @return Response
     */
    public function store(PageRequest $request)
    {
        $page = new Page($request->all());
        $page->save();

        return redirect('/pages');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Page $page)
    {
        $title = trans('pages.details');
        $action = 'show';

        return view('layouts.show', compact('page', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Page $page
     * @return Response
     */
    public function edit(Page $page)
    {
        $title = trans('pages.edit');

        return view('layouts.edit', compact('title', 'page'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Page $page
     * @return Response
     */
    public function update(PageRequest $request, Page $page)
    {
        $page->update($request->all());

        return redirect('/pages');
    }

    /**
     * @param $page
     * @return Response
     */
    public function delete(Page $page)
    {
        $title = trans('pages.delete');

        return view('/pages/delete', compact('page', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Page $page
     * @return Response
     */
    public function destroy($page)
    {
        Page::find($page)->delete();

        return redirect('/pages');
    }

    public function data()
    {
        $pages = $this->pageRepository->getAll()
            ->orderBy('order')
            ->get()
            ->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                ];
            });

        return Datatables::make($pages)
            ->addColumn('actions', '<a href="#" class="btn btn-sm btn-default"><i class="fa fa-sort"></i> {{ trans("table.order") }}</a>
                                    <a href="{{ url(\'/pages/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                     <a href="{{ url(\'/pages/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/pages/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                            <input type="hidden" name="row" value="{{$id}}" id="row">')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }

    public function reorderpage(Request $request)
    {
        $list = $request['list'];
        $items = explode(',', $list);
        $order = 1;
        foreach ($items as $value) {
            if ($value != '') {
                Page::where('id', '=', $value)->update(['order' => $order]);
                $order++;
            }
        }
    }
}
