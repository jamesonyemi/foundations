<?php

namespace App\Http\Controllers\Secure;

use App\Events\Event;
use App\Events\PostCommentCreatedEvent;
use App\Events\PostCreatedEvent;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Settings;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Http\Requests\Secure\PostCommentRequest;
use App\Http\Requests\Secure\PostRequest;
use App\Models\Competency;
use App\Models\CompetencyType;
use App\Models\Employee;
use App\Models\EmployeeKpiActivity;
use App\Models\EmployeeQualification;
use App\Models\Level;
use App\Models\Position;
use App\Models\PositionCompetency;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\SchoolDirection;
use App\Notifications\KpiReviewNotification;
use App\Notifications\PostCommentNotification;
use App\Notifications\PostLikeNotification;
use App\Repositories\EmployeeRepository;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Validator;

class PostController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     * @param EmployeeRepository $employeeRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository,
        EmployeeRepository $employeeRepository
    ) {
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'post');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('position.position');
        $positions = Position::where('company_id', session('current_company'))
            ->get();

        return view('position.index', compact('title', 'positions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('position.new');


        return view('post.modalForm', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(PostRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $post = new Post();
                $post->post = $request->post2;
                $post->employee_id = session('current_employee');
                $post->save();

                /*event(new PostCreatedEvent($post));*/
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return $this->latestPosts();

    }

    /**
     * Display the specified resource.
     *
     * @param Position $position
     * @return Response
     */
    public function show(Position $position)
    {
        $title = trans('position.details');
        $competencies = $position->competencies;
        $action = 'show';
        $competencyTypes = CompetencyType::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select'.trans('position.competency_type'), '')
            ->toArray();

        return view('layouts.show', compact('position', 'title', 'action', 'competencies', 'competencyTypes'));
    }

    public function addComment(PostCommentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (! empty($request->comment)) {
                    $comment = new PostComment();
                    $comment->post_id = $request->post_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->comment;
                    $comment->save();

                    //send email to user
                    $post = Post::find($request->post_id);
                    if (GeneralHelper::validateEmail($post->employee->user->email)) {
                        @Notification::send($post->employee->user, new PostCommentNotification($post->employee->user, $post, $comment));
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        $posts = Post::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(10)
            ->get();

        return view('post._posts', compact('posts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $post
     * @return Response
     */
    public function edit(Post $post)
    {
        return view('post.modalForm', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(PostRequest $request, Post $post)
    {
        try {
            DB::transaction(function () use ($request, $post) {
                $post->post = $request->post2;
                $post->save();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return $this->latestPosts();
    }

    public function delete(Post $post)
    {
        try {
            DB::transaction(function () use ($post) {
                $post->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return $this->latestPosts();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(Post $post)
    {
        try {
            DB::transaction(function () use ($post) {
                $post->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Post Deleted Successfully</div>');
    }

    public function latestPosts()
    {
        $title = trans('applicant.applicants');

        $posts = Post::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(5)
            ->get();

        return view('post._posts', compact('title', 'posts'));
    }


    public function likePost(Request $request)
    {
        PostLike::firstOrCreate([
            'employee_id'     => session('current_employee'),
            'post_id'          => $request->post_id,
        ]);

        //send email to user
        $liker = Employee::find(session('current_employee'))->user;
        $post = Post::find($request->post_id);
        if (GeneralHelper::validateEmail($post->employee->user->email)) {
            @Notification::send($post->employee->user, new PostLikeNotification($post->employee->user, $post, $liker));
        }

        return view('post.postLikes', compact('post'));
    }



    public function findSectionLevel(Request $request)
    {
        $directions = $this->levelRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();

        return $directions;
    }
}
