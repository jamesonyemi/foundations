<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\MealTypeRequest;
use App\Models\MealType;
use App\Repositories\MealTypeRepository;
use Yajra\DataTables\Facades\DataTables;

class MealTypeController extends SecureController
{
    /**
     * @var MealTypeRepository
     */
    private $mealTypeRepository;

    /**
     * MealTypeController constructor.
     * @param MealTypeRepository $mealTypeRepository
     */
    public function __construct(MealTypeRepository $mealTypeRepository)
    {
        parent::__construct();

        $this->mealTypeRepository = $mealTypeRepository;

        view()->share('type', 'meal_type');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('meal_type.meal_types');

        return view('meal_type.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('meal_type.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(MealTypeRequest $request)
    {
        $mealType = new MealType($request->all());
        $mealType->save();

        return redirect('/meal_type');
    }

    /**
     * Display the specified resource.
     *
     * @param  MealType $mealType
     * @return Response
     */
    public function show(MealType $mealType)
    {
        $title = trans('meal_type.details');
        $action = 'show';

        return view('layouts.show', compact('mealType', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  MealType $mealType
     * @return Response
     */
    public function edit(MealType $mealType)
    {
        $title = trans('meal_type.edit');

        return view('layouts.edit', compact('title', 'mealType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|MealTypeRequest $request
     * @param MealType $mealType
     *
     * @return Response
     */
    public function update(MealTypeRequest $request, MealType $mealType)
    {
        $mealType->update($request->all());

        return redirect('/meal_type');
    }

    public function delete(MealType $mealType)
    {
        $title = trans('meal_type.delete');

        return view('/meal_type/delete', compact('mealType', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  MealType $mealType
     * @return Response
     */
    public function destroy(MealType $mealType)
    {
        $mealType->delete();

        return redirect('/meal_type');
    }

    public function data()
    {
        $mealTypes = $this->mealTypeRepository->getAll()
            ->get()
            ->map(function ($mealType) {
                return [
                    'id' => $mealType->id,
                    'title' => $mealType->title,
                ];
            });

        return Datatables::make($mealTypes)
            ->addColumn('actions', '<a href="{{ url(\'/meal_type/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/meal_type/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/meal_type/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }
}
