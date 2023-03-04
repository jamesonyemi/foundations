@extends('layouts.print_blue')

@section('header')
    <h1>{{trans('section.registration_numbers')}}</h1>
    <p>&nbsp;</p>
@stop
@section('content')
    {!!  $content !!}
@stop