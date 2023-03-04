<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\IssueBookRequest;
use App\Models\BookUser;
use App\Models\GetBook;
use App\Repositories\BookUserRepository;
use DB;
use Yajra\DataTables\Facades\DataTables;

class ReservedBookController extends SecureController
{
    /**
     * @var BookUserRepository
     */
    private $bookUserRepository;

    /**
     * ReservedBookController constructor.
     * @param BookUserRepository $bookUserRepository
     */
    public function __construct(BookUserRepository $bookUserRepository)
    {
        parent::__construct();

        $this->bookUserRepository = $bookUserRepository;

        view()->share('type', 'reservedbook');

        $columns = ['name', 'book', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('reservedbook.reservedbooks');

        return view('reservedbook.index', compact('title'));
    }

    public function show(BookUser $bookUser)
    {
        $title = trans('reservedbook.issue');
        $action = 'issue';
        $available = isset($bookUser->book->id) ? $bookUser->book->availableCopies() : 0;

        return view('reservedbook.issue', compact('bookUser', 'title', 'action', 'available'));
    }

    public function issue(BookUser $bookUser, IssueBookRequest $request)
    {
        GetBook::create(['book_id' => $bookUser->book_id,
            'user_id' => $bookUser->user_id,
            'company_year_id' => $bookUser->company_year_id,
            'get_books_count' => $request->get('quantity'),
            'get_date' => date('Y-m-d'), ]);
        $bookUser->delete();

        return redirect('/reservedbook');
    }

    public function delete(BookUser $bookUser)
    {
        $title = trans('reservedbook.delete');
        $available = isset($bookUser->book->id) ? $bookUser->book->availableCopies() : 0;

        return view('reservedbook.delete', compact('bookUser', 'title', 'available'));
    }

    public function destroy($bookuserid)
    {
        $bookuser = BookUser::find($bookuserid);
        $bookuser->delete();

        return redirect('/reservedbook');
    }

    public function data()
    {
        $reservedBooks = $this->bookUserRepository->getAll()
            ->with('book', 'user')
            ->get()
            ->filter(function ($reservedBook) {
                return ! is_null($reservedBook->reserved);
            })
            ->map(function ($reservedBook) {
                return [
                    'id' => $reservedBook->id,
                    'name' => isset($reservedBook->user) ? $reservedBook->user->full_name : '',
                    'book' => $reservedBook->book->title.' '.$reservedBook->book->author.'('.$reservedBook->book->internal.')',
                ];
            });

        return Datatables::make($reservedBooks)
            ->addColumn('actions', '<a href="{{ url(\'/reservedbook/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("reservedbook.issue") }}</a>
                                     <a href="{{ url(\'/reservedbook/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("reservedbook.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
