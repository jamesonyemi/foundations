@extends('layouts.secure')
@section('content')
    <section class="dashboard">

            <div class="_program-wrap"><h1>Tackling Social Development Concerns in Ghana Program</h1></div>
            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>My Project Reports</h3>
                        <span class="pull-right"><a href="{{  url('project/create' ) }}" class="btn">Create New</a></span>

                        <p><span>Client ID: {{$currentEmployee->sID}}</span></p>
                    </div>
                </div>
            </div>
@if($projects->count() > 0)
        <div class="grid grid-pad">
            <div class="publication">
                <div class="col-1-1">
                    <div class="content">
                        <table width="100%" border="0" cellspacing="0" cellpadding="1">
                            <tbody>
                            <tr class="theader">
                                <td>ID</td>
                                <td>Projects</td>
                                <td>Date Upload</td>
                                <td>Status</td>
                            </tr>
                            @foreach( $projects as $key)
                                <tr>
                                    <td>{{$key->id}}</td>
                                    <td><a href="{{  url('project/'. $key->id. '/show' ) }}">{{$key->company->title}}</a></td>
                                    <td>{{@$key->created_at/*->diffForHumans()*/}}</td>

                                    <td>
                                        @if($key->approval == 1)
                                            <a href="{{  url('project/'. $key->id. '/show' ) }}" class="btn">Approved</a>
                                        @else
                                            <a href="{{  url('project/'. $key->id. '/show' ) }}" class="btn bg-warning">Pending</a>
                                        @endif

                                    </td>

                                </tr>
                            @endforeach

                            </tbody>
                        </table>

                    </div>
                </div>

            </div></div>
@else
        <div class="grid grid-pad">
                <div class="publication">
                    <div class="col-1-1">
                        <div class="content">
                            NO reports uploaded
                        </div>
                    </div>

                </div></div>
@endif


    </section>
@stop
@section('styles')


@stop

@section('scripts')


@stop



