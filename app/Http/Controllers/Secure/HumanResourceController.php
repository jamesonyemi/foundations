<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\TeacherRequest;
use App\Models\HumanResourceSchool;
use App\Models\User;
use App\Repositories\UserRepository;
use DB;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class HumanResourceController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TeacherController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;

        $this->middleware('authorized:human_resource.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:human_resource.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:human_resource.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:human_resource.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'human_resource');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('human_resource.human_resource');
        $human_resources = $this->userRepository->getUsersForRole('human_resources');

        return view('human_resource.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('human_resource.new');
        $custom_fields = CustomFormUserFields::getCustomUserFields('human_resources');

        return view('layouts.create', compact('title', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(TeacherRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->all());

        $role = Sentinel::findRoleBySlug('human_resources');
        $role->users()->attach($user);

        $user = User::find($user->id);

        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $user->picture = $picture;
            $user->save();
        }

        $user->update($request->except('password', 'image_file'));

        CustomFormUserFields::storeCustomUserField('human_resources', $user->id, $request);

        HumanResourceSchool::firstOrCreate(['company_id' => session('current_company'), 'user_id' => $user->id]);

        return redirect('/human_resource');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(User $human_resource)
    {
        $title = trans('human_resource.details');
        $action = 'show';
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('human_resources', $human_resource->id);

        return view('layouts.show', compact('human_resource', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(User $human_resource)
    {
        $title = trans('human_resource.edit');
        $custom_fields = CustomFormUserFields::fetchCustomValues('human_resources', $human_resource->id);

        return view('layouts.edit', compact('title', 'human_resource', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param User $human_resource
     * @return Response
     */
    public function update(TeacherRequest $request, User $human_resource)
    {
        if ($request->password != '') {
            $human_resource->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $human_resource->picture = $picture;
            $human_resource->save();
        }

        $human_resource->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('human_resources', $human_resource->id, $request);

        return redirect('/human_resource');
    }

    /**
     * @param User $human_resource
     * @return Response
     */
    public function delete(User $human_resource)
    {
        $title = trans('human_resource.delete');
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('human_resources', $human_resource->id);

        return view('human_resource/delete', compact('human_resource', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $human_resource
     * @return Response
     */
    public function destroy(User $human_resource)
    {
        $human_resource->delete();

        return redirect('/human_resource');
    }

    public function data()
    {
        $human_resources = $this->userRepository->getUsersForRole('human_resources')
            ->map(function ($human_resource) {
                return [
                    'id' => $human_resource->id,
                    'full_name' => $human_resource->full_name,
                ];
            });

        return Datatables::make($human_resources)
            ->addColumn('actions', '@if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'human_resource.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/human_resource/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/human_resource/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'human_resource.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/human_resource/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
