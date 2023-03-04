<table class="table display nowrap table-striped table-bordered file-export table-hover scroll-horizontal-vertical">
    <thead class="bg-success white">
    <tr>
        <th>{{ trans('table.id') }}</th>
        <th>{{ trans('student.matid') }}</th>
        <th>{{ trans('student.full_name') }}</th>
        <th>{{ trans('student.programme') }}</th>
        <th>{{ trans('student.balance') }}</th>
        <th>{{ trans('table.actions') }}</th>
    </tr>
    </thead>
    <tbody>


    @foreach($general_ledger as $ledger)

        <tr>
            <td>{{isset($ledger->student) ? $ledger->student->id : ""}}</td>
            <td>{{isset($ledger->student) ? $ledger->student->sID : ""}}</td>
            <td>{{isset($ledger->student->user) ? $ledger->student->user->full_name : ""}}</td>
            <td>{{isset($ledger->student->programme) ? $ledger->student->programme->title : ""}}</td>
            <td>{{isset($ledger->student) ? \App\Helpers\GeneralHelper::amount($ledger->student->financial->sum('credit') - $ledger->student->financial->sum('debit') ): ""}}</td>


            <td>

                <a href="{{ url('/student/' . $ledger->student_id . '/show' ) }}" class="btn btn-primary btn-sm" >
                    <i class="fa fa-eye"></i> {{ trans("table.details") }}</a>
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr class="bg-warning white">
        <th>{{ trans('table.id') }}</th>
        <th>{{ trans('student.matid') }}</th>
        <th>{{ trans('student.full_name') }}</th>
        <th>{{ trans('student.programme') }}</th>
        <th>{{ trans('student.balance') }}</th>
        <th>{{ trans('table.actions') }}</th>
    </tr>
    </tfoot>
</table>
