<div class="card card-custom card-stretch gutter-b">
    <!--begin::Header-->
   {{-- <div class="card-header border-0 py-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label font-weight-bolder text-dark">{{$title}}</span>
           --}}{{-- <span class="text-muted mt-3 font-weight-bold font-size-sm">More than 400+ new members</span>--}}{{--
        </h3>

    </div>--}}
    <!--end::Header-->

    <!--begin::Body-->
    @if($competencies->count() > 0)
    <div class="card-body pt-0 pb-3">
        <div class="tab-content">
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table table-head-custom table-head-bg table-borderless table-vertical-center">
                    <thead>
                    <tr class="text-left text-uppercase">
                        <th style="min-width: 250px" class="pl-7"><span class="text-dark-75">Competency</span></th>

                    </tr>
                    </thead>
                    <tbody>

                    @foreach( $competencies as $competency)
                    <tr>
                        <td class="pl-0 py-8">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50 symbol-light mr-4">
                                        <span class="symbol-label">
                                            <img src="assets/media/svg/avatars/001-boy.svg" class="h-75 align-self-end" alt="">
                                        </span>
                                </div>
                                <div>
                                    <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary mb-1 font-size-lg">{{$competency->title}}</a>
                                    {{--<span class="text-muted font-weight-bold d-block">HTML, JS, ReactJS</span>--}}
                                </div>
                            </div>
                            <div class="alert alert-danger" style="display:none">
                                <ul>
                                    @foreach ($competency->competency_levels as $level)
                                        <li>{{ $level->title }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>

                        </td>
                     {{--   <td>
                                <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                                    <div class="controls">
                                        {!! Form::text('comment', null, array('class' => 'form-control')) !!}
                                        --}}{{--<span class="help-block">{{ $errors->first('title', ':message') }}</span>--}}{{--
                                    </div>
                                </span>

                        </td>--}}


                    </tr>
                    @endforeach


                    </tbody>
                </table>
            </div>
            <!--end::Table-->
        </div>
    </div>
    @endif
    <!--end::Body-->
</div>



