<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="content-type" content="text-html; charset=utf-8">
    <title>SMS invoice</title>
</head>
<body>
<div id="page-wrap">
    <table width="100%">
        <tr>
            <td width="5%">
                <h2>
                    <img src="{{ url('uploads/site').'/duc_logo.png'}}"/>
                </h2>
            </td>
            <td width="75%">
                <h3>{{Settings::get('name') }}</h3>
            </td>
            <td width="20%">
                <h5>{{--{{ trans('invoice.create_date') . " : " . $invoice->created_at->format(Settings::get('date_format'))}}--}}</h5>
            </td>
        </tr>
    </table>
    <br/><br/>
    {{--<table width="100%">
        <tr>
            <td width="33%">
                <table>
                    <tbody>
                    <tr>
                        <th>{{trans('invoice.invoice_from')}}</th>
                    </tr>
                    <tr>
                        <td>{{ !is_null($invoice->school->title)?$invoice->school->title:"" }}</td>
                    </tr>
                    <tr>
                        <td>{{ !is_null($invoice->school->address)?$invoice->school->address:"" }}</td>
                    </tr>
                    <tr>
                        <td>{{trans('invoice.school_phone') . " : " . (!is_null($invoice->school->phone)?$invoice->school->phone:"") }}</td>
                    </tr>
                    <tr>
                        <td>{{trans('invoice.school_email') . " : " . (!is_null($invoice->school->email)?$invoice->school->email:"") }}</td>
                    </tr>
                    </tbody>
                </table>

            </td>
            <td width="33%">
                <table>
                    <tbody>
                    <tr>
                        <th>{{trans('invoice.invoice_to')}}</th>
                    </tr>
                    <tr>
                        <td>{{ !is_null($invoice->user)?$invoice->user->full_name:"" }}</td>
                    </tr>
                    <tr>
                        <td>{{ !is_null($invoice->user)?$invoice->user->address:"" }}</td>
                    </tr>
                    <tr>
                        <td>{{ !is_null($invoice->user)?$invoice->user->phone:"" }}</td>
                    </tr>
                    <tr>
                        <td>{{ !is_null($invoice->user)?$invoice->user->email:"" }}</td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td width="33%">
                <table>
                    <tbody>
                    <tr>
                        <td>{{ trans('invoice.invoice_no') . " : " . $invoice->id }}</td>
                    </tr>
                    <tr>
                        <td>
                            {{ trans('invoice.status') . " : " . (($invoice->paid==0)?trans('invoice.not_payed'):trans('invoice.payed')) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>--}}
    <br/><br/>
    <table class="table table-striped">
        <thead>
        <tr>
            <th width="10%" style="text-align: right">{{trans('invoice.item_no')}}</th>
            <th width="45%" style="text-align: right">{{trans('invoice.description')}}</th>
            <th width="20%" style="text-align: right">{{trans('invoice.quantity')}}</th>
            <th width="25%" style="text-align: right">{{trans('invoice.subtotal')}}</th>
        </tr>
        </thead>
        <tbody>
        {{--@if (isset($invoice) && !is_null($invoice->items))
             @foreach($invoice->items as $key=>$item)
                @if (isset($item->option))
                    <tr>
                        <td style="text-align: right">{{ $key+1}}</td>
                        <td style="text-align: right">{{ $item->item_name_price}}</td>
                        <td style="text-align: right">{{ $item->quantity }}</td>
                        <td style="text-align: right">{{ $item->item_sub_price }}</td>
                    </tr>
                @endif
            @endforeach
        @endif--}}
        </tbody>
    </table>
    <table class="table" width="100%">
        <tr>
            <td width="55%">
            </td>
            <td width="45%">
                {{--<table class="table">
                    <tr>
                        <th width="55%">{{trans('invoice.total')}}</th>
                        <td width="55%">
                            <b>{{Settings::get('currency')}} {{$invoice->amount}}</b>
                        </td>
                    </tr>
                </table>--}}
            </td>
        </tr>
    </table>
</div>
</body>
</html>
