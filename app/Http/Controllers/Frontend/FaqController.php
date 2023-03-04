<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Faq;
use App\Models\FaqCategory;

class FaqController extends FrontendController
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
        $faqs = Faq::all();
        $faq_categories = FaqCategory::all();
        $title = trans('frontend.faq');

        return view('faq', compact('faqs', 'faq_categories', 'title'));
    }
}
