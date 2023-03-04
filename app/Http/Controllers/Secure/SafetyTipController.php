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
use App\Http\Requests\Secure\SafetyTipCommentRequest;
use App\Http\Requests\Secure\SafetyTipRequest;
use App\Models\Article;
use App\Models\Competency;
use App\Models\CompetencyType;
use App\Models\EmployeeQualification;
use App\Models\Level;
use App\Models\Position;
use App\Models\PositionCompetency;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\SafetyTip;
use App\Models\SafetyTipComment;
use App\Models\SchoolDirection;
use App\Models\SupplierDocument;
use App\Notifications\KpiReviewNotification;
use App\Notifications\PostCommentNotification;
use App\Repositories\EmployeeRepository;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Validator;

class SafetyTipController extends SecureController
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

        view()->share('type', 'safetyTip');
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

        return view('safetyTip.index', compact('title', 'positions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('position.new');


        return view('safetyTip.modalForm', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(SafetyTipRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if ($request->hasFile('file') != '') {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/assets/media/safety/';
                    $file->move($destinationPath, $document);

                    $safetyTip = new SafetyTip();
                    $safetyTip->company_id = session('current_company');
                    $safetyTip->image = $document;
                    $safetyTip->save();
                }


                /*event(new PostCreatedEvent($post));*/
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return 'Uploaded successfully';

    }

    /**
     * Display the specified resource.
     *
     * @param Position $position
     * @return Response
     */
    public function show(SafetyTip $safetyTip)
    {
        $title = trans('position.details');
        $action = 'show';

        return view('safetyTip._details', compact('safetyTip', 'title', 'action'));
    }


    public function safetyTipComments(SafetyTip $safetyTip)
    {
        return view('safetyTip.comments', compact('safetyTip'));
    }


    public function addComment(SafetyTipCommentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (! empty($request->comment)) {
                    $comment = new SafetyTipComment();
                    $comment->safety_tip_id = $request->safety_tip_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->comment;
                    $comment->save();

                    //send email to user
                    /*$post = Post::find($request->post_id);
                    if (GeneralHelper::validateEmail($post->employee->user->email)) {
                        @Notification::send($post->employee->user, new PostCommentNotification($post->employee->user, $post, $comment));
                    }*/
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        $safetyTip = SafetyTip::find($request->safety_tip_id);

        return view('safetyTip.comments', compact('safetyTip'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $posts
     * @return Response
     */
    public function edit(SafetyTip $safetyTip)
    {
        return view('safetyTip.modalForm', compact('safetyTip'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(SafetyTipCommentRequest $request, SafetyTip $safetyTip)
    {
        try {
            DB::transaction(function () use ($request, $safetyTip) {
                $safetyTip->post = $request->post2;
                $safetyTip->save();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return $this->latestPosts();
    }

    public function delete(SafetyTip $safetyTip)
    {
        try {
            DB::transaction(function () use ($safetyTip) {
                $safetyTip->delete();
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
    public function destroy(SafetyTip $safetyTip)
    {
        try {
            DB::transaction(function () use ($safetyTip) {
                $safetyTip->delete();
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
            ->take(10)
            ->get();

        return view('post._posts', compact('title', 'posts'));
    }

}
