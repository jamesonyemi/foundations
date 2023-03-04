@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')


    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">{{ $title }}
                   {{-- <span class="d-block text-muted pt-2 font-size-sm">Sub-datatable examples with local datasource</span></h3>--}}
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                {{--<div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
												<span class="svg-icon svg-icon-md">
													<!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
													<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
														<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
															<rect x="0" y="0" width="24" height="24" />
															<path d="M3,16 L5,16 C5.55228475,16 6,15.5522847 6,15 C6,14.4477153 5.55228475,14 5,14 L3,14 L3,12 L5,12 C5.55228475,12 6,11.5522847 6,11 C6,10.4477153 5.55228475,10 5,10 L3,10 L3,8 L5,8 C5.55228475,8 6,7.55228475 6,7 C6,6.44771525 5.55228475,6 5,6 L3,6 L3,4 C3,3.44771525 3.44771525,3 4,3 L10,3 C10.5522847,3 11,3.44771525 11,4 L11,19 C11,19.5522847 10.5522847,20 10,20 L4,20 C3.44771525,20 3,19.5522847 3,19 L3,16 Z" fill="#000000" opacity="0.3" />
															<path d="M16,3 L19,3 C20.1045695,3 21,3.8954305 21,5 L21,15.2485298 C21,15.7329761 20.8241635,16.200956 20.5051534,16.565539 L17.8762883,19.5699562 C17.6944473,19.7777745 17.378566,19.7988332 17.1707477,19.6169922 C17.1540423,19.602375 17.1383289,19.5866616 17.1237117,19.5699562 L14.4948466,16.565539 C14.1758365,16.200956 14,15.7329761 14,15.2485298 L14,5 C14,3.8954305 14.8954305,3 16,3 Z" fill="#000000" />
														</g>
													</svg>
                                                    <!--end::Svg Icon-->
												</span>Export</button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose an option:</li>
                            <li class="navi-item">
                                <a href="#" class="navi-link">
																<span class="navi-icon">
																	<i class="la la-print"></i>
																</span>
                                    <span class="navi-text">Print</span>
                                </a>
                            </li>
                            <li class="navi-item">
                                <a href="#" class="navi-link">
																<span class="navi-icon">
																	<i class="la la-copy"></i>
																</span>
                                    <span class="navi-text">Copy</span>
                                </a>
                            </li>
                            <li class="navi-item">
                                <a href="#" class="navi-link">
																<span class="navi-icon">
																	<i class="la la-file-excel-o"></i>
																</span>
                                    <span class="navi-text">Excel</span>
                                </a>
                            </li>
                            <li class="navi-item">
                                <a href="#" class="navi-link">
																<span class="navi-icon">
																	<i class="la la-file-text-o"></i>
																</span>
                                    <span class="navi-text">CSV</span>
                                </a>
                            </li>
                            <li class="navi-item">
                                <a href="#" class="navi-link">
																<span class="navi-icon">
																	<i class="la la-file-pdf-o"></i>
																</span>
                                    <span class="navi-text">PDF</span>
                                </a>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>--}}
                <!--end::Dropdown-->
                <!--begin::Button-->
                {{--<a href="#" class="btn btn-primary font-weight-bolder">
											<span class="svg-icon svg-icon-md">
												<!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
												<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
													<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
														<rect x="0" y="0" width="24" height="24" />
														<circle fill="#000000" cx="9" cy="15" r="6" />
														<path d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z" fill="#000000" opacity="0.3" />
													</g>
												</svg>
                                                <!--end::Svg Icon-->
											</span>New Record</a>--}}
                <a href="#" class="btn btn-primary font-weight-bolder"  onclick="showAdd()">
                    <i class="la la-plus" ></i>New Record</a>
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <!--begin: Search Form-->
            <!--begin::Search Form-->
            <div class="mb-7">
                <div class="row align-items-center">
                    <div class="col-lg-9 col-xl-8">
                        <div class="row align-items-center">
                            <div class="col-md-4 my-2 my-md-0">
                                <div class="input-icon">
                                    <input type="text" class="form-control" placeholder="Search..." id="kt_datatable_search_query" />
                                    <span>
																	<i class="flaticon2-search-1 text-muted"></i>
																</span>
                                </div>
                            </div>
                            <div class="col-md-4 my-2 my-md-0">
                                <div class="d-flex align-items-center">
                                    <label class="mr-3 mb-0 d-none d-md-block">Status:</label>
                                    <select class="form-control" id="kt_datatable_search_status">
                                        <option value="">All</option>
                                        <option value="1">Pending</option>
                                        <option value="2">Delivered</option>
                                        <option value="3">Canceled</option>
                                        <option value="4">Success</option>
                                        <option value="5">Info</option>
                                        <option value="6">Danger</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 my-2 my-md-0">
                                <div class="d-flex align-items-center">
                                    <label class="mr-3 mb-0 d-none d-md-block">Type:</label>
                                    <select class="form-control" id="kt_datatable_search_type">
                                        <option value="">All</option>
                                        <option value="1">Online</option>
                                        <option value="2">Retail</option>
                                        <option value="3">Direct</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xl-4 mt-5 mt-lg-0">
                        <a href="#" class="btn btn-light-primary px-6 font-weight-bold">Search</a>
                    </div>
                </div>
            </div>
            <!--end::Search Form-->
            <!--end: Search Form-->
            <!--begin: Datatable-->
            <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
            <!--end: Datatable-->
        </div>
    </div>

@stop

@section('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

@stop




{{-- Scripts --}}
@section('scripts')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

    {{--<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/pages/crud/datatables/advanced/column-rendering.js') }}"></script>--}}
    {{--<script src="{{ asset('assets/js/pages/crud/ktdatatable/child/data-local.js') }}"></script>--}}
    {{--<script src="{{ asset('assets/js/pages/crud/ktdatatable/advanced/modal.js') }}"></script>--}}
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


        function showUpload() {
            $('#upload_modal').modal('show');
            $("body").addClass("modal-open");

            var get_url = "{{ url($type.'/import') }}";

            $("#upload_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....');

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


        function showKra(id) {

            $('#show_modal').modal('show');
            $("body").addClass("modal-open");

            var get_url = "{{'kra/:id'. '/show'}}";

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




        'use strict';
        // Class definition
        var KTDatatableChildDataLocalDemo = function() {
            // Private functions

            var subTableInit = function(e) {
                $('<div/>').attr('id', 'child_data_local_' + e.data.RecordID).appendTo(e.detailCell).KTDatatable({
                    data: {
                        type: 'local',
                        source: e.data.Orders,
                        pageSize: 5,
                    },

                    // layout definition
                    layout: {
                        scroll: true,
                        height: 400,
                        footer: false,
                    },

                    sortable: true,

                    // columns definition
                    columns: [
                       {
                            field: 'ShipCountry',
                            title: 'Title',
                        }, {
                            field: 'ShipAddress',
                            title: 'Owner',
                        }, {
                            field: 'ShipName',
                            title: 'Responsibility',
                        }, {
                            field: 'Timeline',
                            title: 'Time Line',
                        },
                        {
                            field: 'Status',
                            title: 'Status',
                            // callback function support for column rendering
                            template: function(row) {
                                var status = {
                                    0: {'title': 'Waiting Approval', 'class': ' label-light-warning'},
                                    1: {'title': 'Approved', 'class': ' label-light-success'},
                                };
                                return '<span class="label label-lg font-weight-bold' + status[row.Status].class + ' label-inline">' + status[row.Status].title + '</span>';
                            },
                        },
                        {
                            field: 'Actions',
                            width: 130,
                            title: 'Actions',
                            sortable: false,
                            overflow: 'visible',
                            template: function(row) {
                                return '\
	                        <a href="javascript:;"  onclick="showRecord('+row.OrderID+')" class="btn btn-sm btn-clean btn-icon" title="Show Details">\
	                             <i class="fa fa-eye"></i>\
	                        </a>\
	                        <a href="javascript:;" onclick="Edit('+row.OrderID+')" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit Record">\
	                            <i class="fa fa-pencil-ruler"></i>\
	                        </a>\
	                        <a href="javascript:;" onclick="Delete('+row.OrderID+')" class="btn btn-sm btn-clean btn-icon mr-2" title="Delete Record">\
	                            <i class="fa fa-trash"></i>\
	                        </a>\
	                    ';
                            },
                        }
                    ],
                });
            };

            // demo initializer
            var mainTableInit = function() {

                var dataJSONArray = JSON.parse( '{!! \App\Helpers\GeneralHelper::kras() !!}');

                var datatable = $('#kt_datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: dataJSONArray,
                        pageSize: 10, // display 20 records per page
                    },

                    // layout definition
                    layout: {
                        scroll: false,
                        height: null,
                        footer: false,
                    },

                    sortable: true,

                    filterable: false,

                    pagination: true,

                    detail: {
                        title: 'Load sub table',
                        content: subTableInit,
                    },

                    search: {
                        input: $('#kt_datatable_search_query'),
                        key: 'generalSearch'
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'RecordID',
                            title: '',
                            sortable: false,
                            width: 30,
                            textAlign: 'center',
                        },
                        {
                            field: 'Department',
                            title: 'Department',
                        },
                        {
                            field: 'Title',
                            title: 'Title',
                        },
                        {
                            field: 'Kra',
                            title: 'Number of Kpis',
                        },
                        {
                            field: 'Status',
                            title: 'Status',
                            // callback function support for column rendering
                            template: function(row) {
                                var status = {
                                    0: {'title': 'Waiting Approval', 'class': ' label-light-warning'},
                                    1: {'title': 'Approved', 'class': ' label-light-success'},
                                };
                                return '<span class="label label-lg font-weight-bold' + status[row.Status].class + ' label-inline">' + status[row.Status].title + '</span>';
                            },
                        },
                        {
                            field: 'Actions',
                            width: 130,
                            title: 'Actions',
                            sortable: false,
                            overflow: 'visible',
                            template: function(row) {
                                return '\
	                       <a href="javascript:;"  onclick="showKra('+row.RecordID+')" class="btn btn-sm btn-clean btn-icon" title="Show Details">\
	                             <i class="fa fa-eye"></i>\
	                        </a>\
	                    ';
                            },
                        }],
                });

                $('#kt_datatable_search_status').on('change', function() {
                    datatable.search($(this).val().toLowerCase(), 'Status');
                });

                $('#kt_datatable_search_type').on('change', function() {
                    datatable.search($(this).val().toLowerCase(), 'Type');
                });

                $('#kt_datatable_search_status, #kt_datatable_search_type').selectpicker();
            };

            return {
                // Public functions
                init: function() {
                    // init dmeo
                    mainTableInit();
                },
            };
        }();

        jQuery(document).ready(function() {
            KTDatatableChildDataLocalDemo.init();
        });

    </script>
@stop
