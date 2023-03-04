<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Models\User;
use App\Models\LibrarianSchool;
use App\Repositories\UserRepository;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Sentinel;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\LibrarianRequest;

class LibrarianController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * LibrarianController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;

        $this->middleware('authorized:librarian.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:librarian.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:librarian.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:librarian.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'librarian');

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
        $title = trans('librarian.librarian');
        return view('librarian.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('librarian.new');
        $custom_fields =  CustomFormUserFields::getCustomUserFields('librarian');
        return view('layouts.create', compact('title', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LibrarianRequest $request
     * @return Response
     */
    public function store(LibrarianRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->all());

        $role = Sentinel::findRoleBySlug('librarian');
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

        CustomFormUserFields::storeCustomUserField('librarian', $user->id, $request);

        LibrarianSchool::firstOrCreate([ 'company_id' => session('current_company'), 'user_id' => $user->id ]);


        return redirect('/librarian');
    }

    /**
     * Display the specified resource.
     *
     * @param User $librarian
     * @return Response
     */
    public function show(User $librarian)
    {
        $title = trans('librarian.details');
        $action = 'show';
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('librarian', $librarian->id);
        return view('layouts.show', compact('librarian', 'title', 'action', 'custom_fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $librarian
     * @return Response
     */
    public function edit(User $librarian)
    {
        $title = trans('librarian.edit');
        $custom_fields =  CustomFormUserFields::fetchCustomValues('librarian', $librarian->id);
        return view('layouts.edit', compact('title', 'librarian', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LibrarianRequest $request
     * @param User $librarian
     * @return Response
     */
    public function update(LibrarianRequest $request, User $librarian)
    {
        if ($request->password != "") {
            $librarian->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != "") {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
            $librarian->picture = $picture;
            $librarian->save();
        }
        $librarian->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('librarian', $librarian->id, $request);
        return redirect('/librarian');
    }

    /**
     * @param User $librarian
     * @return Response
     */
    public function delete(User $librarian)
    {
        $title = trans('librarian.delete');
        $custom_fields =  CustomFormUserFields::getCustomUserFieldValues('librarian', $librarian->id);
        return view('/librarian/delete', compact('librarian', 'title', 'custom_fields'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $librarian
     * @return Response
     */
    public function destroy(User $librarian)
    {
        $librarian->delete();
        return redirect('/librarian');
    }

    public function data()
    {
        $librarians = $this->userRepository->getUsersForRole('librarian')
            ->map(function ($librarian) {
                return [
                    'id' => $librarian->id,
                    'full_name' => $librarian->full_name,
                ];
            });
        return Datatables::make($librarians)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'librarian.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/librarian/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/librarian/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'librarian.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/librarian/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
