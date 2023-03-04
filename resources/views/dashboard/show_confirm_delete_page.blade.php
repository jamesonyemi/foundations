@extends('layouts.secure')
@section('content')

    <section class="dashboard">
            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>
                            <a style="color: #eb3a3e !important;

                            " href="{{  url()->previous() }}" class="">
                                <i class="fa fa-backward" aria-hidden="true" aria-label="back button"></i>
                            </a>
                            Are you sure you want to
                            Delete Project # {!! $g->id !!} </h3>
                        Owner: <span class="mt-1">{!! $g->company->title !!}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="publication">
                    <div class="col-1-1">
                        <div class="content">
                            <div class="container">
                                <form method = "POST" action ="{{ route('delete-project') }} " >
                                    @csrf
                                    @if ($g->title)
                                    <fieldset class="form-group row">
                                            <legend class="col-form-legend col-sm-1-12">
                                                {!! $g->title !!}
                                            </legend>
                                        </fieldset>
                                        @else
                                    @endif
                                    <input type="hidden" name="g" value="{!! Crypt::Encrypt($g->id) !!}" >
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <button type="submit" class="btn rejected_button">Delete</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
    </section>


@stop
@section('scripts')


@stop
