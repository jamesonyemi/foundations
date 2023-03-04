@foreach($legalCase->comments as $comment)
    <div class="card card-custom gutter-b">
        <!--begin::Body-->
        <!--begin::Container-->
        <div>
            <!--begin::Header-->
            <div class="d-flex align-items-center pb-4">
                <!--begin::Symbol-->
                <div class="symbol symbol-40 mr-5">
                    <span class="symbol-label">
                        <img src="{{ url(@$comment->employee->user->picture) }}" class="h-75 align-self-end" alt="{{@$comment->employee->user->full_name}}">
                    </span>
                </div>
                <!--end::Symbol-->

                <!--begin::Info-->
                <div class="d-flex flex-column flex-grow-1">
                    <a href="#" class="text-dark-75 text-hover-primary mb-1 font-size-lg font-weight-bolder">{{@$comment->employee->user->full_name}}</a>
                    <span class="text-muted font-weight-bold">{{--Yestarday at 5:06 PM--}} {{$comment->created_at->diffForHumans()}}</span>
                </div>
            </div>
            <div>
                <!--begin::Text-->
                <p class="text-dark-75 font-size-lg font-weight-normal">
                    {{$comment->comment}}

                    {{--<a href="javascript:;" onclick="deletePost({{ $comment->id }})" class="navi-link">
                        <span class="navi-icon"><i class="fa fa-trash text-danger mr-5"></i></span>
                    </a>--}}
                </p>
                <!--begin::Separator-->
                <div class="separator separator-solid mt-5 mb-4"></div>

            </div>
            <!--end::Body-->
        </div>

    </div>

    @endforeach
