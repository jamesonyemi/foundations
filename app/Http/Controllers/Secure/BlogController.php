<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class BlogController extends SecureController
{
    public function __construct()
    {
        parent::__construct();
        view()->share('type', 'blog');

        $columns = ['title', 'category', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('blog.blogs');

        return view('blog.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('blog.new');
        $blog_categories = BlogCategory::pluck('title', 'id')->toArray();

        return view('layouts.create', compact('title', 'blog_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogRequest|Request $request
     * @return Response
     */
    public function store(BlogRequest $request)
    {
        $blog = new Blog($request->except('image_file', 'tags'));
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $folderName = '/uploads/blog/';
            $picture = Str::random(8) .'.'.$extension;
            $blog->image = $picture;

            $destinationPath = public_path().$folderName;
            $request->file('image_file')->move($destinationPath, $picture);
        }
        $blog->user_id = Sentinel::getUser()->id;
        $blog->save();

        $blog->tag($request->tags);

        return redirect('/blog');
    }

    /**
     * Display the specified resource.
     *
     * @param Blog $blog
     * @return Response
     * @internal param int $id
     */
    public function show(Blog $blog)
    {
        $title = trans('blog.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'blog', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Blog $blog
     * @return Response
     * @internal param int $id
     */
    public function edit(Blog $blog)
    {
        $title = trans('blog.edit');
        $blog_categories = BlogCategory::pluck('title', 'id')->toArray();

        return view('layouts.edit', compact('title', 'blog', 'blog_categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BlogRequest|Request $request
     * @param Blog $blog
     * @return Response
     * @internal param int $id
     */
    public function update(BlogRequest $request, Blog $blog)
    {
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $folderName = '/uploads/blog/';
            $picture = Str::random(8) .'.'.$extension;
            $blog->image = $picture;

            $destinationPath = public_path().$folderName;
            $request->file('image')->move($destinationPath, $picture);
        }
        $blog->retag($request['tags']);

        $blog->update($request->except('image_file', 'tags'));

        return redirect('/blog');
    }

    public function delete(Blog $blog)
    {
        $title = trans('blog.delete');

        return view('blog.delete', compact('title', 'blog'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Blog $blog
     * @return Response
     * @internal param int $id
     */
    public function destroy(Blog $blog)
    {
        $blog->delete();

        return redirect('/blog');
    }

    public function data()
    {
        $blogs = Blog::get()
            ->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'category' => isset($blog->category) ? $blog->category->title : '',
                ];
            });

        return Datatables::make($blogs)
            ->addColumn('actions', '<a href="{{ url(\'/blog/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/blog/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/blog/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
