<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\TaskRequest;
use App\Models\Task;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sentinel;
use Yajra\Datatables\Facades\Datatables;

class TaskController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TaskController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;

        view()->share('type', 'task');
    }

    public function index()
    {
        $title = trans('task.tasks');
        if ($this->user->inRole('super_admin')) {
            $users = $this->userRepository->getAll()->get()
                ->filter(function ($user) {
                    return $user->inRole('admin') || $this->user->inRole('employee');
                })
                ->map(function ($user) {
                    return [
                        'name' => $user->full_name,
                        'id' => $user->id,
                    ];
                })
                ->pluck('name', 'id')->toarray();

            return view('task.index', compact('title', 'users'));
        }
        if ($this->user->inRole('librarian')) {
            $users = [$this->user->id => $this->user->full_name];

            return view('task.index', compact('title', 'users'));
        } elseif ($this->user->inRole('admin')) {
            return view('task.index_admin', compact('title'));
        } elseif ($this->user->inRole('employee')) {
            return view('task.index_employee', compact('title'));
        } else {
            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(TaskRequest $request)
    {
        $task = new Task($request->except('_token', 'full_name'));
        $task->save();

        return $task->id;
    }

    /**
     * @param Driver $driver
     * @param DriverRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Task $task, Request $request)
    {
        $task->update($request->except('_method', '_token'));
    }

    /**
     * Delete the given Driver.
     *
     * @param  int $id
     * @return Redirect
     */
    public function delete(Task $task)
    {
        $task->delete();
    }

    /**
     * Ajax Data
     * @return Datatable;
     */
    public function data()
    {
        if ($this->user->inRole('admin')) {
            return Task::where('user_id', $this->user->id)
                ->orderBy('finished', 'ASC')
                ->orderBy('task_deadline', 'DESC')
                ->get()
                ->map(function ($task) {
                    return [
                        'task_from' => $task->task_from_users->full_name,
                        'id' => $task->id,
                        'finished' => $task->finished,
                        'task_deadline' => $task->task_deadline,
                        'task_description' => $task->task_description,
                    ];
                })->toArray();
        } elseif ($this->user->inRole('super_admin')) {
            return Task::orderBy('finished', 'ASC')
                ->orderBy('task_deadline', 'DESC')
                ->get()
                ->map(function ($task) {
                    return [
                        'task_from' => $task->task_from_users->full_name,
                        'id' => $task->id,
                        'finished' => $task->finished,
                        'task_deadline' => $task->task_deadline,
                        'task_description' => $task->task_description,
                    ];
                })->toArray();
        } elseif ($this->user->inRole('librarian')) {
            return Task::where('user_id', $this->user->id)->orderBy('finished', 'ASC')
                ->orderBy('task_deadline', 'DESC')
                ->get()
                ->map(function ($task) {
                    return [
                        'task_from' => $task->task_from_users->full_name,
                        'id' => $task->id,
                        'finished' => $task->finished,
                        'task_deadline' => $task->task_deadline,
                        'task_description' => $task->task_description,
                    ];
                })->toArray();
        } else {
            return [];
        }
    }
}
