<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Blog;

class BlogController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $blogs = Blog::latest()->paginate(5);
        $blogs->setPath('blogs');
        $title = trans('frontend.blog');

        return view('blogs', compact('blogs', 'title'));
    }

    /**
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function blog($slug = '')
    {
        if ($slug == '') {
            $blog = Blog::first();
            $blog->increment('views');
        } else {
            $blog = Blog::whereSlug($slug)->first();
            $blog->increment('views');
        }
        $title = $blog->title;

        return view('blog', compact('blog', 'title'));
    }
}
