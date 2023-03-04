<div class="modal-header py-5 ">
    <h5 class="modal-title">{{$title}}
        {{--<span class="d-block text-muted font-size-sm">sub datatable for the selected row is loaded from remote data source</span>--}}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="card card-custom gutter-b">
    <div class="card-body">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('level.name') }}</label>
            <div class="controls">
                @if (isset($kpi))
                    {{ $kpi->title }}
                @endif
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="subject">{{trans('level.section')}}</label>
            <div class="controls">
                @if (isset($kpi->kra))
                    {{ $kpi->kra->title }}
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
