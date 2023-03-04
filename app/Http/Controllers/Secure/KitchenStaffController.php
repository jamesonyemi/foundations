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

class KitchenStaffController extends SecureController
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
        $this->middleware('authorized:kitchen_staff.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:kitchen_staff.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:kitchen_staff.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:kitchen_staff.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();

        $this->userRepository = $userRepository;

        view()->share('type', 'kitchen_staff');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('kitchen_staff.kitchen_staff');
        return view('kitchen_staff.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('kitchen_staff.new');
        $custom_fields =  CustomFormUserFields::getCustomUserFields('kitchen_staff');
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

        $role = Sentinel::findRoleBySlug('kitchen_staff');
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


        CustomFormUserFields::storeCustomUserField('kitchen_staff', $user->id, $request);

        if (Settings::get('account_one_school')=='yes') {
            AccountantSchool::create(['company_id'=>$request->get('company_id'),'user_id'=>$user->id]);
        }

        return redirect('/kitchen_staff');
    }

    /**
     * Display the specified resource.
     *
     * @param User $kitchen_staff
     * @return Response
     */
    public function show(User $kitchen_staff)
    {
        $title = trans('kitchen_staff.details');
        $action = 'show';
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('kitchen_staff', $kitchen_staff->id);
        return view('layouts.show', compact('kitchen_staff', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $kitchen_staff
     * @return Response
     */
    public function edit(User $kitchen_staff)
    {
        $title = trans('kitchen_staff.edit');
        $custom_fields =  CustomFormUserFields::fetchCustomValues('kitchen_staff', $kitchen_staff->id);
        $all_schools = School::pluck('title', 'id')->toArray();
        $kitchen_staff_schools = AccountantSchool::where('user_id', $kitchen_staff->id)->pluck('company_id', 'company_id')->toArray();
        return view('layouts.edit', compact('title', 'kitchen_staff', 'custom_fields', 'all_schools', 'kitchen_staff_schools'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TeacherRequest $request
     * @param User $kitchen_staff
     * @return Response
     */
    public function update(TeacherRequest $request, User $kitchen_staff)
    {
        if ($request->password != "") {
            $kitchen_staff->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $kitchen_staff->picture = $picture;
            $kitchen_staff->save();
        }

        $kitchen_staff->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('kitchen_staff', $kitchen_staff->id, $request);

        AccountantSchool::where('user_id', $kitchen_staff->id)->delete();

        if (Settings::get('account_one_school')=='yes') {
            AccountantSchool::create(['company_id'=>$request->get('company_id'),'user_id'=>$kitchen_staff->id]);
        }
        return redirect('/kitchen_staff');
    }

    /**
     * @param User $kitchen_staff
     * @return Response
     */
    public function delete(User $kitchen_staff)
    {
        $title = trans('kitchen_staff.delete');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('kitchen_staff', $kitchen_staff->id);
        return view('/kitchen_staff/delete', compact('kitchen_staff', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $kitchen_staff
     * @return Response
     */
    public function destroy(User $kitchen_staff)
    {
        $kitchen_staff->delete();
        return redirect('/kitchen_staff');
    }

    public function data()
    {
        $kitchen_staffs = $this->userRepository->getUsersForRole('kitchen_staff')
            ->map(function ($kitchen_staff) {
                return [
                    'id' => $kitchen_staff->id,
                    'full_name' => $kitchen_staff->full_name,
                ];
            });
        return Datatables::make($kitchen_staffs)
            ->addColumn('actions', '@if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'kitchen_staff.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/kitchen_staff/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                        <a href="{{ url(\'/kitchen_staff/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'kitchen_staff.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/kitchen_staff/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make(false);
    }
}
