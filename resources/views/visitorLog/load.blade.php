<table class="table display nowrap table-separate table-head-custom table-checkable" id="kt_datatable">
    <thead>
    <tr>
        <th><strong>ID</strong></th>
        <th><strong>Name</strong></th>
        <th><strong>Purpose</strong></th>
        <th><strong>Employee Visited</strong></th>
        <th><strong></strong></th>
        {{-- <th><strong>Completed Activities</strong></th>
         <th><strong>Total Score</strong></th>--}}
        <th><strong>Action</strong></th>
    </tr>
    </thead>
    <tbody>


    @foreach($visitorLogs as $key)

        <tr>
            <td>{{ $key->id }}</td>
            <td>{{ $key->name }}</td>
            <td>{{ @$key->purpose}}</td>
            <td>{{ @$key->employee->user->full_name ?? ''}}</td>
            <td></td>
            {{--  <td>{{ $key->completed_kpi_activities->count()}}    ({{\App\Helpers\GeneralHelper::getPercentage($key->completed_kpi_activities->count(), $key->kpi_activities->count())}})</td>
             <td>{{ $key->kpi_score}}</td>--}}
            <td>


                <a href="javascript:;"  onclick="showVisitorRecord({{ $key->id }})" class="btn btn-sm btn-clean btn-icon" title="Show Details">
                    <i class="fa fa-eye text-primary mr-5"></i>
                </a>
                {{--<a href="javascript:;" onclick="Edit({{ $key->id }})" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit Record">
                    <i class="fa fa-pencil-ruler text-warning mr-5"></i>
                </a>--}}
                {{-- <a href="javascript:;" onclick="Delete({{ $key->id }})" class="btn btn-sm btn-clean btn-icon mr-2" title="Delete Record">
                     <i class="fa fa-trash text-danger mr-5"></i>
                 </a>--}}


            </td>
        </tr>

    @endforeach

    </tbody>
</table>


    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label">Weekly Visitor Log Chart</h3>
        </div>
    </div>

    <div id="kt_amcharts_2" style="height: 300px;"></div>




<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />


<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('assets/js/pages/crud/datatables/advanced/column-rendering.js') }}"></script>
<script src="{{ asset('js/amchart/amcharts.js') }}"></script>
<script src="{{ asset('js/amchart/light.js') }}"></script>
<script src="{{ asset('js/amchart/serial.js') }}"></script>
<script src="{{ asset('js/amchart/pie.js') }}"></script>
<script src="{{ asset('js/amchart/radar.js') }}"></script>

<script>

    var KTamChartsChartsDemo = function() {

        // Private functions
        var departmentBarChart = function() {
            var chart = AmCharts.makeChart("kt_amcharts_2", {
                "rtl": KTUtil.isRTL(),
                "type": "serial",
                "theme": "light",
                "dataProvider": {!! \App\Helpers\GeneralHelper::weekly_visitor_armchar_bar1(\Carbon\Carbon::create($request->ddate)) !!},
                "valueAxes": [{
                    "gridColor": "#FFFFFF",
                    "gridAlpha": 0.2,
                    "position": "left",
                    "title": "Visitors",
                    "dashLength": 0
                }],

                "gridAboveGraphs": true,
                "startDuration": 1,
                "graphs": [{
                    "balloonText": "[[category]]: <b>[[value]]</b>",
                    "fillColorsField": "color",
                    "fillAlphas": 0.8,
                    "lineAlpha": 0.2,
                    "type": "column",
                    "valueField": "visits"
                }],
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": true
                },
                "categoryField": "country",
                "depth3D": 60,
                "angle": 30,
                "categoryAxis": {
                    "gridPosition": "start",
                    "gridAlpha": 0,
                    "tickPosition": "start",
                    "labelRotation": 45,
                    "tickLength": 20
                },
                "export": {
                    "enabled": true
                }

            });
        }


        return {
            // public functions
            init: function() {
                departmentBarChart();
            }
        };
    }();
    jQuery(document).ready(function() {
        KTamChartsChartsDemo.init();
    });



    function showVisitorRecord(id) {

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


    function getAttendanceDetails(id) {

        $('#show_modal').modal('show');
        $("body").addClass("modal-open");

        var get_url = "{{  url($type.'/:id'.'/show' ) }}";

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

</script>
