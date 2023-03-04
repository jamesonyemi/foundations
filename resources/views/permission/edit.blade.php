@extends('layouts.master')
@section('title')
    Edit Permission
@endsection
@section('current-page')
    Edit Permission
@endsection
@section('content')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
    <div class="panel panel-default">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <h6 class="panel-title">Edit Permission</h6>

        </div>
        <form method="post" action="{{url('user/permission/'.$permission->id.'/update')}}" class="form">
            {{csrf_field()}}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-line">
                                <label for="type"
                                       class="">{{trans_choice('general.type',1)}}</label>
                                <select name="type" class="form-control" id="type">
                                    <option value="0" @if($permission->parent_id=="0") selected @endif>Parent
                                        Permission
                                    </option>
                                    <option value="1" @if($permission->parent_id!="0") selected @endif>Sub Permission
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="parent">
                            <div class="form-line">
                                <label for="parent_id"
                                       class="">Parent</label>
                                <select name="parent_id" class="form-control select2" id="parent_id">
                                    <option value="0" @if($permission->parent_id=="0") selected @endif></option>
                                    @foreach(\App\Models\Permission::where('parent_id', 0)->get() as $key)
                                        <option value="{{$key->id}}"
                                                @if($permission->parent_id==$key->id) selected @endif>{{$key->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-line">
                                <label for="name"
                                       class="">{{trans_choice('general.name',1)}}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{trans_choice('general.name',1)}}"
                                       value="{{$permission->name}}"
                                       required id="name">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-line">
                                <label for="slug"
                                       class="">{{trans_choice('general.slug',1)}}</label>
                                <input type="text" name="slug" class="form-control"
                                       placeholder="{{trans_choice('general.slug',1)}}"
                                       value="{{$permission->slug}}"
                                       required id="slug">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-line">
                                <label for="description"
                                       class="">{{trans_choice('general.description',1)}}</label>
                                <textarea name="description" class="form-control"
                                          placeholder="{{trans_choice('general.description',1)}}"
                                          id="description" rows="3">{{$permission->description}}</textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /.panel-body -->
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary pull-right">Save</button>
            </div>
        </form>
    </div>
    <script>
        $(document).ready(function () {
            if ($('#type').val() == 0) {
                $('#parent').hide();
            } else {
                $('#parent').show();
            }
            $('#type').change(function () {
                if ($('#type').val() == 0) {
                    $('#parent').hide();
                    $('#type').val('0')
                } else {
                    $('#parent').show();
                }
            })
        })
    </script>
@endsection
