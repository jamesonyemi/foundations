@extends('layouts.secure')
@section('title')
    {{trans_choice('general.role',2)}}
@endsection
@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{{trans_choice('general.role',2)}}</h3>

            <div class="box-tools pull-right">
                @if(Sentinel::hasAccess('users.roles.create'))
                    <a href="{{ url('user/role/create') }}" class="btn btn-info btn-sm">
                        {{trans_choice('general.add',1)}} {{trans_choice('general.role',1)}}
                    </a>
                @endif
            </div>
        </div>
        <div class="box-body  responsive">
            <table class="table table-bordered table-hover table-striped" id="">
                <thead>
                <tr>
                    <th>{{trans_choice('general.name',1)}}</th>
                    <th>{{trans('general.slug')}}</th>
                    <th>{{trans('general.time_limit')}}</th>
                    <th>{{trans_choice('general.action',1)}}</th>
                </tr>
                </thead>

                <tbody>
                @foreach($data as $key)

                    <tr>
                        <td>{{ $key->name }}</td>
                        <td>{{ $key->slug}}</td>
                        <td>
                            @if($key->time_limit==1)
                                {{trans_choice('general.yes',1)}}
                            @endif
                            @if($key->time_limit==0)
                                {{trans_choice('general.no',1)}}
                            @endif

                        </td>
                        <td>
                            <div class="btn-group">

                                <button class="btn bg-blue btn-sm dropdown-toggle" type="button"
                                        data-toggle="dropdown"
                                        aria-expanded="false"><i
                                            class="fa fa-navicon"></i></button>
                                <ul class="dropdown-menu" role="menu">
                                    @if(Sentinel::hasAccess('users.roles.update'))
                                        <li>
                                            <a href="{{ url('user/role/'.$key->id.'/edit') }}"><i
                                                        class="fa fa-edit"></i>
                                                {{ trans('general.edit') }}</a>
                                        </li>
                                    @endif
                                    @if(Sentinel::hasAccess('users.roles.delete'))
                                        <li>
                                            <a href="{{ url('user/role/'.$key->id.'/delete') }}"
                                               class="delete"><i
                                                        class="fa fa-trash"></i>
                                                {{ trans('general.delete') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
