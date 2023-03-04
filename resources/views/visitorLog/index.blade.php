@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{--{{$fees->count()}}--}}
{{-- Content --}}
@section('content')
    <div class="card card-custom gutter-b">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">{{ $title }}
                    {{--<span class="d-block text-muted pt-2 font-size-sm">row selection and group actions</span>--}}
                </h3>
            </div>
            <div class="card-toolbar">

                <!--begin::Button-->

                    <a href="#" class="btn btn-primary font-weight-bolder"  onclick="showAdd()">
                        <i class="la la-plus" ></i>New Visitor Record
                    </a>


            <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <!--begin: Datatable-->
            <table class="table display nowrap table-separate table-head-custom table-checkable" id="kt_datatable">
                <thead>
                <tr>
                    <th><strong>ID</strong></th>
                    <th><strong>Name</strong></th>
                    <th><strong>Purpose</strong></th>
                    <th><strong>Action</strong></th>
                </tr>
                </thead>
                <tbody>


                @foreach($visitorLogs as $key)

                        <tr>
                            <td>{{ $key->id }}</td>
                            <td>{{ $key->name }}</td>
                            <td>{{ $key->purpose }}</td>
                            <td>


                                <a href="javascript:;"  onclick="showRecord({{ $key->id }})" class="btn btn-sm btn-clean btn-icon" title="Show Details">
                                    <i class="fa fa-eye text-primary mr-5"></i>
                                </a>
                                <a href="javascript:;" onclick="Edit({{ $key->id }})" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit Record">
                                    <i class="fa fa-pencil-ruler text-warning mr-5"></i>
                                </a>
                                {{-- <a href="javascript:;" onclick="Delete({{ $key->id }})" class="btn btn-sm btn-clean btn-icon mr-2" title="Delete Record">
                                     <i class="fa fa-trash text-danger mr-5"></i>
                                 </a>--}}


                            </td>
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

            $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

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

            $("#show_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

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

            $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

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

            $("#delete_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

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
