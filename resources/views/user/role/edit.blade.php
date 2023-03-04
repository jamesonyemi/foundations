@extends('layouts.secure')
@section('title')
    {{ trans('general.edit') }} {{ trans_choice('general.role',1) }}
@endsection
@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('general.edit') }} {{ trans_choice('general.role',1) }}</h3>

        </div>
        <form action="{{url('user/role/'.$role->id.'/update')}}" method="post" class="form">
            {{csrf_field()}}
            <div class="box-body">


                <div class="form-group">
                    <label for="name">{{trans_choice('general.name',1)}}</label>
                    <input type="text" name="name" value="{{$role->name}}" class="form-control" required id="name">
                </div>
                <div class="form-group">
                    <label for="time_limit">{{trans_choice('general.time_limit',1)}}?</label>
                    <select name="time_limit" id="time_limit" class="form-control">
                        <option value="0"
                                @if($role->time_limit==0) selected @endif >{{trans_choice('general.no',1)}}</option>
                        <option value="1"
                                @if($role->time_limit==1) selected @endif>{{trans_choice('general.yes',1)}}</option>
                    </select>
                </div>
                <div id="time_div">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="from_time">{{trans_choice('general.from_time',1)}}</label>
                                <input type="text" name="from_time" value="{{$role->from_time}}"
                                       class="form-control time-picker" required
                                       id="from_time">
                            </div>
                            <div class="col-md-4">
                                <label for="to_time">{{trans_choice('general.to_time',1)}}</label>
                                <input type="text" name="to_time" value="{{$role->to_time}}"
                                       class="form-control time-picker" required
                                       id="to_time">
                            </div>
                            <div class="col-md-4">
                                <label for="access_days">{{trans_choice('general.day',2)}}</label>
                                <select name="access_days[]" id="access_days" class="form-control select2" multiple>
                                    <option value="monday"
                                            @if(in_array("monday",json_decode($role->access_days))) selected @endif>{{trans_choice('general.monday',1)}}</option>
                                    <option value="tuesday"
                                            @if(in_array("tuesday",json_decode($role->access_days))) selected @endif >{{trans_choice('general.tuesday',1)}}</option>
                                    <option value="wednesday"
                                            @if(in_array("wednesday",json_decode($role->access_days))) selected @endif>{{trans_choice('general.wednesday',1)}}</option>
                                    <option value="thursday"
                                            @if(in_array("thursday",json_decode($role->access_days))) selected @endif>{{trans_choice('general.thursday',1)}}</option>
                                    <option value="friday"
                                            @if(in_array("friday",json_decode($role->access_days))) selected @endif>{{trans_choice('general.friday',1)}}</option>
                                    <option value="saturday"
                                            @if(in_array("saturday",json_decode($role->access_days))) selected @endif>{{trans_choice('general.saturday',1)}}</option>
                                    <option value="sunday"
                                            @if(in_array("sunday",json_decode($role->access_days))) selected @endif>{{trans_choice('general.sunday',1)}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <hr>
                    <h4>{{trans_choice('general.manage',1)}} {{trans_choice('general.permission',2)}}</h4>

                    <div class="col-md-6">
                        <table class="table table-stripped table-hover">
                            @foreach($data as $permission)
                                <tr>
                                    <td>
                                        @if($permission->parent_id==0)
                                            <strong>{{$permission->name}}</strong>
                                        @else
                                            __ {{$permission->name}}
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($permission->description))
                                            <i class="fa fa-info" data-toggle="tooltip"
                                               data-original-title="{!!  $permission->description!!}"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <input @if(array_key_exists($permission->slug,$role->permissions)) checked=""
                                               @endif type="checkbox" data-parent="{{$permission->parent_id}}"
                                               name="permission[]" value="{{$permission->slug}}"
                                               id="{{$permission->id}}"
                                               class="form-control pcheck">
                                        <label class="" for="{{$permission->id}}">

                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right">{{trans_choice('general.save',1)}}</button>
            </div>
        </form>
    </div>
    <script>
        $(document).ready(function () {
            if ($("#time_limit").val() == "0") {
                $("#time_div").hide();
                $("#from_time").removeAttr("required");
                $("#to_time").removeAttr("required");
                $("#access_days").removeAttr("required");

            } else {
                $("#time_div").show();
                $("#from_time").attr("required", "required");
                $("#to_time").attr("required", "required");
                $("#access_days").attr("required", "required");
            }
            $("#time_limit").change(function () {
                if ($("#time_limit").val() == "0") {
                    $("#time_div").hide();
                    $("#from_time").removeAttr("required");
                    $("#to_time").removeAttr("required");
                    $("#access_days").removeAttr("required");

                } else {
                    $("#time_div").show();
                    $("#from_time").attr("required", "required");
                    $("#to_time").attr("required", "required");
                    $("#access_days").attr("required", "required");
                }
            });
            $(".pcheck").on('ifChecked', function (e) {
                if ($(this).attr('data-parent') == 0) {
                    var id = $(this).attr('id');
                    $(":checkbox[data-parent=" + id + "]").iCheck('check');

                }
            });
            $(".pcheck").on('ifUnchecked', function (e) {
                if ($(this).attr('data-parent') == 0) {
                    var id = $(this).attr('id');
                    $(":checkbox[data-parent=" + id + "]").iCheck('uncheck');

                }
            });
        })
    </script>
@endsection
