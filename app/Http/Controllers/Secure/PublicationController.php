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
use App\Http\Requests\Secure\PublicationCommentRequest;
use App\Http\Requests\Secure\PublicationRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleComment;
use App\Models\Competency;
use App\Models\CompetencyType;
use App\Models\Level;
use App\Models\Position;
use App\Models\PositionCompetency;
use App\Models\Post;
use App\Models\Publication;
use App\Models\PublicationCategory;
use App\Models\PublicationComment;
use App\Models\SchoolDirection;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\Input;
use Validator;

class PublicationController extends SecureController
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

        view()->share('type', 'publication');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'News Items';

        $publications = Article::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(30)
            ->get();

        return view('publication.index', compact('title', 'publications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'Create New Publication';
        $publicationCategories = PublicationCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select category', '')
            ->toArray();

        return view('layouts.create', compact('title', 'publicationCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(PublicationRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $publication = new Publication();
                $publication->title = $request->title;
                $publication->post = $request->post;
                $publication->employee_id = session('current_employee');
                $publication->publication_category_id = $request->publication_category_id;
                $publication->save();

                if ($request->hasFile('image_file') != '') {
                    $file = $request->file('image_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = str_replace(' ', '_', $request->title).'_'.Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/publications/';
                    $file->move($destinationPath, $picture);
                    $publication->picture = $picture;
                    $publication->save();
                }
                if ($request->hasFile('file_file') != '') {
                    $file = $request->file('file_file');
                    $extension = $file->getClientOriginalExtension();
                    $pictureFile = str_replace(' ', '_', $request->title).'_'.Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/publications/';
                    $file->move($destinationPath, $pictureFile);
                    $publication->file = $pictureFile;
                    $publication->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }


        return response('Publication Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param Publication $publication
     * @return Response
     */
    public function show(Publication $publication)
    {
        $title = $publication->title;
        $action = 'show';

        $publications = Publication::with('employee', 'comments')
            ->orderBy('id', 'Desc')
            ->take(50)
            ->get();

        $publicationCategories = PublicationCategory::with('publications')
            ->orderBy('title', 'Asc')
            ->get();

        return view('publication.show', compact('publication', 'title', 'action', 'publications', 'publicationCategories'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Publication $publication
     * @return Response
     */
    public function edit(Publication $publication)
    {
        $title = 'Edit Publication';
        $publicationCategories = PublicationCategory::get()
            ->pluck('title', 'id')
            ->prepend('Select category', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'publication', 'publicationCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Article $article
     * @return Response
     */
    public function update(PublicationRequest $request, Publication $publication)
    {
        try {
            DB::transaction(function () use ($request, $publication) {
                $publication->update($request->all());

                if ($request->hasFile('image_file') != '') {
                    $file = $request->file('image_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = str_replace(' ', '_', $request->title).'_'.Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/publications/';
                    $file->move($destinationPath, $picture);
                    Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                    $publication->picture = $picture;
                    $publication->save();
                }

                if ($request->hasFile('file_file') != '') {
                    $file = $request->file('file_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = str_replace(' ', '_', $request->title).'_'.Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/publications/';
                    $file->move($destinationPath, $picture);
                    $publication->file = $picture;
                    $publication->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('Publication Updated Successfully');
    }

    public function delete(Publication $publication)
    {
        $publication->delete();

        return 'Pubication Deleted';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(Publication $publication)
    {
        $publication->delete();

        return 'Pubication Deleted';
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



    public function latestPublicationComments(Publication $publication)
    {
        return view('publication.comments', compact('publication'));
    }


    public function addComment(PublicationCommentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (! empty($request->newsComment)) {
                    $comment = new PublicationComment();
                    $comment->publication_id = $request->publication_id;
                    $comment->employee_id = session('current_employee');
                    $comment->comment = $request->newsComment;
                    $comment->save();

                    /*event(new PostCommentCreatedEvent($comment));*/
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $publication = Publication::find($request->publication_id);

        return view('publication.comments', compact('publication'));
    }
}
