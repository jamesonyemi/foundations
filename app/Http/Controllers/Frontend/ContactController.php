<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Requests\ContactRequest;
use App\Mail\MessageMail;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ContactController extends FrontendController
{
    /**
     * ContactController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $title = trans('frontend.contact');

        return view('contact', compact('title'));
    }

    public function contact(ContactRequest $request)
    {
        Contact::create($request->all());

        Mail::send(new MessageMail($request->all()));

        return redirect('contact')->with('success', trans('frontend.contact_message'));
    }
}
