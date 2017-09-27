@extends('backpack::layout')

@section('header')
    <section class="content-header">
        <h1>
            {{ trans('backpack::crud.show') }} <span class="text-lowercase">{{ $crud->entity_name }}</span>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
            <li class="active">{{ trans('backpack::crud.show') }}</li>
        </ol>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            @if ($crud->hasAccess('list'))
                <a href="{{ url($crud->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span class="text-lowercase">{{ $crud->entity_name_plural }}</span></a><br><br>
            @endif
        <!-- Default box -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        {{ trans('backpack::crud.show') }}
                        <span class="text-lowercase">{{ $crud->entity_name }}</span>
                    </h3>
                </div>
                <div class="box-body row">
                    @if(view()->exists('vendor.backpack.crud.show.show_content'))
                        @include('vendor.backpack.crud.show.show_content', ['fields' => $fields])
                    @else
                        @include('crud::show.show_content', ['fields' => $fields])
                    @endif
                </div><!-- /.box-body -->
                @if($crud->hasAccess('update'))
                    <div class="box-footer">
                        <div class="form-group">
                            <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-default"><i class="fa fa-edit"></i> {{ trans('backpack::crud.edit') }}</a>
                        </div>
                    </div>
                @endif
            </div><!-- /.box -->
        </div>
    </div>
@endsection


@section('after_styles')
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/show.css') }}">
@endsection

@section('after_scripts')
    <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/show.js') }}"></script>
@endsection
