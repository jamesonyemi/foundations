<?php

namespace App\Http\Controllers\Secure;

use App\Repositories\GetBookRepository;
use App\Repositories\ReturnBookRepository;
use Sentinel;
use DB;
use Yajra\DataTables\Facades\DataTables;

class BorrowedBookController extends SecureController
{
    /**
     * @var GetBookRepository
     */
    private $getBookRepository;
    /**
     * @var ReturnBookRepository
     */
    private $returnBookRepository;

    /**
     * BorrowedBookController constructor.
     * @param GetBookRepository $getBookRepository
     * @param ReturnBookRepository $returnBookRepository
     */
    public function __construct(
        GetBookRepository $getBookRepository,
        ReturnBookRepository $returnBookRepository
    ) {
    
        parent::__construct();

        $this->getBookRepository = $getBookRepository;
        $this->returnBookRepository = $returnBookRepository;

        view()->share('type', 'borrowedbook');

        $columns = ['title', 'author', 'book_get_count', 'actions'];
        view()->share('columns', $columns);
    }

    public function index()
    {
        $title = trans('borrowedbook.borrowedbooks');
        return view('borrowedbook.index', compact('title'));
    }

    public function data()
    {
        if ($this->user->inRole('student') || $this->user->inRole('teacher')) {
            $user_id = $this->user->id;
        } else {
            $user_id = session('current_student_user_id');
        }
        $bookUsers = $this->getBookRepository->getAllForUser($user_id)
            ->groupBy('book_id')
            ->with('book')
            ->select('book_id', 'user_id', DB::raw('sum(get_books_count) as book_get_sum'))
            ->get()
            ->filter(function ($item) use ($user_id) {
                return ($item->book_get_sum > $this->returnBookRepository->getSumForUserAndBook($user_id, $item->id));
            })
            ->map(function ($bookUser) {
                return [
                    "title" => isset($bookUser->book) ? $bookUser->book->title : "",
                    "author" => isset($bookUser->book) ? $bookUser->book->author : "",
                    "book_get_count" => ($bookUser->book_get_sum) - ($this->returnBookRepository->getSumForUserAndBook($bookUser->user_id, $bookUser->book_id)),
                ];
            });
        return Datatables::make($bookUsers) ->rawColumns([ 'actions' ])->make();
    }
}
