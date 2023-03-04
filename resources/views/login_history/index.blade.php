@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{--{{$fees->count()}}--}}
{{-- Content --}}
@section('content')
<div class="card card-custom">
    <div class="card-header flex-wrap py-5">
        <div class="card-title">
            <h3 class="card-label">{{$title}}
                <div class="text-muted pt-2 font-size-sm">{{--multiple controls examples--}}</div></h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Dropdown-->
            <!--end::Dropdown-->
            <!--begin::Button-->
            {{--<a href="#" class="btn btn-primary font-weight-bolder"  onclick="showAdd()">
                <i class="la la-plus" ></i>Add New Position</a>--}}
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table display nowrap table-separate table-head-custom table-checkable" id="kt_datatable">
            <thead>
            <tr>
                <th>{{ 'date' }}</th>
                <th>{{ 'user' }}
                <th>{{ 'Company' }}
                <th>{{ 'IP Address' }}
                <th>{{ 'Browser' }}
            </tr>
            </thead>
            <tbody>


            @foreach($login_histories as $key)

                <tr>
                    <td>{{ @$key->created_at }}</td>
                    <td>{{ @$key->user->full_name }}</td>
                    <td>{{ @$key->user->employee->company->title }}</td>
                    <td>{{ $key->ip_address }}</td>
                    <td>{{ $key->user_agent }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>

@stop

@section('styles')


@stop

@section('scripts')

@stop
