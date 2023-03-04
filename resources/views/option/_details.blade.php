<div class="panel panel-primary">
    <div class="card-body">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('option.category')}}</label>

            <div class="controls">
                    {{ $option->category }}
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('option.title')}}</label>

            <div class="controls">
                    {{ $option->title }}
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('option.value')}}</label>

            <div class="controls">
                    {{ $option->value }}
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url($type) }}" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> {{trans('table.close')}}</a>
                @else
                    <a href="{{ url($type) }}" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                @endif
            </div>
        </div>
    </div>
</div>
