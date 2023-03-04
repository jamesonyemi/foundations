@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class=" clearfix">
            </div>
            {!! Form::open(array('url' => $type . '/' . $option->id, 'method' => 'delete', 'class' => 'bf')) !!}

            @include($type.'/_details')

            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('scripts')
    <script>
    </script>
@endsection