@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
	{{ $title }}
@stop

{{-- Content --}}
@section('content')
	<div class="col-sm-3">
		<ul class="list-group subject-list">
		</ul>
	</div>
    <div class="col-sm-9">
        <ul class="list-group list-group-horizontal semester-list">
        </ul>
        <table class="table table-bordered no-padding marks hidden">
            <thead>
                <th width="33%">{{trans('report.date')}}</th>
                <th width="33%">{{trans('report.mark_type')}}</th>
                <th width="33%">{{trans('report.mark_value')}}</th>
            </thead>
            <tbody>

            </tbody>
        </table>
        <table class="table table-bordered no-padding attendances hidden">
            <thead>
            <th width="33%">{{trans('report.date')}}</th>
            <th width="33%">{{trans('report.hour')}}</th>
            <th width="33%">{{trans('report.attendance_type')}}</th>
            </thead>
            <tbody>

            </tbody>
        </table>
        <table class="table table-bordered no-padding notices hidden">
            <thead>
            <th width="33%">{{trans('report.date')}}</th>
            <th width="33%">{{trans('report.title')}}</th>
            <th width="33%">{{trans('report.text')}}</th>
            </thead>
            <tbody>

            </tbody>
        </table>
        <table class="table table-bordered no-padding subjects hidden">
            <thead>
            <th width="40%">{{trans('report.book')}}</th>
            <th width="40%">{{trans('report.author')}}</th>
            <th width="10%">{{trans('report.quantity')}}</th>
            <th width="10%">{{trans('report.issued')}}</th>
            </thead>
            <tbody>

            </tbody>
        </table>
        <table class="table table-bordered no-padding exams hidden">
            <thead>
            <th width="20%">{{trans('report.exam')}}</th>
            <th width="20%">{{trans('report.title')}}</th>
            <th width="20%">{{trans('report.date')}}</th>
            <th width="40%">{{trans('report.description')}}</th>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
@stop
@section('scripts')

	<script>
		$(document).ready(function () {
			$.ajax({
				type: "POST",
				url: '{{ url('/report/'.$student_user->id.'/studentsubjects') }}',
                data: {_token: '{{ csrf_token() }}'},
				success: function (response) {
                    var i = 0;
                    $.each(response, function (key, value) {
                        $('.subject-list').append('<li class="list-group-item" id="subject" data-id="'+key+'"><span>' + value +'</span></li>');
                    });
                    $('li#subject').click(function()
                    {
                        $(this).parent().children().removeClass('active');
                        $(this).addClass('active');
                        {{$method}}($(this),$('li#semester.active'));
                    })
                }
			});
            @if($method!="getNotices" && $method!="getSubjectBook")
            $.ajax({
                    type: "POST",
                    url: '{{ url('/report/'.$student_user->id.'/semesters') }}',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function (response) {
                        var i = 0;
                        $.each(response, function (key, value) {
                            $('.semester-list').append('<li class="list-group-item'+ ((i==0)?' active':'')+'" id="semester" data-id="'+key+'"><span>' + value +'</span></li>');
                            i++;
                        });
                        $('li#semester').click(function()
                        {
                            $(this).parent().children().removeClass('active');
                            $(this).addClass('active');
                            {{$method}}($('li#subject.active'),$(this));
                        })
                    }
                });
            @endif
            function getMarks(subject,semester)
            {
                $('.marks tbody').empty();
                $('.marks').removeClass('hidden');
                var subject_id = subject.data('id');
                var semester_id = semester.data('id');
                $.ajax({
                    type: "GET",
                    url: '{{ url('/report/'.$student_user->id.'/marksforsubject') }}',
                    data: {_token: '{{ csrf_token() }}', 'subject_id':subject_id, 'semester_id':semester_id },
                    success: function (response) {
                        $.each(response, function (key,value) {
                            $('.marks tbody').append('<tr><td>' + value.date +'</td><td>' + value.mark_type +'</td><td>' + value.mark_value +'</td></tr>');
                        });
                    }
                });
            }
            function getAttendances(subject,semester)
            {
                $('.attendances tbody').empty();
                $('.attendances').removeClass('hidden');
                var subject_id = subject.data('id');
                var semester_id = semester.data('id');
                $.ajax({
                    type: "GET",
                    url: '{{ url('/report/'.$student_user->id.'/attendancesforsubject') }}',
                    data: {_token: '{{ csrf_token() }}', 'subject_id':subject_id, 'semester_id':semester_id },
                    success: function (response) {
                        $.each(response, function (key,value) {
                            $('.attendances tbody').append('<tr><td>' + value.date +'</td><td>' + value.hour +'</td><td>' + value.attendance_type +'</td></tr>');
                        });
                    }
                });
            }
            function getNotices(subject, semester) {
                semester = semester || 1;
                $('.notices tbody').empty();
                $('.notices').removeClass('hidden');
                var semester_id = subject.data('id');
                $.ajax({
                    type: "GET",
                    url: '{{ url('/report/'.$student_user->id.'/noticeforsubject') }}',
                    data: {_token: '{{ csrf_token() }}', 'subject_id':semester_id },
                    success: function (response) {
                        $.each(response, function (key,value) {
                            $('.notices tbody').append('<tr><td>' + value.date +'</td><td>' + value.title +'</td><td>' + value.description +'</td></tr>');
                        });
                    }
                });
            }
            function getSubjectBook(subject)
            {
                $('.subjects tbody').empty();
                $('.subjects').removeClass('hidden');
                var subject_id = subject.data('id');
                $.ajax({
                    type: "GET",
                    url: '{{ url('/report/'.$student_user->id.'/getSubjectBook') }}',
                    data: {_token: '{{ csrf_token() }}', 'subject_id':subject_id },
                    success: function (response) {
                        $.each(response, function (key,value) {
                            $('.subjects tbody').append('<tr><td>' + value.title +'</td><td>' + value.author +'</td><td>' + value.quantity +'</td><td>' + value.issued +'</td></tr>');
                        });
                    }
                });
            }
            function getSubjectExams(subject)
            {
                $('.exams tbody').empty();
                $('.exams').removeClass('hidden');
                var subject_id = subject.data('id');
                $.ajax({
                    type: "GET",
                    url: '{{ url('/report/'.$student_user->id.'/examforsubject') }}',
                    data: {_token: '{{ csrf_token() }}', 'subject_id':subject_id },
                    success: function (response) {
                        $.each(response, function (key,value) {
                            $('.exams tbody').append('<tr><td>' + value.title +'</td><td>' + value.subject +'</td><td>' + value.date +'</td><td>' + value.description +'</td></tr>');
                        });
                    }
                });
            }
		});
	</script>
@stop