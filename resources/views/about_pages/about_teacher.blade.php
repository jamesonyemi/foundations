@extends('layouts.frontend')
@section('content')
    <h1>{!! $title !!}</h1>
    <div class="row">
        <div class="col-md-12">
            <div class="details">
                {!! $introduction !!}
                @foreach($teachers as $teacher)
                    <div class="row">
                        <h3> {{ $teacher->full_name }} </h3>
                        <div class="col-md-5">
                            <img src="{{ url($teacher->picture) }}" class="img-thumbnail">
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <div class="controls">
                                    {{ $teacher->about }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop