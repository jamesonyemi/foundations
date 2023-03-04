<?php

namespace App\Http\Controllers\Secure;

use App\Models\Book;
use App\Models\BookUser;
use App\Repositories\BookRepository;
use Sentinel;
use DB;
use Yajra\DataTables\Facades\DataTables;

class BookUserController extends SecureController
{
    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * BookUserController constructor.
     * @param BookRepository $bookRepository
     */
    public function __construct(BookRepository $bookRepository)
    {
        parent::__construct();

        $this->bookRepository = $bookRepository;

        view()->share('type', 'bookuser');

        $columns = ['title', 'author', 'subject', 'actions'];
        view()->share('columns', $columns);
    }

    public function index()
    {
        $title = trans('bookuser.bookuser');
        return view('bookuser.index', compact('title'));
    }

    public function data()
    {
        $books = $this->bookRepository->getAll()
            ->with('subject')
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'subject' => isset($book->subject) ? $book->subject->title : "",
                    'reserved' => (BookUser::whereBookId($book->id)->whereUserId($this->user->id)->whereNotNull('reserved')->count() > 0) ? 1 : 0
                ];
            });
        return Datatables::make($books)
            ->addColumn('actions', '@if($reserved == 0)<a href="{{ url(\'/bookuser/\' . $id . \'/reserve\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-list-alt"></i> {{ trans("bookuser.reserve") }}</a>
                                      @else <a href="" class="btn btn-success disabled btn-sm" >
                                            <i class="fa fa-check-circle"></i> {{ trans("bookuser.reserved") }}</a>
                                      @endif')
            ->removeColumn('id', 'reserved')
             ->rawColumns([ 'actions' ])->make();
    }

    public function reserve(Book $book)
    {
        $bookUser = new BookUser();
        $bookUser->book_id = $book->id;
        $bookUser->user_id = $this->user->id;
        $bookUser->reserved = date('Y-m-d');
        $bookUser->save();

        return redirect()->back();
    }
}
