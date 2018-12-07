@extends('backpack::layout')

@section('header')
<h1 class="page-title">
	<span class="text-capitalize">{{ $crud->entity_name_plural }}</span>
	<small>{{ trans('backpack::crud.edit') }}
		<span>{{ $crud->entity_name_plural }}</span> {{ trans('backpack::crud.in_the_database') }}.</small>
</h1>
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li>
			<a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a>
		</li>
		<i class="fa fa-angle-right"></i>
		<li>
			<a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a>
		</li>
		<i class="fa fa-angle-right"></i>
		<li class="active">{{ trans('backpack::crud.edit') }}</li>
	</ul>
</div>
@endsection

@section('content')
<div class="row">
    <!-- THE ACTUAL CONTENT -->
	<div class="col-md-6  col-md-offset-1">
		<div class="portlet light bordered">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-pencil font-dark"></i>
					<span class="caption-subject font-green sbold uppercase">{{ trans('backpack::crud.edit') }} {{ $crud->entity_name }}</span>
				</div>
				<div class="actions">
					<div class="btn-group btn-group-devided">
						@if ($crud->hasAccess('list'))
						<a href="{{ url($crud->route) }}" class="btn btn-transparent green-jungle btn-circle btn-sm">
						<i class="fa fa-arrow-left fa-fw"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a>
						@endif
					</div>
				</div>
			</div>
			<!-- Default box -->

			@include('crud::inc.grouped_errors')
			<div class="portlet-body form">
				<form class="form-horizontal" method="post"
					action="{{ url($crud->route.'/'.$entry->getKey()) }}"
					@if ($crud->hasUploadFields('update', $entry->getKey()))
					enctype="multipart/form-data"
					@endif
					>
					<div class="form-body">
						@if(view()->exists('vendor.backpack.crud.form_content'))
							@include('vendor.backpack.crud.form_content', ['fields' => $fields, 'action' => 'edit'])
						@else
							@include('crud::form_content', ['fields' => $fields, 'action' => 'edit'])
						@endif
    					<label class="col-md-3 control-label"></label>
    					<div class="col-md-9">
							{!! csrf_field() !!}
							{!! method_field('PUT') !!}
						</div>
					</div><!-- /.box-body -->
					<div class="form-actions right">
						@include('crud::inc.form_save_buttons')
					</div><!-- /.box-footer-->
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
