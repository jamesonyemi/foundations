<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\MealRequest;
use App\Models\Meal;
use App\Models\MealType;
use App\Repositories\MealRepository;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MealController extends SecureController
{
    /**
     * @var MealRepository
     */
    private $mealRepository;

    /**
     * MealController constructor.
     * @param MealRepository $mealRepository
     */
    public function __construct(MealRepository $mealRepository)
    {
        parent::__construct();

        $this->mealRepository = $mealRepository;

        view()->share('type', 'meal');

        $columns = ['title', 'meal_type', 'serve_start_date', 'serve_end_date'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('meal.meals');

        return view('meal.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('meal.new');
        $meal_types = MealType::pluck('title', 'id');

        return view('layouts.create', compact('title', 'meal_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(MealRequest $request)
    {
        $meal = new Meal($request->all());
        $meal->save();

        return redirect('/meal');
    }

    /**
     * Display the specified resource.
     *
     * @param  Meal $meal
     * @return Response
     */
    public function show(Meal $meal)
    {
        $title = trans('meal.details');
        $action = 'show';

        return view('layouts.show', compact('meal', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Meal $meal
     * @return Response
     */
    public function edit(Meal $meal)
    {
        $meal_types = MealType::pluck('title', 'id');
        $title = trans('meal.edit');

        return view('layouts.edit', compact('title', 'meal', 'meal_types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|MealRequest $request
     * @param Meal $meal
     *
     * @return Response
     */
    public function update(MealRequest $request, Meal $meal)
    {
        $meal->update($request->all());

        return redirect('/meal');
    }

    public function delete(Meal $meal)
    {
        $title = trans('meal.delete');

        return view('/meal/delete', compact('meal', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Meal $meal
     * @return Response
     */
    public function destroy(Meal $meal)
    {
        $meal->delete();

        return redirect('/meal');
    }

    public function data()
    {
        $meals = $this->mealRepository->getAll()
            ->orderBy('serve_end_date', 'desc')
            ->orderBy('serve_start_date', 'desc')
            ->get()
            ->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'meal_type' => is_null($meal->meal_type) ? '-' : $meal->meal_type->title,
                    'serve_start_date' => $meal->serve_start_date,
                    'serve_end_date' => $meal->serve_end_date,
                ];
            });

        return Datatables::make($meals)
            ->addColumn('actions', '<a href="{{ url(\'/meal/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/meal/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/meal/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }

    public function mealTable()
    {
        $title = trans('meal.meal_table_today');
        $meals_yesterday = $this->mealRepository->getAllForDate(Carbon::now()->addDay(-1)->format('Y-m-d'))->get();
        $meals_today = $this->mealRepository->getAllForDate(Carbon::now()->format('Y-m-d'))->get();
        $meals_tomorrow = $this->mealRepository->getAllForDate(Carbon::now()->addDay(1)->format('Y-m-d'))->get();

        return view('meal.meal_table', compact('title', 'meals_today', 'meals_tomorrow', 'meals_yesterday'));
    }
}
