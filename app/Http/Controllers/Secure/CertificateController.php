<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests;
use App\Http\Requests\Secure\CertificateRequest;
use App\Models\Certificate;
use App\Models\CertificateUser;
use App\Repositories\CertificateRepository;
use App\Repositories\PageRepository;
use App\Repositories\UserRepository;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class CertificateController extends SecureController
{
    /**
     * @var PageRepository
     */
    private $certificateRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * SchoolController constructor.
     * @param CertificateRepository $certificateRepository
     * @param UserRepository $userRepository
     */
    public function __construct(CertificateRepository $certificateRepository, UserRepository $userRepository)
    {
        parent::__construct();

        $this->certificateRepository = $certificateRepository;
        $this->userRepository = $userRepository;

        view()->share('type', 'certificate');

        $columns = ['title', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('certificate.certificate');
        return view('certificate.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('certificate.new');
        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PageRequest $request
     * @return Response
     */
    public function store(CertificateRequest $request)
    {
        $certificate = new Certificate($request->except('image_file'));
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/certificate/';
            $file->move($destinationPath, $picture);
            $certificate->image = $picture;
        }
        $certificate->save();

        return redirect('/certificate');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Certificate $certificate)
    {
        $title = trans('certificate.details');
        $action = 'show';
        return view('layouts.show', compact('certificate', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Certificate $certificate)
    {
        $title = trans('certificate.edit');
        return view('layouts.edit', compact('title', 'certificate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CertificateRequest $request
     * @param Certificate $certificate
     * @return Response
     * @internal param int $id
     */
    public function update(CertificateRequest $request, Certificate $certificate)
    {
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/certificate/';
            $file->move($destinationPath, $picture);
            $certificate->image = $picture;
        }
        $certificate->update($request->except('image_file'));
        return redirect('/certificate');
    }

    /**
     *
     *
     * @param $website
     * @return Response
     */
    public function delete(Certificate $certificate)
    {
        $title = trans('certificate.delete');
        return view('/certificate/delete', compact('certificate', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param School $school
     * @return Response
     */
    public function destroy(Certificate $certificate)
    {
        $certificate->delete();
        return redirect('/certificate');
    }

    public function user(Certificate $certificate)
    {
        $users = $this->userRepository->getUsersForRole('student')
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                ];
            })->pluck('full_name', 'id')->toArray();
        $title = trans('certificate.users');
        $users_select = CertificateUser::where('certificate_id', $certificate->id)->pluck('user_id')->toArray();
        return view('/certificate/user', compact('certificate', 'title', 'users', 'users_select'));
    }

    public function addusers(Request $request, Certificate $certificate)
    {
        CertificateUser::where('certificate_id', $certificate->id)->delete();
        foreach ($request->users_select as $item) {
            $certificate_user = new CertificateUser();
            $certificate_user->certificate_id = $certificate->id;
            $certificate_user->user_id = $item;
            $certificate_user->save();
        }
        return redirect('/certificate');
    }

    public function data()
    {
        $certificates = $this->certificateRepository->getAll()->get()
            ->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'title' => $certificate->title,
                ];
            });
        return Datatables::make($certificates)
            ->addColumn('actions', '<a href="{{ url(\'/certificate/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                     <a href="{{ url(\'/certificate/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/certificate/\' . $id . \'/user\' ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-user-plus"></i>  {{ trans("certificate.users") }}</a>
                                    <a href="{{ url(\'/certificate/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
