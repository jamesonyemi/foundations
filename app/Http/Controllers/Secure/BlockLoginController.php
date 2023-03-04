<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\BlockLoginRequest;
use App\Models\BlockLogin;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BlockLoginController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * BlockUserController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        view()->share('type', 'block_login');
        $this->userRepository = $userRepository;

        $columns = ['full_name', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('block_login.block_logins');

        return view('block_login.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('block_login.new');
        $users = $users = $this->userRepository->getAll()
                                                ->where('id', '<>', $this->user->id)
                                                ->whereNotIn('id', BlockLogin::pluck('user_id'))
                                                ->get()
                                                ->filter(function ($user) {
                                                    return ! ($user->inRole('super_admin') ||
                                                            $user->inRole('admin_super_admin'));
                                                })
                                                ->map(function ($user) {
                                                    return [
                                                        'id' => $user->id,
                                                        'text' => $user->full_name,
                                                    ];
                                                })->pluck('text', 'id');

        return view('layouts.create', compact('title', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlockLoginRequest|Request $request
     * @return Response
     */
    public function store(BlockLoginRequest $request)
    {
        $block_user = new BlockLogin($request->all());
        $block_user->save();

        return redirect('/block_login');
    }

    public function delete(BlockLogin $blockLogin)
    {
        $title = trans('block_login.delete');

        return view('block_login.delete', compact('title', 'blockLogin'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BlockLogin $blockLogin
     * @return Response
     */
    public function destroy(BlockLogin $blockLogin)
    {
        $blockLogin->delete();

        return redirect('/block_login');
    }

    public function data()
    {
        $blockLogins = BlockLogin::get()
            ->map(function ($blockLogin) {
                return [
                    'id' => $blockLogin->id,
                    'full_name' => is_null($blockLogin->user) ? '' : $blockLogin->user->full_name,
                    'date' => $blockLogin->created_at->format(Settings::get('date_format')),
                ];
            });

        return Datatables::make($blockLogins)
            ->addColumn('actions', '<a href="{{ url(\'/block_login/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
