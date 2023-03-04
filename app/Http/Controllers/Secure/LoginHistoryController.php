<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Repositories\LoginHistoryRepository;

class LoginHistoryController extends SecureController
{
    /**
     * @var LoginHistoryRepository
     */
    private $loginHistoryRepository;

    /**
     * DairyController constructor.
     * @param LoginHistoryRepository $loginHistoryRepository
     */
    public function __construct(LoginHistoryRepository $loginHistoryRepository)
    {
        parent::__construct();
        $this->loginHistoryRepository = $loginHistoryRepository;
        view()->share('type', 'login_history');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('login_history.login_history');
        $login_histories = $this->loginHistoryRepository->getAllToday()
            ->orderBy('created_at', 'desc')
            ->with('user.employee')
            ->get();

        return view('login_history.index', compact('title', 'login_histories'));
    }
}
