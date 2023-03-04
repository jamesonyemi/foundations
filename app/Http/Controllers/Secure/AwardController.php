<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Award;
use App\Models\Company;
use App\Repositories\EmployeeRepository;

class AwardController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * SchoolController constructor.
     * @param EmployeeRepository $employeeRepository
     */
    public function __construct(EmployeeRepository $employeeRepository)
    {
        parent::__construct();

        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'award');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Awards';

        $awards = Award::whereCompanyId(session('current_company'))->get();

        return view('award.index', compact('title', 'awards'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Award';

        $employees = $this->employeeRepository
            ->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->user_id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')->toArray();

        return view('layouts.create', compact('title', 'employees'));
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

        return redirect('/contact_request');
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

        return redirect('/contact_request');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Company $school)
    {
        $title = trans('schools.delete');

        return view('/contact_request/delete', compact('school', 'title'));
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

        return redirect('/contact_request');
    }

    public function activate(Company $school)
    {
        $school->active = ($school->active + 1) % 2;
        $school->save();

        return redirect('/contact_request');
    }
}
