<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Faq;
use App\Models\Page;

class FrontendController extends Controller
{
    private $tags;

    public function __construct()
    {
        $this->pages = Page::select('title', 'slug')->orderBy('order')->get();
        view()->share('pages', $this->pages);

        $this->count_blogs = Blog::count();
        view()->share('count_blogs', $this->count_blogs);

        $this->tags = Blog::allTags();

        $count_faq = Faq::count();
        view()->share('count_faq', $count_faq);
    }
}
