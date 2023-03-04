@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            {!! Form::open(array('url' => url($type) . '/' . $page->id, 'method' => 'delete', 'class' => 'bf')) !!}

            @include($type.'/_details')

            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('scripts')
    <script>
    </script>
@endsection