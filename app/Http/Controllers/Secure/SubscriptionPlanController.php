<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Company;
use App\Models\ContactRequest;
use App\Models\Plan;
use App\Repositories\SchoolRepository;

class SubscriptionPlanController extends SecureController
{
    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * SchoolController constructor.
     * @param SchoolRepository $schoolRepository
     */
    public function __construct(SchoolRepository $schoolRepository)
    {
        parent::__construct();

        $this->schoolRepository = $schoolRepository;

        view()->share('type', 'subscription_plans');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('schools.school');

        $plans = Plan::all();

        return view('subscription_plans.index', compact('title', 'plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('schools.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SchoolRequest $request)
    {
        if (Settings::get('multi_school') == 'yes') {
            $school = new Company($request->except('student_card_background_file', 'photo_file'));
            if ($request->hasFile('student_card_background_file') != '') {
                $file = $request->file('student_card_background_file');
                $extension = $file->getClientOriginalExtension();
                $picture = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/student_card/';
                $file->move($destinationPath, $picture);
                $school->student_card_background = $picture;
            }
            if ($request->hasFile('photo_file') != '') {
                $file = $request->file('photo_file');
                $extension = $file->getClientOriginalExtension();
                $picture = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/school_photo/';
                $file->move($destinationPath, $picture);
                $school->photo = $picture;
            }
            $school->save();
        }

        return redirect('/subscription_plans');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(ContactRequest $contact_request)
    {
        $title = trans('schools.details');
        $action = 'show';

        return view('layouts.show', compact('school', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Company $school)
    {
        $title = trans('schools.edit');

        return view('layouts.edit', compact('title', 'school'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(SchoolRequest $request, Company $school)
    {
        if ($request->hasFile('student_card_background_file') != '') {
            $file = $request->file('student_card_background_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/student_card/';
            $file->move($destinationPath, $picture);
            $school->student_card_background = $picture;
        }
        if ($request->hasFile('photo_file') != '') {
            $file = $request->file('photo_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/school_photo/';
            $file->move($destinationPath, $picture);
            $school->photo = $picture;
        }
        $school->update($request->except('student_card_background_file', 'photo_file'));

        return redirect('/subscription_plans');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Company $school)
    {
        $title = trans('schools.delete');

        return view('/subscription_plans/delete', compact('school', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Company $school
     * @return Response
     */
    public function destroy(Company $school)
    {
        $school->delete();

        return redirect('/subscription_plans');
    }

    public function activate(Company $school)
    {
        $school->active = ($school->active + 1) % 2;
        $school->save();

        return redirect('/subscription_plans');
    }
}
