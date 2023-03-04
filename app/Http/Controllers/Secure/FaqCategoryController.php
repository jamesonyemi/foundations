<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FaqCategoryRequest;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FaqCategoryController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'faq_category');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('faq_category.faq_categories');

        return view('faq_category.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('faq_category.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FaqCategoryRequest|Request $request
     * @return Response
     */
    public function store(FaqCategoryRequest $request)
    {
        $faq_category = new FaqCategory($request->all());
        $faq_category->save();

        return redirect('/faq_category');
    }

    /**
     * Display the specified resource.
     *
     * @param FaqCategory $faqCategory
     * @return Response
     * @internal param int $id
     */
    public function show(FaqCategory $faqCategory)
    {
        $title = trans('faq_category.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'faqCategory', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param FaqCategory $faqCategory
     * @return Response
     */
    public function edit(FaqCategory $faqCategory)
    {
        $title = trans('faq_category.edit');

        return view('layouts.edit', compact('title', 'faqCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FaqCategory|Request $request
     * @param FaqCategory $faqCategory
     * @return Response
     */
    public function update(FaqCategoryRequest $request, FaqCategory $faqCategory)
    {
        $faqCategory->update($request->all());
        $faqCategory->save();

        return redirect('/faq_category');
    }

    public function delete(FaqCategory $faqCategory)
    {
        $title = trans('faq_category.delete');

        return view('faq_category.delete', compact('title', 'faqCategory'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FaqCategory $faqCategory
     * @return Response
     */
    public function destroy(FaqCategory $faqCategory)
    {
        $faqCategory->delete();

        return redirect('/faq_category');
    }

    public function data()
    {
        $faqCategories = FaqCategory::get()
            ->map(function ($faqCategory) {
                return [
                    'id' => $faqCategory->id,
                    'title' => $faqCategory->title,
                ];
            });

        return Datatables::make($faqCategories)
            ->addColumn('actions', '<a href="{{ url(\'/faq_category/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/faq_category/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/faq_category/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }
}
