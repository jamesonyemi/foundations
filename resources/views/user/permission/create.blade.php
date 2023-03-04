@extends('layouts.secure')
@section('title')
    Create Permission
@endsection
@section('content')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
    <div class="panel panel-white">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <h6 class="panel-title">Create Permission</h6>

        </div>
        <form method="post" action="{{url('user/permission/store')}}" class="form">
            {{csrf_field()}}
            <div class="card-body">

                <div class="form-group">
                    <div class="form-line">
                        <label for="type"
                               class="">{{trans_choice('general.type',1)}}</label>
                        <select name="type" class="form-control" id="type">
                            <option value="0">Parent Permission</option>
                            <option value="1">Sub Permission</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="parent">
                    <div class="form-line">
                        <label for="parent_id"
                               class="">Parent</label>
                        <select name="parent_id" class="form-control select2" id="parent_id">
                            <option value="0"></option>
                            @foreach(\App\Models\Permission::where('parent_id', 0)->get() as $permission)
                                <option value="{{$permission->id}}">{{$permission->name}}</option>
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
                               value="{{old('name')}}"
                               required id="name">
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-line">
                        <label for="slug"
                               class="">{{trans_choice('general.slug',1)}}</label>
                        <input type="text" name="slug" class="form-control"
                               placeholder="{{trans_choice('general.slug',1)}}"
                               value="{{old('slug')}}"
                               required id="slug">
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-line">
                        <label for="description"
                               class="">{{trans_choice('general.description',1)}}</label>
                        <textarea name="description" class="form-control"
                                  placeholder="{{trans_choice('general.description',1)}}"
                                  id="description" rows="3">{{old('description')}}</textarea>
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
                } else {
                    $('#parent').show();
                }
            })
        })
    </script>
@endsection
