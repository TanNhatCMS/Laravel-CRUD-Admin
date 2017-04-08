@extends('backpack::layout')

@section('content-header')
    <section class="content-header">
        <h1>
            {{ trans('backpack::crud.preview') }} <span class="text-lowercase">{{ $crud->entity_name }}</span>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
            <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
            <li class="active">{{ trans('backpack::crud.preview') }}</li>
        </ol>
    </section>
@endsection

@section('content')
    @if ($crud->hasAccess('list'))
        <a href="{{ url($crud->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span class="text-lowercase">{{ $crud->entity_name_plural }}</span></a><br><br>
    @endif

    <h1>PACKAGE VIEW</h1>

    <!-- Default box -->
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">
                {{ trans('backpack::crud.preview') }}
                <span class="text-lowercase">{{ $crud->entity_name }}</span>
            </h3>
        </div>
        <div class="box-body">
            @php($fields = $crud->getFields('both'))
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Fields</th>
                    <th>Values</th>
                </tr>
                </thead>
                @foreach($entry->getCasts() as $property => $type)
                    <tr>
                        <td>
                            @if(array_key_exists($property, $fields))
                                {{ $fields[$property]['label'] }}
                            @else
                                {{ strtoupper($property) }}
                            @endif
                        </td>

                        @if(in_array($type, ['int', 'integer', 'real', 'float', 'double']))
                            @if($property !== 'id' && array_key_exists('entity', $fields[$property]))
                                @php($entity = $fields[$property]['entity'])
                                @php($attribute = $fields[$property]['attribute'])
                                <td>{{ $entry->$entity->$attribute }}</td>
                            @else
                                <td>{{ $entry->$property }}</td>
                            @endif
                        @elseif($type === 'string')
                            @if($fields[$property]['type'] === 'simplemde')
                                <td>@markdown($entry->$property)</td>
                            @elseif(in_array($fields[$property]['type'], ['ckeditor', 'summernote', 'tinymce', 'wysiwyg']))
                                <td>{!! $entry->$property !!}</td>
                            @else
                                <td>{{ $entry->$property }}</td>
                            @endif
                        @elseif($type === 'boolean')
                            <td>
                                <span class="badge label-{{ $entry->$property ? 'success' : 'danger' }}">
                                    {{ $entry->$property }}
                                </span>
                            </td>
                        @elseif($type === 'object')
                            {{--TODO--}}
                        @elseif($type === 'array' || $type === 'collection')
                            {{--TODO--}}
                        @elseif($type === 'date' || $type === 'datetime' || $type === 'timestamp')
                            <td>{{ date('Y-m-d H:i:s', strtotime($entry->$property)) }}</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

@endsection


@section('after_styles')
	<link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
	<link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/show.css') }}">
@endsection

@section('after_scripts')
	<script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
	<script src="{{ asset('vendor/backpack/crud/js/show.js') }}"></script>
@endsection