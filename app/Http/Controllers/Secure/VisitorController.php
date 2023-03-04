<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Helpers\Settings;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\VisitorRequest;
use App\Models\User;
use App\Models\Visitor;
use App\Repositories\UserRepository;
use Sentinel;

class VisitorController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TeacherController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;

        $this->middleware('authorized:visitor.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:visitor.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:visitor.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:visitor.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'visitor');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('visitor.visitor');

        return view('visitor.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('visitor.new');
        $custom_fields = CustomFormUserFields::getCustomUserFields('visitor');

        return view('layouts.create', compact('title', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorRequest $request
     *
     * @return Response
     */
    public function store(VisitorRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->all());

        $role = Sentinel::findRoleBySlug('visitor');
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

        $visitor = new Visitor();
        $visitor->user_id = $user->id;
        $visitor->save();

        $visitor->visitor_no = Settings::get('visitor_card_prefix').$visitor->id;
        $visitor->save();

        CustomFormUserFields::storeCustomUserField('visitor', $user->id, $request);

        return redirect('/visitor');
    }

    /**
     * Display the specified resource.
     *
     * @param  User $visitor
     *
     * @return Response
     */
    public function show(User $visitor)
    {
        $title = trans('visitor.details');
        $action = 'show';

        return view('layouts.show', compact('visitor', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $visitor
     *
     * @return Response
     */
    public function edit(User $visitor)
    {
        $title = trans('visitor.edit');
        $custom_fields = CustomFormUserFields::fetchCustomValues('visitor', $visitor->id);

        return view('layouts.edit', compact('title', 'visitor', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VisitorRequest $request
     * @param User $visitor
     *
     * @return Response
     */
    public function update(VisitorRequest $request, User $visitor)
    {
        if ($request->password != '') {
            $visitor->password = bcrypt($request->password);
        }
        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $visitor->picture = $picture;
            $visitor->save();
        }
        $visitor->update($request->except('password', 'image_file'));
        CustomFormUserFields::updateCustomUserField('visitor', $visitor->id, $request);

        return redirect('/visitor');
    }

    /**
     * @param User $visitor
     *
     * @return Response
     */
    public function delete(User $visitor)
    {
        $title = trans('visitor.delete');

        return view('/visitor/delete', compact('visitor', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $visitor
     *
     * @return Response
     */
    public function destroy(User $visitor)
    {
        Visitor::where('user_id', '=', $visitor->id)->delete();

        $visitor->delete();

        return redirect('/visitor');
    }

    public function data(Datatables $datatables)
    {
        $visitors = $this->userRepository->getUsersForRole('visitor')
                                         ->map(function ($user) {
                                             return [
                                                 'id'         => $user->id,
                                                 'full_name'  => $user->full_name,
                                                 'visitor_no' => $user->visitor->last()->visitor_no,
                                                 'email'      => $user->email,
                                             ];
                                         });

        return Datatables::make($visitors)
                          ->addColumn('actions', ' @if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'visitor.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/visitor/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @endif
                                     <a href="{{ url(\'/visitor_card/\' . $id ) }}"  target="_blank" class="btn btn-success btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("visitor.visitor_card") }}</a>
                                      @if(!Sentinel::inRole(\'admin\') || (Sentinel::inRole(\'admin\') && in_array(\'visitor.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/visitor/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
                          ->removeColumn('id')
                          ->rawColumns(['actions'])->make(false);
    }
}
