<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\FeedbackRequest;
use App\Models\Feedback;
use App\Models\FeedbackType;
use App\Models\Option;
use App\Repositories\FeedbackRepository;
use Yajra\DataTables\Facades\DataTables;

class FeedbackController extends SecureController
{
    /**
     * @var FeedbackRepository
     */
    private $feedbackRepository;

    /**
     * FeedbackController constructor.
     * @param FeedbackRepository $feedbackRepository
     */
    public function __construct(FeedbackRepository $feedbackRepository)
    {
        parent::__construct();

        $this->feedbackRepository = $feedbackRepository;

        view()->share('type', 'feedback');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('feedback.feedback');

        return view('feedback.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('feedback.new');
        $feedback_type = Option::where('category', 'feedback_type')->pluck('title', 'value')->toArray();

        return view('layouts.create', compact('title', 'subjects', 'feedback_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(FeedbackRequest $request)
    {
        $feedback = new Feedback($request->all());
        $feedback->user_id = $this->user->id;
        $this->getRole($feedback);
        $feedback->save();

        return redirect('/feedback');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Feedback $feedback)
    {
        $title = trans('feedback.details');
        $action = 'show';

        return view('layouts.show', compact('feedback', 'title', 'action'));
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Feedback $feedback)
    {
        $title = trans('feedback.delete');

        return view('/feedback/delete', compact('feedback', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return redirect('/feedback');
    }

    public function data()
    {
        $feedbacks = $this->feedbackRepository->getAll()->where('user_id', $this->user->id)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'title' => $feedback->title,
                    'feedback_type' => $feedback->feedback_type,
                    'created_at' => $feedback->created_at->format('d.m.Y. H:s'),
                ];
            });

        return Datatables::make($feedbacks)
            ->addColumn('actions', '<a href="{{ url(\'/feedback/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/feedback/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }

    /**
     * @param $feedback
     */
    public function getRole($feedback)
    {
        if ($this->user->inRole('admin')) {
            $feedback->role = 'admin';
        } elseif ($this->user->inRole('parent')) {
            $feedback->role = 'parent';
        } elseif ($this->user->inRole('student')) {
            $feedback->role = 'student';
        } elseif ($this->user->inRole('teacher')) {
            $feedback->role = 'teacher';
        } else {
            $feedback->role = 'librarian';
        }
    }
}
