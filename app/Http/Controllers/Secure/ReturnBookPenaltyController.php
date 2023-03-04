<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Repositories\GetBookRepository;
use App\Repositories\ReturnBookRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReturnBookPenaltyController extends SecureController
{
    /**
     * @var GetBookRepository
     */
    private $getBookRepository;

    /**
     * @var ReturnBookRepository
     */
    private $returnBookRepository;

    public function __construct(
        GetBookRepository $getBookRepository,
        ReturnBookRepository $returnBookRepository
    ) {
        parent::__construct();

        view()->share('type', 'return_book_penalty');

        $columns = ['user', 'book', 'book_get', 'late_days', 'amount'];
        view()->share('columns', $columns);

        $this->getBookRepository = $getBookRepository;
        $this->returnBookRepository = $returnBookRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('return_book_penalty.return_book_penalty');

        return view('return_book_penalty.index', compact('title'));
    }

    public function data()
    {
        $return_book_penalties = $this->getBookRepository->getAll()
            ->groupBy('user_id')
            ->groupBy('book_id')
            ->with('user', 'book', 'book.borrowingPeriod')
            ->select('get_books.*', DB::raw('sum(get_books_count) as book_get_sum'))
            ->distinct()
            ->get()
            ->filter(function ($item) {
                $issued = Carbon::createFromTimestamp(strtotime($item->get_date));
                $today = new Carbon();
                $difference = $issued->diff($today)->days;
                if (isset($item->book->borrowingPeriod) &&
                    $difference > $item->book->borrowingPeriod->value
                    && ($item->book_get_sum > $this->returnBookRepository->getSumForUserAndBook($item->user_id, $item->book_id))) {
                    return $item;
                }
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user' => isset($item->user) ? $item->user->full_name : '',
                    'book' => isset($item->book) ? $item->book->title : '',
                    'book_get' => Carbon::createFromTimestamp(strtotime($item->get_date))->format(Settings::get('date_format')),
                    'late_days' =>  Carbon::createFromTimestamp(strtotime($item->get_date))->diff(new Carbon())->days,
                    'amount' => ((Carbon::createFromTimestamp(strtotime($item->get_date))->diff(new Carbon())->days *
                        $item->book->borrowingPeriod->value) *
                        ($item->book_get_sum -
                            ($this->returnBookRepository->getSumForUserAndBook($item->user_id, $item->book_id)))),
                ];
            });

        return Datatables::make($return_book_penalties)
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
