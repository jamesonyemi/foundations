<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FaqRequest;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class FaqController extends SecureController
{
    public function __construct()
    {
        parent::__construct();
        view()->share('type', 'faq');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('faq.faqs');

        return view('faq.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('faq.new');
        $faq_categories = FaqCategory::pluck('title', 'id')->toArray();

        return view('layouts.create', compact('title', 'faq_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FaqRequest|Request $request
     * @return Response
     */
    public function store(FaqRequest $request)
    {
        $faq = new Faq($request->all());
        $faq->user_id = Sentinel::getUser()->id;
        $faq->save();

        return redirect('/faq');
    }

    /**
     * Display the specified resource.
     *
     * @param Faq $faq
     * @return Response
     * @internal param int $id
     */
    public function show(Faq $faq)
    {
        $title = trans('faq.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'faq', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Faq $faq
     * @return Response
     * @internal param int $id
     */
    public function edit(Faq $faq)
    {
        $title = trans('faq.edit');
        $faq_categories = FaqCategory::pluck('title', 'id')->toArray();

        return view('layouts.edit', compact('title', 'faq', 'faq_categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FaqRequest|Request $request
     * @param Faq $faq
     * @return Response
     * @internal param int $id
     */
    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->all());

        return redirect('/faq');
    }

    public function delete(Faq $faq)
    {
        $title = trans('faq.delete');

        return view('faq.delete', compact('title', 'faq'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Faq $faq
     * @return Response
     * @internal param int $id
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect('/faq');
    }

    public function data()
    {
        $faqs = Faq::get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'title' => $faq->title,
                    'category' => isset($faq->category) ? $faq->category->title : '',
                ];
            });

        return Datatables::make($faqs)
            ->addColumn('actions', '<a href="{{ url(\'/faq/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/faq/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/faq/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
