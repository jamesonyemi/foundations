@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <table class="table table-hover">
            <thead>
                <th>{{trans('report.title')}}</th>
                <th>{{trans('report.date_start')}}</th>
                <th>{{trans('report.date_end')}}</th>
                <th>{{trans('report.subject')}}</th>
                <th></th>
            </thead>
            <tbody>
            @foreach($onlineExamList as $item)
                <tr>
                    <td>{{$item['title']}}</td>
                    <td>{{$item['date_start']}}</td>
                    <td>{{$item['date_end']}}</td>
                    <td>{{$item['subject']}}</td>
                    <td><a class="btn btn-sm btn-success" href="{{url('online_exam/'.$item['id'].'/start')}}">{{trans('report.start_exam')}} <i class="fa fa-play"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop