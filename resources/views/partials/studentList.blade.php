
<table class="table display nowrap table-striped table-bordered file-export table-hover scroll-horizontal-vertical">
    <thead class="bg-success white">
    <tr>
        <th>{{ trans('table.id') }}</th>
        <th>{{ trans('student.matid') }}</th>
        <th>{{ trans('student.full_name') }}</th>
        <th>{{ trans('student.section') }}</th>
        <th>{{ trans('student.programme') }}</th>
        <th>{{ trans('table.actions') }}</th>
    </tr>
    </thead>
    <tbody>


    @foreach($students as $student)

        <tr>
            <td>{{isset($student->user) ? $student->id : ""}}</td>
            <td>{{isset($student->user) ? $student->sID : ""}}</td>
            <td>{{isset($student->user) ? $student->user->full_name : ""}}</td>
            <td>{{isset($student->section) ? @$student->user->mobile : ""}}</td>
            <td>{{isset($student->programme) ? $student->programme->title : ""}}</td>

            @if($student->discount == 1)
            <td>
                <a href="{{ url('/student/' . $student->id . '/show' ) }}" class="btn btn-primary btn-sm" >
                    <i class="fa fa-eye"></i> {{ trans("table.details") }}</a>
                <strong>D </strong>
            </td>
             @else
            <td>
                    <a href="{{ url('/student/' . $student->id . '/show' ) }}" class="btn btn-primary btn-sm" >
                        <i class="fa fa-eye"></i> {{ trans("table.details") }}</a>
            </td>
            @endif
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr class="bg-warning white">
        <th>{{ trans('table.id') }}</th>
        <th>{{ trans('student.matid') }}</th>
        <th>{{ trans('student.full_name') }}</th>
        <th>{{ trans('student.mobile') }}</th>
        <th>{{ trans('student.programme') }}</th>
        <th>{{ trans('table.actions') }}</th>
    </tr>
    </tfoot>
</table>


