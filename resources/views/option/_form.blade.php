<div class="panel panel-primary">
    <div class="card-body">
        @if (isset($option))
            {!! Form::model($option, array('url' => $type . '/' . $option->id, 'method' => 'put', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => $type, 'method' => 'post', 'files'=> true)) !!}
        @endif
        <div class="form-group required {{ $errors->has('category') ? 'has-error' : '' }}">
            {!! Form::label('type', trans('option.category'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::select('category', $categories, null, array('id'=>'category', 'class' => 'form-control select2')) !!}
                <span class="help-block">{{ $errors->first('category', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
            {!! Form::label('title', trans('option.title'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('title', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('title', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('company_id') ? 'has-error' : '' }}">
            {!! Form::label('type', trans('option.school'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::select('company_id', $school_lists, null, array('id'=>'company_id', 'class' => 'form-control select2')) !!}
                <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('value') ? 'has-error' : '' }}">
            {!! Form::label('value', trans('option.value'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('value', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('value', ':message') }}</span>
            </div>
        </div>


        <div class="form-group">
            <div class="controls">
                <a href="{{ url($type) }}" class="btn btn-primary btn-sm"><i
                            class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                <button type="submit" class="btn btn-success btn-sm"><i
                            class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
            </div>
        </div>


        {!! Form::close() !!}
    </div>
</div>
