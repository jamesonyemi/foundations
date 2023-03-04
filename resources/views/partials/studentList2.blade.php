<div class="card card-custom">
    <div class="card-header flex-wrap py-5">
        <div class="card-title">
            <h3 class="card-label">{{ $title }}
                <div class="text-muted pt-2 font-size-sm">multiple controls examples</div></h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Dropdown-->
            <div class="dropdown dropdown-inline mr-2">
                <a href="#" class="btn btn-warning font-weight-bolder" onclick="showUpload()">
                    <i class="la la-upload" ></i>Upload Employees</a>
                <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="la la-download"></i>Export</button>
                <!--begin::Dropdown Menu-->
                <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                    <ul class="nav flex-column nav-hover">
                        <li class="nav-header font-weight-bolder text-uppercase text-primary pb-2">Choose an option:</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon la la-print"></i>
                                <span class="nav-text">Print</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon la la-copy"></i>
                                <span class="nav-text">Copy</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon la la-file-excel-o"></i>
                                <span class="nav-text">Excel</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon la la-file-text-o"></i>
                                <span class="nav-text">CSV</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon la la-file-pdf-o"></i>
                                <span class="nav-text">PDF</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!--end::Dropdown Menu-->
            </div>
            <!--end::Dropdown-->
            <!--begin::Button-->
            <a href="#" class="btn btn-primary font-weight-bolder"  onclick="showAdd()">
                <i class="la la-plus" ></i>New Record</a>

            {{--<a href="{{ url($type.'/create') }}" class="btn btn-primary font-weight-bolder" >
                <i class="la la-plus" ></i>New Employee</a>--}}
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table display nowrap table-separate table-head-custom table-checkable" id="kt_datatable">
            <thead>
            <tr>
                <th>{{ trans('table.id') }}</th>
                <th>{{ trans('student.matid') }}</th>
                <th>{{ trans('student.full_name') }}</th>
                <th>{{ 'Department' }}</th>
                <th>{{ trans('table.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employees as $key)

                <tr>
                    <td>{{@$key->id}}</td>
                    <td>{{@$key->sID}}</td>
                    <td>{{@$key->user->first_name }}</td>
                    <td>{{isset($key->user) ? @$key->section->title : ""}}</td>
                    <td>
                        <button onclick="showRecord({{@$key->id}})" class="btn btn-primary btn-sm" title="View records">
                            <i class="fa fa-eye"></i> Details
                        </button>

                        <button onclick="Edit({{@$key->id}})" class="btn btn-success btn-sm" title="View records">
                            <i class="fa fa-pencil-ruler"></i> Edit
                        </button>

                        <button onclick="Delete({{@$key->id}})" class="btn btn-danger btn-sm" title="View records">
                            <i class="fa fa-trash"></i> Delete
                        </button>

                        {{--<a href="{{ url('/employee/' . $key->id . '/show' ) }}" class="btn btn-primary btn-sm" onclick="showDiv()">
                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>

                        <a href="{{ url('/employee/' . $key->id . '/edit' ) }}" class="btn btn-success btn-sm" onclick="showDiv()">
                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>

                        <a href="{{ url('/employee/' . $key->id . '/delete' ) }}" class="btn btn-danger btn-sm" onclick="showDiv()">
                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>--}}

                    </td>

                </tr>
            @endforeach

            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>



@section('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

@stop

@section('scripts')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/pages/crud/datatables/advanced/column-rendering.js') }}"></script>
    {{--<script src="{{ asset('assets/js/pages/crud/ktdatatable/advanced/modal.js') }}"></script>--}}
    <script >


        function showAdd() {
            $('#add_modal').modal('show');
            $("body").addClass("modal-open");

            var get_url = "{{ url($type.'/create') }}";

            $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.png') !!}</div>');

            $.ajax({
                type: "GET",
                url: get_url,
                data: {}
            }).done(function (response) {
                $("#add_modal_body").html(response);
            });
        }


        function showUpload() {
            $('#upload_modal').modal('show');
            $("body").addClass("modal-open");

            var get_url = "{{ url($type.'/import') }}";

            $("#upload_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.png') !!}</div>');

            $.ajax({
                type: "GET",
                url: get_url,
                data: {}
            }).done(function (response) {
                $("#upload_modal_body").html(response);
            });
        }



        function showRecord(id) {

            $('#show_modal').modal('show');
            $("body").addClass("modal-open");

            var get_url = "{{ url('/employee/' .':id'. '/show' ) }}";

            get_url = get_url.replace(':id', id);

            $("#show_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.png') !!}</div>');

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

            var get_url = "{{ url('/employee/' .':id'. '/edit' ) }}";

            get_url = get_url.replace(':id', id);

            $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.png') !!}</div>');

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

            var get_url = "{{ url('/employee/' .':id'. '/delete' ) }}";

            get_url = get_url.replace(':id', id);

            $("#delete_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.png') !!}</div>');

            $.ajax({
                type: "GET",
                url: get_url,
                data: {}
            }).done(function (response) {
                $("#delete_modal_body").html(response);
            });
        }




        function del(id,category)
        {

            $('#deleteModal').modal('show');
            $("#deleteModal").find('#info').html('{!!  __('messages.faqCategoryDeleteConfirm') !!} <strong>'+category+'</strong>?<br>' +
                '<br><div class="note note-warning">' +
                '{!! __('messages.deleteNotefaqCategory')!!}'+
                '</div>');

            $('#deleteModal').find("#delete").off().click(function()
            {
                var url = "{{ route('admin.faq_categories.destroy',':id') }}";
                url = url.replace(':id',id);
                $.ajax({

                    type: "DELETE",
                    url : url,
                    dataType: 'json',
                    data: {"id":id}

                }).done(function(response)
                {
                    if(response.success == "deleted")
                    {
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                        $('#deleteModal').modal('hide');

                        var msg = prepareMessage("{!! trans("messages.faqCategoryDeleteMessage") !!}", ":category", category);
                        showToastrMessage(msg, '{{__('core.success')}}', 'success');
                        table1._fnDraw();
                    }
                });
            })

        }


    </script>
@stop
