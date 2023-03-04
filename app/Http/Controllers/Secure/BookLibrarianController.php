<?php

namespace App\Http\Controllers\Secure;

use App\Models\BookUser;
use App\Models\GetBook;
use App\Models\Invoice;
use App\Models\ReturnBook;
use App\Models\User;
use App\Models\Book;
use App\Repositories\BookRepository;
use App\Repositories\BookUserRepository;
use App\Repositories\GetBookRepository;
use App\Repositories\ReturnBookRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use DB;
use Illuminate\Routing\Controller;

class BookLibrarianController extends SecureController
{
    /**
     * @var UserRepository
     */
    private $userbRepository;
    /**
     * @var BookRepository
     */
    private $bookRepository;
    /**
     * @var BookUserRepository
     */
    private $bookUserRepository;
    /**
     * @var GetBookRepository
     */
    private $getBookRepository;
    /**
     * @var ReturnBookRepository
     */
    private $returnBookRepository;

    /**
     * BookLibrarianController constructor.
     *
     * @param UserRepository $userbRepository
     * @param BookRepository $bookRepository
     * @param BookUserRepository $bookUserRepository
     * @param GetBookRepository $getBookRepository
     * @param ReturnBookRepository $returnBookRepository
     */
    public function __construct(
        UserRepository $userbRepository,
        BookRepository $bookRepository,
        BookUserRepository $bookUserRepository,
        GetBookRepository $getBookRepository,
        ReturnBookRepository $returnBookRepository
    ) {
        parent::__construct();

        $this->userbRepository      = $userbRepository;
        $this->bookRepository       = $bookRepository;
        $this->bookUserRepository   = $bookUserRepository;
        $this->getBookRepository    = $getBookRepository;
        $this->returnBookRepository = $returnBookRepository;

        view()->share('type', 'booklibrarian');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('booklibrarian.find');

        return view('booklibrarian.index', compact('title'));
    }

    public function getUsers(Request $request)
    {
        $userb  = trim($request['userb']);
        $words  = explode(" ", $userb);
        $userbs =  [];
        foreach ($words as $word) {
            $result = $this->userbRepository->getAll()
                                            ->where('first_name', 'LIKE', '%' . $word . '%')
                                            ->orWhere('last_name', 'LIKE', '%' . $word . '%')
                                            ->get()
                                            ->map(function ($userb) {
                                                return [
                                                    "value" => $userb->id,
                                                    "label" => $userb->full_name,
                                                ];
                                            })
                                            ->toArray();
            $userbs = array_merge($userbs, $result);
        }

        return response()->json($userbs);
    }

    public function issueReturnBook(User $user)
    {
        $title = trans('booklibrarian.find');
        if ($user == null) {
            return response()->json(trans('booklibrarian.no_userb'));
        } else {
            $bookuserb = $this->getBookRepository->getAllForUser($user->id)
                                                 ->groupBy('book_id')
                                                 ->with('book')
                                                 ->select('get_books.*', DB::raw('sum(get_books_count) as book_get_sum'))
                                                 ->get()
                                                 ->filter(function ($item) use ($user) {
                                                     return ( $item->book_get_sum > $this->returnBookRepository->getSumForUserAndBook($user->id, $item->book_id) );
                                                 })
                                                 ->map(function ($item) use ($user) {
                                                     return [
                                                         "id"             => $item->id,
                                                         "internal"       => isset($item->book->internal) ? $item->book->internal : "",
                                                         "title"          => isset($item->book->title) ? $item->book->title : "",
                                                         "author"         => isset($item->book->author) ? $item->book->author : "",
                                                         "book_get_count" => ( $item->book_get_sum ) - ( $this->returnBookRepository->getSumForUserAndBook($item->user_id, $item->book_id) ),
                                                     ];
                                                 });

            return view('booklibrarian.issuereturn', compact('title', 'user', 'bookuserb'));
        }
    }

    public function returnBook(GetBook $getBook, $return_books)
    {
        if (is_int(intval($return_books))) {
            $return_book = ReturnBook::create([
                'book_id'            => $getBook->book_id,
                'user_id'            => $getBook->user_id,
                'school_year_id'     => session('current_company_year'),
                'return_date'        => date('Y-m-d'),
                'return_books_count' => $return_books
            ]);

            if (Settings::get('late_return_book_make_invoice') == 'true' && isset($getBook->book->borrowingPeriod)) {
                $issued     = new Carbon($getBook->get_date);
                $today      = new Carbon(date('Y-m-d'));
                $difference = $issued->diff($today)->days;
                if ($difference > $getBook->book->borrowingPeriod->value) {
                    Invoice::create([
                        'title'       => trans('booklibrarian.book_late_return'),
                        'description' => trans('booklibrarian.book_late_return_desc', [ 'days' => $difference ]),
                        'amount'      => ( $difference * $getBook->book->borrowingPeriod->value ) * $return_books,
                        'user_id'     => $getBook->user_id
                    ]);
                }
            }
            $getBooks    = $this->getBookRepository
                ->getSumForUserAndBook($getBook->user_id, $getBook->book_id);
            $returnBooks = $this->returnBookRepository
                ->getSumForUserAndBook($getBook->user_id, $getBook->book_id);
            if ($getBooks == $returnBooks) {
                $return_book->delete();
                $getBook->delete();
            }
        }
    }

    public function getBooks(Request $request)
    {
        $book  = trim($request['book']);
        $words = explode(" ", $book);
        $books =  [];
        foreach ($words as $word) {
            $result = $this->bookRepository->getAll()
                                           ->where('title', 'LIKE', '%' . $word . '%')
                                           ->orWhere('publisher', 'LIKE', '%' . $word . '%')
                                           ->orWhere('author', 'LIKE', '%' . $word . '%')
                                           ->orWhere('internal', 'LIKE', '%' . $word . '%')
                                           ->orWhere('isbn', 'LIKE', '%' . $word . '%')
                                           ->get()
                                           ->map(function ($book) {
                                               return [
                                                   "value" => $book->id,
                                                   "label" => $book->title . ' - ' . $book->author . '(' . $book->internal . ')',
                                               ];
                                           })->toArray();
            $books  = array_merge($books, $result);
        }

        return response()->json($books);
    }

    public function getBook(Book $book, User $userb)
    {
        return view("booklibrarian.book", compact('book', 'userb'));
    }

    public function issueBook(User $user, Book $book, $get_books)
    {
        if (is_int(intval($get_books))) {
            GetBook::create([
                'user_id'         => $user->id,
                'book_id'         => $book->id,
                'get_date'        => date('Y-m-d'),
                'get_books_count' => intval($get_books),
                'school_year_id'  => session('current_company_year')
            ]);
        }
        $userb = $user;

        return Controller::callAction('issueReturnBook', [ $userb ]);
    }

    public function issueReservedBook(BookUser $bookUser, $get_books)
    {
        if (is_int(intval($get_books))) {
            GetBook::create([
                'book_id'         => $bookUser->book_id,
                'user_id'         => $bookUser->user_id,
                'school_year_id'  => session('current_company_year'),
                'get_date'        => date('Y-m-d'),
                'get_books_count' => $get_books
            ]);

            $bookUser->delete();
        }
    }
}
