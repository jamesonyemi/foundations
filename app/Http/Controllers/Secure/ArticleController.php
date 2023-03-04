<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\ArticleCommentRequest;
use App\Http\Requests\Secure\ArticleRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Http\Requests\Secure\PostCommentRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleComment;
use App\Models\Competency;
use App\Models\CompetencyType;
use App\Models\Level;
use App\Models\Position;
use App\Models\PositionCompetency;
use App\Models\Post;
use App\Models\SchoolDirection;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\Input;
use Validator;

class ArticleController extends SecureController
{
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
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'article');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'News Items';

        $articles = Article::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(30)
            ->get();

        return view('article.index', compact('title', 'articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'Create New Article';
        $articleCategories = ArticleCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select category', '')
            ->toArray();

        return view('layouts.create', compact('title', 'articleCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ArticleRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $position = new Article();
                $position->title = $request->title;
                $position->post = $request->post;
                $position->employee_id = session('current_employee');
                $position->article_category_id = $request->article_category_id;
                $position->save();

                if ($request->hasFile('image_file') != '') {
                    $file = $request->file('image_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/news/';
                    $file->move($destinationPath, $picture);
                    Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                    $position->image = $picture;
                    $position->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $articles = Article::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(10)
            ->get();


        return view('article._latest_news', compact('articles'));
    }

    /**
     * Display the specified resource.
     *
     * @param Position $position
     * @return Response
     */
    public function show(Article $article)
    {
        $title = $article->title;
        $action = 'show';
        $articles = Article::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(50)
            ->get();
        $articleCategories = ArticleCategory::with('articles')
            ->orderBy('title', 'Asc')
            ->get();

        return view('article.show', compact('article', 'title', 'action', 'articles', 'articleCategories'));
    }

    public function addCompetency(Request $request)
    {
        $position_id = $request['position_id'];
        $competency_type_ids = $request['competency_type_id'];
        $titles = $request['title'];

        $rules = [];

        foreach ($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            try {
                foreach ($titles as $index => $title) {
                    if (! empty($titles[$index])) {
                        $competency = new Competency();
                        $competency->position_id = $position_id;
                        $competency->competency_type_id = $competency_type_ids[$index];
                        $competency->company_id = session('current_company');
                        $competency->title = $title;
                        $competency->save();
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }

            /* return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;*/
            $competencies = Position::find($request['position_id'])->competencies;

            return view('position.competencies', compact('competencies'));
        }

        return response()->json(['error'=>$validator->errors()->all()]);

        /* END OF ONE*/
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Article $article
     * @return Response
     */
    public function edit(Article $article)
    {
        $title = 'Edit News Article';
        $articleCategories = ArticleCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select category', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'article', 'articleCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Article $article
     * @return Response
     */
    public function update(Request $request, Article $article)
    {
        try {
            DB::transaction(function () use ($request, $article) {
                $article->update($request->all());

                if ($request->hasFile('image_file') != '') {
                    $file = $request->file('image_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/news/';
                    $file->move($destinationPath, $picture);
                    Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                    $article->image = $picture;
                    $article->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $articles = Article::whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(10)
            ->get();

        return view('article._latest_news', compact('articles'));
    }

    public function delete(Article $article)
    {
        $title = trans('level.delete');

        return view('position.delete', compact('article', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return 'Position Deleted';
    }

    public function latestNews()
    {
        $title = trans('applicant.applicants');

        $articles = Article::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(10)
            ->get();


        return view('article._latest_news', compact('title', 'articles'));
    }

    public function ajax_featured_news()
    {
        $title = trans('applicant.applicants');

        $articles = Article::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(10)
            ->get();


        return view('article._featured_news', compact('title', 'articles'));
    }

    public function latestArticleComments(Article $article)
    {
        return view('article.comments', compact('article'));
    }

    public function addComment(ArticleCommentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (! empty($request->newsComment)) {
                    $comment = new ArticleComment();
                    $comment->article_id = $request->article_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->newsComment;
                    $comment->save();

                    /*event(new PostCommentCreatedEvent($comment));*/
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $article = Article::find($request->article_id);

        return view('article.comments', compact('article'));
    }
}
