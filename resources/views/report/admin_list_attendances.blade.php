@extends('layouts.print_blue')

@section('header')
    @if(isset($school))
        @foreach($school as $item)
            <h1>{{ $item['title'] }}</h1>
            <p>{{ $item['address'] }}</p>
            <p>{{ $item['email'] }} {{ $item['phone'] }}</p>
        @endforeach
    @endif
@stop
@section('content')
    {!! $data !!}
@stop