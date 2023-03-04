<?php
$topStudentsDebtors = \App\Models\Employee::select("*",
    \Illuminate\Support\Facades\DB::raw('(SELECT (SUM(credit)- SUM(debit)) FROM general_ledger WHERE general_ledger.student_id = students.id) as balance'))
    ->orderBy('balance', 'DESC')
    ->take(10)
    ->get();
?>

<table class="table display nowrap table-striped table-bordered table-hover scroll-horizontal-vertical">
    <thead class="bg-danger white">
    <tr>
        <th>{{ trans('student.balance') }}</th>
        <th>{{ trans('student.full_name') }}</th>
        <th>{{ trans('student.matid') }}</th>
        <th>{{ trans('table.actions') }}</th>
    </tr>
    </thead>
    <tbody>


    @foreach($topStudentsDebtors as $ledger)

        <tr>
            <td>{{$ledger->balance}}</td>
            <td>{{isset($ledger->user) ? $ledger->user->full_name : ""}}</td>
            <td>{{$ledger->sID}}</td>
            <td>

                <a href="{{ url('/student/' . $ledger->id . '/show' ) }}" class="btn btn-primary btn-sm" >
                    <i class="fa fa-eye"></i></a>
            </td>
        </tr>
    @endforeach
    </tbody>

</table>
