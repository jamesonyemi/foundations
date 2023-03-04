@extends('layouts.secure')
@section('content')
    <h1>{{trans('dashboard.calendar')}}</h1>
    <div id="calendar"></div>
@stop
@section('scripts')
    <script>
    $(document).ready(function() {
        $('#calendar').fullCalendar({
            "header": {
                "left": "prev,next today",
                "center": "title",
                "right": "month,agendaWeek,agendaDay"
            },
            "eventLimit": true,
            "firstDay": 1,
            "eventRender": function (event, element) {
                element.popover({
                    content: event.description,
                    template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                    title: event.title,
                    container: 'body',
                    trigger: 'click',
                    placement: 'auto'
                });
            },
            "eventSources": [
                {
                    url:"{{url('events')}}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    error: function() {
                        alert('there was an error while fetching events!');
                    }
                }
            ]
        });
    });
    </script>
    @stop
