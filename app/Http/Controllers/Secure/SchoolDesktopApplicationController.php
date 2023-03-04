<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests;
use App\Http\Requests\Secure\SchoolDesktopApplicationRequest;
use App\Models\Company;
use App\Models\SchoolDesktopApplication;
use App\Repositories\SchoolRepository;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;
use Rhumsaa\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class SchoolDesktopApplicationController extends SecureController
{
    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * SchoolDesktopApplicationController constructor.
     * @param SchoolRepository $schoolRepository
     */
    public function __construct(SchoolRepository $schoolRepository)
    {
        parent::__construct();

        $this->schoolRepository = $schoolRepository;

        view()->share('type', 'desktop_applications');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('desktop_application.desktop_application');

        return view('desktop_applications.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('desktop_application.new');
        $company_ids = Company::pluck('title', 'id')->toArray();

        return view('layouts.create', compact('title', 'company_ids'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SchoolDesktopApplicationRequest $request
     * @return Response
     */
    public function store(SchoolDesktopApplicationRequest $request)
    {
        try {
            $auth_id = Uuid::uuid4();
            $auth_secure = Uuid::uuid4();

            $school_desktop_application = new SchoolDesktopApplication();
            $school_desktop_application->company_id = $request->company_id;
            $school_desktop_application->auth_id = $auth_id->toString();
            $school_desktop_application->auth_secure = $auth_secure->toString();
            $school_desktop_application->save();

            return redirect('/desktop_applications');
        } catch (UnsatisfiedDependencyException $e) {
            // Some dependency was not met. Either the method cannot be called on a
            // 32-bit system, or it can, but it relies on Moontoast\Math to be present.
            return back()->withErrors('Caught exception: '.$e->getMessage()."\n");
        }
    }

    /**
     * Display the specified resource.
     * @param  int $id
     * @return Response
     */
    public function show(SchoolDesktopApplication $desktopApplication)
    {
        $title = trans('desktop_application.details');
        $action = 'show';

        return view('layouts.show', compact('desktopApplication', 'title', 'action'));
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(SchoolDesktopApplication $desktopApplication)
    {
        $title = trans('desktop_application.delete');

        return view('desktop_applications/delete', compact('desktopApplication', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(SchoolDesktopApplication $desktopApplication)
    {
        $desktopApplication->delete();

        return redirect('/desktop_applications');
    }

    public function data()
    {
        $desktop_applications = SchoolDesktopApplication::all()
            ->map(function ($desktop_applications) {
                return [
                    'id' => $desktop_applications->id,
                    'school' => $desktop_applications->school->title,
                    'auth_id' => $desktop_applications->auth_id,
                    'auth_secure' => $desktop_applications->auth_secure,
                ];
            });

        return Datatables::make($desktop_applications)
            ->addColumn('actions', '<a href="{{ url(\'/desktop_applications/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/desktop_applications/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }
}
