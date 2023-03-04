<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\SliderRequest;
use App\Models\Slider;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SliderController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'slider');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('slider.slider');

        return view('slider.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('slider.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SliderRequest $request
     * @return Response
     */
    public function store(SliderRequest $request)
    {
        $slider = new Slider($request->except('image_file'));
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $folderName = '/uploads/slider/';
            $picture = Str::random(8) .'.'.$extension;
            $slider->image = $picture;

            $destinationPath = public_path().$folderName;
            $request->file('image_file')->move($destinationPath, $picture);
        }
        $slider->save();

        return redirect('/slider');
    }

    /**
     * Display the specified resource.
     *
     * @param Slider $slider
     * @return Response
     */
    public function show(Slider $slider)
    {
        $title = trans('slider.details');
        $action = 'show';

        return view('layouts.show', compact('slider', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Slider $slider
     * @return Response
     */
    public function edit(Slider $slider)
    {
        $title = trans('slider.edit');

        return view('layouts.edit', compact('title', 'slider'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SliderRequest $request
     * @param Slider $slider
     * @return Response
     */
    public function update(SliderRequest $request, Slider $slider)
    {
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $folderName = '/uploads/blog/';
            $picture = Str::random(8) .'.'.$extension;
            $slider->image = $picture;

            $destinationPath = public_path().$folderName;
            $request->file('image')->move($destinationPath, $picture);
        }
        $slider->update($request->except('image_file'));

        return redirect('/slider');
    }

    /**
     * @param Slider $slider
     * @return Response
     */
    public function delete(Slider $slider)
    {
        $title = trans('slider.delete');

        return view('/slider/delete', compact('slider', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     * @param Slider $slider
     * @return Response
     */
    public function destroy(Slider $slider)
    {
        $slider->delete();

        return redirect('/slider');
    }

    public function data()
    {
        $slider = Slider::orderBy('position')->select(['id', 'title', 'content'])->get();

        return Datatables::make($slider)
            ->addColumn('actions', '<a href="#" class="btn btn-sm btn-default"><i class="fa fa-sort"></i> {{ trans("table.order") }}</a>
                                    <a href="{{ url(\'/slider/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/slider/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/slider/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                   <input type="hidden" name="row" value="{{$id}}" id="row">')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }

    public function reorderSlider(Request $request)
    {
        $list = $request->get('list');
        $items = explode(',', $list);
        $order = 1;
        foreach ($items as $value) {
            if ($value != '') {
                Slider::where('id', '=', $value)->update(['position' => $order]);
                $order++;
            }
        }

        return $list;
    }
}
