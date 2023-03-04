<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Models\AccountantSchool;
use App\Models\School;
use App\Models\User;
use App\Repositories\UserRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Settings;
use DB;
use App\Helpers\Thumbnail;
use Sentinel;
use App\Http\Requests\Secure\TeacherRequest;

class KitchenAdminController extends SecureController
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
        $this->middleware('authorized:kitchen_admin.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:kitchen_admin.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:kitchen_admin.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:kitchen_admin.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();

        $this->userRepository = $userRepository;

        view()->share('type', 'kitchen_admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kitchen_admin.kitchen_admin');
        return view('kitchen_admin.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kitchen_admin.new');
        $custom_fields =  CustomFormUserFields::getCustomUserFields('kitchen_admin');
        $all_schools = School::pluck('title', 'id')->toArray();
        return view('layouts.create', compact('title', 'custom_fields', 'all_schools'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TeacherRequest $request
     * @return Response
     */
    public function store(TeacherRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->except('company_id'));

        $role = Sentinel::findRoleBySlug('kitchen_admin');
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


        CustomFormUserFields::storeCustomUserField('kitchen_admin', $user->id, $request);

        if (Settings::get('account_one_school')=='yes') {
            AccountantSchool::create(['company_id'=>$request->get('company_id'),'user_id'=>$user->id]);
        }

        return redirect('/kitchen_admin');
    }

    /**
     * Display the specified resource.
     *
     * @param User $kitchen_admin
     * @return Response
     */
    public function show(User $kitchen_admin)
    {
        $title = trans('kitchen_admin.details');
        $action = 'show';
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('kitchen_admin', $kitchen_admin->id);
        return view('layouts.show', compact('kitchen_admin', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $kitchen_admin
     * @return Response
     */
    public function edit(User $kitchen_admin)
    {
        $title = trans('kitchen_admin.edit');
        $custom_fields =  CustomFormUserFields::fetchCustomValues('kitchen_admin', $kitchen_admin->id);
        $all_schools = School::pluck('title', 'id')->toArray();
        $kitchen_admin_schools = AccountantSchool::where('user_id', $kitchen_admin->id)->pluck('company_id', 'company_id')->toArray();
        return view('layouts.edit', compact('title', 'kitchen_admin', 'custom_fields', 'all_schools', 'kitchen_admin_schools'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TeacherRequest $request
     * @param User $kitchen_admin
     * @return Response
     */
    public function update(TeacherRequest $request, User $kitchen_admin)
    {
        if ($request->password != "") {
            $kitchen_admin->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $kitchen_admin->picture = $picture;
            $kitchen_admin->save();
        }

        $kitchen_admin->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('kitchen_admin', $kitchen_admin->id, $request);

        AccountantSchool::where('user_id', $kitchen_admin->id)->delete();

        if (Settings::get('account_one_school')=='yes') {
            AccountantSchool::create(['company_id'=>$request->get('company_id'),'user_id'=>$kitchen_admin->id]);
        }
        return redirect('/kitchen_admin');
    }

    /**
     * @param User $kitchen_admin
     * @return Response
     */
    public function delete(User $kitchen_admin)
    {
        $title = trans('kitchen_admin.delete');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('kitchen_admin', $kitchen_admin->id);
        return view('/kitchen_admin/delete', compact('kitchen_admin', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $kitchen_admin
     * @return Response
     */
    public function destroy(User $kitchen_admin)
    {
        $kitchen_admin->delete();
        return redirect('/kitchen_admin');
    }

    public function data()
    {
        $kitchen_admins = $this->userRepository->getUsersForRole('kitchen_admin')
            ->map(function ($kitchen_admin) {
                return [
                    'id' => $kitchen_admin->id,
                    'full_name' => $kitchen_admin->full_name,
                ];
            });
        return Datatables::make($kitchen_admins)
            ->addColumn('actions', '@if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'kitchen_admin.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/kitchen_admin/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                        <a href="{{ url(\'/kitchen_admin/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'kitchen_admin.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/kitchen_admin/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make(false);
    }
}
