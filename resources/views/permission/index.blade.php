
    <div class="card card-custom">
        <div class="card-header flex-wrap py-5">
            <div class="card-title">
                <h3 class="card-label">Multiple Controls
                    <div class="text-muted pt-2 font-size-sm">multiple controls examples</div></h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <!--end::Dropdown-->
                <!--begin::Button-->
                <a href="#" class="btn btn-primary font-weight-bolder"  onclick="showAdd()">
                    <i class="la la-plus" ></i>New Record</a>
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <!--begin: Datatable-->
            <table class="table display nowrap table-separate table-head-custom table-checkable" id="kt_datatable">
                <thead>
                <tr>
                    <th>{{ trans('role.name') }}</th>
                    <th>{{ trans('role.slug') }}</th>
                    <th>{{ trans('table.actions') }}</th>
                </tr>
                </thead>
                <tbody>


                @foreach($data as $key)

                    <tr>
                        <td>{{ $key->name }}</td>
                        <td>{{ $key->slug}}</td>
                        <td>


                            <a href="javascript:;"  onclick="showRecord({{ $key->id }})" class="btn btn-sm btn-clean btn-icon" title="Show Details">
                                <i class="fa fa-eye text-primary mr-5"></i>
                            </a>
                            <a href="javascript:;" onclick="Edit({{ $key->id }})" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit Record">
                                <i class="fa fa-pencil-ruler text-warning mr-5"></i>
                            </a>
                            <a href="javascript:;" onclick="Delete({{ $key->id }})" class="btn btn-sm btn-clean btn-icon mr-2" title="Delete Record">
                                <i class="fa fa-trash text-danger mr-5"></i>
                            </a>


                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>





    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/pages/crud/datatables/advanced/column-rendering.js') }}"></script>
    <script src="{{ asset('assets/js/pages/crud/ktdatatable/advanced/modal.js') }}"></script>


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

    </script>
