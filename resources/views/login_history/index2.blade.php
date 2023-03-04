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
                <th>{{ 'IP Address' }}
                <th>{{ 'Browser' }}
            </tr>
            </thead>
            <tbody>


            @foreach($login_histories as $key)

                <tr>
                    <td>{{ @$key->created_at }}</td>
                    <td>{{ @$key->user->full_name }}</td>
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


<script >
    function showAdd() {
        $('#add_modal').modal('show');
        $("body").addClass("modal-open");

        var get_url = "{{ url($type.'/create') }}";

        $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif')!!} Loading.....</div>');

        $.ajax({
            type: "GET",
            url: get_url,
            data: {}
        }).done(function (response) {
            $("#add_modal_body").html(response);
        });
    }




    function showRecord(id) {

        $('#show_modal').modal('show');
        $("body").addClass("modal-open");

        var get_url = "{{  url($type.'/:id'. '/show' ) }}";

        get_url = get_url.replace(':id', id);

        $("#show_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....');

        $.ajax({
            type: "GET",
            url: get_url,
            data: {}
        }).done(function (response) {
            $("#show_modal_body").html(response);
        });
    }


    function showKpiObjective(id) {

        $('#show_modal').modal('show');
        $("body").addClass("modal-open");

        var get_url = "{{'kpi_objective/:id'. '/show'}}";

        get_url = get_url.replace(':id', id);

        $("#show_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....');

        $.ajax({
            type: "GET",
            url: get_url,
            data: {}
        }).done(function (response) {
            $("#show_modal_body").html(response);
        });
    }

    function Edit(id) {

        $('#add_modal').modal('show');
        $("body").addClass("modal-open");

        var get_url = "{{  url($type.'/:id'. '/edit' ) }}";

        get_url = get_url.replace(':id', id);

        $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....');

        $.ajax({
            type: "GET",
            url: get_url,
            data: {}
        }).done(function (response) {
            $("#add_modal_body").html(response);
        });
    }


    function Delete(id) {

        $('#delete_modal').modal('show');
        $("body").addClass("modal-open");

        var get_url = "{{  url($type.'/:id'. '/delete' ) }}";

        get_url = get_url.replace(':id', id);

        $("#delete_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....');

        $.ajax({
            type: "GET",
            url: get_url,
            data: {}
        }).done(function (response) {
            $("#delete_modal_body").html(response);
        });
    }


</script>
@stop
