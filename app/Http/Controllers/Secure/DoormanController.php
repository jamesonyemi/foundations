<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Models\User;
use App\Repositories\UserRepository;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Sentinel;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\DoormanRequest;

class DoormanController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * DoormanController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;

        $this->middleware('authorized:doorman.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:doorman.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:doorman.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:doorman.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'doorman');

        $columns = ['full_name', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('doorman.doorman');
        return view('doorman.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('doorman.new');
        $custom_fields =  CustomFormUserFields::getCustomUserFields('doorman');
        return view('layouts.create', compact('title', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DoormanRequest $request
     * @return Response
     */
    public function store(DoormanRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->all());

        $role = Sentinel::findRoleBySlug('doorman');
        $role->users()->attach($user);

        $user = User::find($user->id);
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $user->picture = $picture;
            $user->save();
        }
        $user->update($request->except('password', 'image_file'));

        CustomFormUserFields::storeCustomUserField('doorman', $user->id, $request);

        return redirect('/doorman');
    }

    /**
     * Display the specified resource.
     *
     * @param User $doorman
     * @return Response
     */
    public function show(User $doorman)
    {
        $title = trans('doorman.details');
        $action = 'show';
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('doorman', $doorman->id);
        return view('layouts.show', compact('doorman', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $doorman
     * @return Response
     */
    public function edit(User $doorman)
    {
        $title = trans('doorman.edit');
        $custom_fields =  CustomFormUserFields::fetchCustomValues('doorman', $doorman->id);
        return view('layouts.edit', compact('title', 'doorman', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DoormanRequest $request
     * @param User $doorman
     * @return Response
     */
    public function update(DoormanRequest $request, User $doorman)
    {
        if ($request->password != "") {
            $doorman->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $doorman->picture = $picture;
            $doorman->save();
        }
        $doorman->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('doorman', $doorman->id, $request);
        return redirect('/doorman');
    }

    /**
     * @param User $doorman
     * @return Response
     */
    public function delete(User $doorman)
    {
        $title = trans('doorman.delete');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('doorman', $doorman->id);
        return view('/doorman/delete', compact('doorman', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $doorman
     * @return Response
     */
    public function destroy(User $doorman)
    {
        $doorman->delete();
        return redirect('/doorman');
    }

    public function data()
    {
        $doormans = $this->userRepository->getUsersForRole('doorman')
            ->map(function ($doorman) {
                return [
                    'id' => $doorman->id,
                    'full_name' => $doorman->full_name,
                ];
            });
        return Datatables::make($doormans)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'doorman.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/doorman/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/doorman/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'doorman.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/doorman/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
