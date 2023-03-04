@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class=" clearfix">
        <div class="pull-right">
            <a href="{{ url($type.'/create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus-circle"></i> {{ trans('table.new') }}</a>
        </div>
    </div>
    <table id="data" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>{{trans('table.title') }}</th>
            <th>{{trans('table.actions') }}</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        var startPosition;
        var endPosition;
        $("#data tbody").sortable({
            cursor : "move",
            start : function(event, ui) {
                startPosition = ui.item.prevAll().length + 1;
            },
            update : function(event, ui) {
                endPosition = ui.item.prevAll().length + 1;
                var lists = "";
                $('#data #row').each(function(i) {
                    lists = lists + ',' + $(this).val();
                });
                $.getJSON("{{ URL::to('pages/reorderpage') }}", {
                    list : lists
                });
            }
        });
    </script>
@stop