<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> {{$title}}</div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('pages.title')}}</label>

            <div class="controls">
                @if (isset($page))
                    {{ $page->title }}
                @endif
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="duration">{{trans('pages.content')}}</label>

            <div class="controls">
                @if (isset($page))
                    {{ $page->content }}
                @endif
            </div>
        </div>

        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                @else
                    <a href="javascript:;" onclick="deleteData()" class="btn btn-danger btn-sm" title="Delete Record">
                        <i class="fa fa-trash">{{trans('table.delete')}}</i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
