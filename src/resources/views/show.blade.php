@extends('backpack::layout')

@section('header')
<!-- <h1 class="page-title">
	<span class="text-capitalize">{{ $crud->entity_name_plural }}</span>
	<small><small>{{ ucfirst(trans('backpack::crud.preview')).' '.$crud->entity_name }}.</small>
</h1> -->
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
		<li class="active">{{ trans('backpack::crud.preview') }}</li>
	</ul>
</div>
@endsection

@section('content')
<div class="row">
	<!-- THE ACTUAL CONTENT -->
	<div class="col-md-12">
	<!-- Default portlet -->
		<div class="portlet light portlet-fit portlet-datatable bordered">
			<div class="portlet-title py-1 my-0 hidden-print">
				<!-- <span class="pull-right"><a href="javascript: window.print();"><i class="fa fa-print"></i></a></span>
				<h3 class="portlet-title">
					{{ trans('backpack::crud.preview') }}
					<span>{{ $crud->entity_name }}</span>
				</h3> -->
				<div class="caption mr-auto">
					<i class="icon-notebook"></i>
					<span class="caption-subject font-green sbold uppercase">{{ trans('backpack::crud.preview') }}</span>
				</div>
				<div class="actions">
					<div class="btn-group btn-group-devided">
						<a href="javascript: window.print();" class="btn btn-transparent grey btn-circle btn-sm">
						<i class="fa fa-print"></i> Print</a>
						@if ($crud->hasAccess('list'))
						<a href="{{ url($crud->route) }}" class="btn btn-transparent green-jungle btn-circle btn-sm">
						<i class="fa fa-arrow-left fa-fw"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a>
						@endif
					</div>
				</div>
			</div>
			<div class="portlet-body">
				<table class="table table-striped table-bordered table-hover display responsive nowrap" cellspacing="0">
					
				<tbody>
					@foreach ($crud->columns as $column)
						<tr>
							<td>
								<strong>{{ $column['label'] }}</strong>
							</td>
							<td>
								@if (!isset($column['type']))
								@include('crud::columns.text')
								@else
								@if(view()->exists('vendor.backpack.crud.columns.'.$column['type']))
									@include('vendor.backpack.crud.columns.'.$column['type'])
								@else
									@if(view()->exists('crud::columns.'.$column['type']))
									@include('crud::columns.'.$column['type'])
									@else
									@include('crud::columns.text')
									@endif
								@endif
								@endif
							</td>
						</tr>
					@endforeach
					@if ($crud->buttons->where('stack', 'line')->count())
						<tr>
							<td><strong>{{ trans('backpack::crud.actions') }}</strong></td>
							<td>
								@include('crud::inc.button_stack', ['stack' => 'line'])
							</td>
						</tr>
					@endif
					</tbody>
				</table>
			</div><!-- /.portlet-body -->
		</div><!-- /.portlet -->
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
