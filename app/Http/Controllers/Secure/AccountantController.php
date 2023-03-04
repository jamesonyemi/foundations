<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\TeacherRequest;
use App\Models\AccountantSchool;
use App\Models\Company;
use App\Models\User;
use App\Repositories\UserRepository;
use DB;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class AccountantController extends SecureController
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
        $this->middleware('authorized:accountant.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:accountant.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:accountant.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:accountant.delete', ['only' => ['delete', 'destroy']]);

        parent::__construct();

        $this->userRepository = $userRepository;

        view()->share('type', 'accountant');

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
        $title = trans('accountant.accountant');
        $accountants = $this->userRepository->getUsersForRole('accountant');

        return view('accountant.index', compact('title', 'accountants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('accountant.new');
        $custom_fields = CustomFormUserFields::getCustomUserFields('accountant');
        $all_schools = Company::pluck('title', 'id')->toArray();

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

        $role = Sentinel::findRoleBySlug('accountant');
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

        CustomFormUserFields::storeCustomUserField('accountant', $user->id, $request);

        //if($request->has('company_id')) {
        AccountantSchool::firstOrCreate(['company_id' => session('current_company'), 'user_id' => $user->id]);
        //}

        return redirect('/accountant');
    }

    /**
     * Display the specified resource.
     *
     * @param User $accountant
     * @return Response
     */
    public function show(User $accountant)
    {
        $title = trans('accountant.details');
        $action = 'show';
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('accountant', $accountant->id);

        return view('layouts.show', compact('accountant', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $accountant
     * @return Response
     */
    public function edit(User $accountant)
    {
        $title = trans('accountant.edit');
        $custom_fields = CustomFormUserFields::fetchCustomValues('accountant', $accountant->id);
        $all_schools = Company::pluck('title', 'id')->toArray();
        $accountant_schools = AccountantSchool::where('user_id', $accountant->id)->pluck('company_id', 'company_id')->toArray();

        return view('layouts.edit', compact('title', 'accountant', 'custom_fields', 'all_schools', 'accountant_schools'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TeacherRequest $request
     * @param User $accountant
     * @return Response
     */
    public function update(TeacherRequest $request, User $accountant)
    {
        if ($request->password != '') {
            $accountant->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $accountant->picture = $picture;
            $accountant->save();
        }

        $accountant->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('accountant', $accountant->id, $request);

        AccountantSchool::firstOrCreate(['company_id' => session('current_company'), 'user_id' => $accountant->id]);

        return redirect('/accountant');
    }

    /**
     * @param User $accountant
     * @return Response
     */
    public function delete(User $accountant)
    {
        $title = trans('accountant.delete');
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('accountant', $accountant->id);

        return view('/accountant/delete', compact('accountant', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $accountant
     * @return Response
     */
    public function destroy(User $accountant)
    {
        $accountant->delete();

        return redirect('/accountant');
    }

    public function data()
    {
        $accountants = $this->userRepository->getUsersForRole('accountant')
            ->map(function ($accountant) {
                return [
                    'id' => $accountant->id,
                    'full_name' => $accountant->full_name,
                ];
            });

        return Datatables::make($accountants)
            ->addColumn('actions', '@if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'accountant.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/accountant/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                        <a href="{{ url(\'/accountant/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'accountant.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/accountant/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
