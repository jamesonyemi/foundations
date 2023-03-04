<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\BlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BlogCategoryController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'blog_category');

        $columns = ['title', 'number_of_blog', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('blog_category.blog_categories');

        return view('blog_category.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('blog_category.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogCategoryRequest|Request $request
     * @return Response
     */
    public function store(BlogCategoryRequest $request)
    {
        $blog_category = new BlogCategory($request->all());
        $blog_category->save();

        return redirect('/blog_category');
    }

    /**
     * Display the specified resource.
     *
     * @param BlogCategory $blogCategory
     * @return Response
     * @internal param int $id
     */
    public function show(BlogCategory $blogCategory)
    {
        $title = trans('blog_category.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'blogCategory', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param BlogCategory $blogCategory
     * @return Response
     */
    public function edit(BlogCategory $blogCategory)
    {
        $title = trans('blog_category.edit');

        return view('layouts.edit', compact('title', 'blogCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BlogCategory|Request $request
     * @param BlogCategory $blogCategory
     * @return Response
     */
    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory)
    {
        $blogCategory->update($request->all());
        $blogCategory->save();

        return redirect('/blog_category');
    }

    public function delete(BlogCategory $blogCategory)
    {
        $title = trans('blog_category.delete');

        return view('blog_category.delete', compact('title', 'blogCategory'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BlogCategory $blogCategory
     * @return Response
     */
    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();

        return redirect('/blog_category');
    }

    public function data()
    {
        $blogCategories = BlogCategory::get()
            ->map(function ($blogCategory) {
                return [
                    'id' => $blogCategory->id,
                    'title' => $blogCategory->title,
                    'number_of_blog' => $blogCategory->number_of_blog,
                ];
            });

        return Datatables::make($blogCategories)
            ->addColumn('actions', '<a href="{{ url(\'/blog_category/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/blog_category/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/blog_category/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
