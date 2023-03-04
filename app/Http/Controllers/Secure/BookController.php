<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\ExcelfileValidator;
use App\Http\Requests\Secure\BookRequest;
use App\Http\Requests\Secure\ImportRequest;
use App\Models\Book;
use App\Models\GetBook;
use App\Repositories\BookRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SubjectRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BookController extends SecureController
{
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;

    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var ExcelRepository
     */
    private $excelRepository;

    /**
     * BookController constructor.
     *
     * @param SubjectRepository $subjectRepository
     * @param BookRepository $bookRepository
     * @param OptionRepository $optionRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        SubjectRepository $subjectRepository,
        BookRepository $bookRepository,
        OptionRepository $optionRepository,
        ExcelRepository $excelRepository
    ) {
        parent::__construct();

        $this->subjectRepository = $subjectRepository;
        $this->bookRepository = $bookRepository;
        $this->optionRepository = $optionRepository;
        $this->excelRepository = $excelRepository;

        view()->share('type', 'book');

        $columns = ['internal', 'title', 'author', 'year', 'quantity', 'remain', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('book.books');

        return view('book.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('book.new');
        $this->generateParam();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BookRequest|Request $request
     * @return Response
     */
    public function store(BookRequest $request)
    {
        $book = new Book($request->all());
        $book->company_id = session('current_company');
        $book->save();

        return redirect('/book');
    }

    /**
     * Display the specified resource.
     *
     * @param Book $book
     * @return Response
     * @internal param int $id
     */
    public function show(Book $book)
    {
        $title = trans('book.details');
        $action = 'show';
        $issued = GetBook::where('book_id', $book->id)->with('user')->get();

        return view('layouts.show', compact('title', 'book', 'action', 'issued'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Book $book
     * @return Response
     * @internal param int $id
     */
    public function edit(Book $book)
    {
        $title = trans('book.edit');
        $this->generateParam();

        return view('layouts.edit', compact('title', 'book'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BookRequest|Request $request
     * @param Book $book
     * @return Response
     * @internal param int $id
     */
    public function update(BookRequest $request, Book $book)
    {
        $book->update($request->all());
        $book->company_id = session('current_company');
        $book->save();

        return redirect('/book');
    }

    public function delete(Book $book)
    {
        $title = trans('book.delete');

        $issued = GetBook::where('book_id', $book->id)->with('user')->get();

        return view('book.delete', compact('title', 'book', 'issued'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Book $book
     * @return Response
     * @internal param int $id
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect('/book');
    }

    public function data()
    {
        $books = $this->bookRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'internal' => $book->internal,
                    'title' => $book->title,
                    'author' => $book->author,
                    'year' => $book->year,
                    'quantity' => $book->quantity,
                    'remain' => $book->remain,
                ];
            });

        return Datatables::make($books)
            ->addColumn('actions', '<a href="{{ url(\'/book/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/book/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/book/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    private function generateParam()
    {
        $subjects = $this->subjectRepository->getAll()
            ->with('direction')
            ->get()
            ->filter(function ($subject) {
                return isset($subject->direction);
            })
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'title' => $subject->direction->title.' ('.$subject->class.') '.$subject->title,
                ];
            })->pluck('title', 'id')->toArray();
        view()->share('subjects', $subjects);

        $book_categories = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'book_category')->get()
            ->map(function ($option) {
                return [
                    'title' => $option->title,
                    'value' => $option->id,
                ];
            })->pluck('title', 'value')->toArray();
        view()->share('book_categories', $book_categories);

        $borrowing_periods = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'borrowing_period')->get()
            ->map(function ($option) {
                return [
                    'title' => $option->title,
                    'value' => $option->id,
                ];
            })->pluck('title', 'value')->toArray();
        view()->share('borrowing_periods', $borrowing_periods);
    }

    public function getImport()
    {
        $title = trans('book.import_book');

        return view('book.import', compact('title'));
    }

    public function postImport(ImportRequest $request)
    {
        $title = trans('book.import_book');

        ExcelfileValidator::validate($request);

        $reader = $this->excelRepository->load($request->file('file'));

        $books = $reader->all()->map(function ($row) {
            return [
                'internal' => trim($row->internal_no),
                'title' => trim($row->title),
                'price' => trim($row->price),
                'isbn' => trim($row->isbn),
                'publisher' => trim($row->publisher),
                'version' => trim($row->version),
                'author' => trim($row->author),
                'year' => trim($row->year),
                'quantity' => trim($row->quantity),
            ];
        });
        $this->generateParam();

        return view('book.import_list', compact('books', 'title'));
    }

    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'internal'=>$request->internal[$item],
                'title'=>$request->title[$item],
                'price'=>$request->price[$item],
                'publisher'=>$request->publisher[$item],
                'isbn'=>$request->isbn[$item],
                'version'=>$request->version[$item],
                'author'=>$request->author[$item],
                'year'=>$request->year[$item],
                'quantity'=>$request->quantity[$item],
                'subject_id'=>$request->subject_id[$item],
                'option_id_category'=>$request->option_id_category[$item],
                'option_id_borrowing_period'=>$request->option_id_borrowing_period[$item],
            ];
            $book = new Book($import_data);
            $book->save();
        }

        return redirect('/student');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/books.xlsx'));
    }
}
