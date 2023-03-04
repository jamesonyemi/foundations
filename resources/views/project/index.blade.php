@extends('layouts.secure')
@section('content')
    <section class="dashboard">
        <form action method="post">

            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>Clients Projects</h3>
                    </div>
                </div>
            </div>


            <div class="grid grid-pad">
                <div class="publication">
                    <div class="col-1-2">
                        <div class="content">
                            @foreach( $projects as $key)
                                <a href="{{  url($type.'/'. $key->id. '/show' ) }}" >{{$key->title}}</a>
                            @endforeach
                        </div>
                    </div>

                </div></div>

        </form></section>
@stop
@section('styles')


@stop

@section('scripts')


@stop



