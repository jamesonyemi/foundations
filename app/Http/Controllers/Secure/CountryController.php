<?php

namespace App\Http\Controllers\Secure;

use App\Models\Country;
use App\Http\Requests\Secure\CountryRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CountryController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'country');

        $columns = ['title', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('country.countries');
        return view('country.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('country.new');
        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CountryRequest|Request $request
     * @return Response
     */
    public function store(CountryRequest $request)
    {
        $country = new Country($request->all());
        $country->save();
        return redirect("/country");
    }

    /**
     * Display the specified resource.
     *
     * @param Country $country
     * @return Response
     */
    public function show(Country $country)
    {
        $title = trans('country.details');
        $action = 'show';
        return view('layouts.show', compact('title', 'country', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Country $country
     * @return Response
     */
    public function edit(Country $country)
    {
        $title = trans('country.edit');
        return view('layouts.edit', compact('title', 'country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Country|Request $request
     * @param Country $country
     * @return Response
     */
    public function update(CountryRequest $request, Country $country)
    {
        $country->update($request->all());
        $country->save();
        return redirect('/country');
    }

    public function delete(Country $country)
    {
        $title = trans('country.delete');
        return view('country.delete', compact('title', 'country'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Country $country
     * @return Response
     */
    public function destroy(Country $country)
    {
        $country->delete();
        return redirect('/country');
    }

    public function data()
    {
        $countries = Country::get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'title' => $country->name
                    ];
            });

        return Datatables::make($countries)
            ->addColumn('actions', '<a href="{{ url(\'/country/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/country/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/country/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }
}
