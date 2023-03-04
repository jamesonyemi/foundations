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
                    <div class="col-1-1">
                        <div class="content">
                            <table width="100%" border="0" cellspacing="0" cellpadding="1">
                                <tbody>
                                <tr class="theader">
                                    <td>ID</td>
                                    <td>Projects</td>
                                    <td>Date Upload</td>
                                    <td>Status</td>
                                    <td>Action</td>
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
                                    <td>
                                        <a style="color: #eb3a3e !important;

                                        " href="{{  url('confirm-delete-page/'. $key->id. '/show' ) }}" class="">
                                            <i class="fa fa-trash-o fa-2x" aria-hidden="true" aria-label="delete button" ></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach

                                </tbody>
                            </table>

                        </div>
                    </div>

                </div></div>

        </form></section>


@stop
@section('scripts')


@stop
