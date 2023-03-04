<?php

namespace App\Http\Controllers\Secure;

use App\Models\Message;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Sentinel;

class AdminUserMailsController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * AdminUserMailsController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        view()->share('type', 'admin_user_mail');
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $title = trans('all_mails.all_mails');
        if (Sentinel::getUser()->inRole('super_admin')) {
            $users = $this->userRepository->getAll()->get();
        } else {
            $users = $this->userRepository->getAllUsersFromSchool(session('current_company'), session('current_company_year'));
        }
        $users = $users->map(function ($user) {
            return [
                'id'        => $user->id,
                'full_name' => $user->full_name.' - '.$user->email,
            ];
        })->pluck('full_name', 'id');

        return view('admin_user_mail.index', compact('title', 'users'));
    }

    public function getMails(Request $request)
    {
        $get_mails = Message::where('to', $request->get('user_id'))->with('sender')
                             ->orderBy('id', 'desc')->get();
        $sent_mails = Message::where('from', $request->get('user_id'))->with('receiver')
                             ->orderBy('id', 'desc')->get();

        return ['get_mails' => $get_mails, 'sent_mails' => $sent_mails];
    }
}
