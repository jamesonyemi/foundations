@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class=" clearfix">
        <label class="radio-inline">
            <input type='radio' id='category' name='category' checked value='__' class='icheck'/> All
        </label>
        @foreach($categories as $key => $value)
            <label class="radio-inline">
                <input type='radio' id='category' name='category' value='{{$key}}' class='icheck'/> {{$value}}
            </label>
        @endforeach
        <br/>
        <div class="pull-right">
            <a href="{{ url($type.'/create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus-circle"></i> {{ trans('table.new') }}</a>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <h4 class="panel-title">
                <i class="material-icons">dashboard</i>
                {{ $title }}
            </h4>
            <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
        </div>
        <div class="card-body">

            <table id="data" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>{{ trans('option.category') }}</th>
                    <th>{{ trans('option.title') }}</th>
                    <th>{{ trans('option.value') }}</th>
                    <th>{{ trans('table.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')
    <script>
        $(document).ready(function () {
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
        });
    </script>
    <script>
        $('input[type=radio]').on('ifChecked', function (event) {
            $('#data').DataTable().ajax.url('{!! url($type.'/data') !!}/' + $(this).val());
            $('#data').DataTable().ajax.reload(null, false);

        });
    </script>
@stop
