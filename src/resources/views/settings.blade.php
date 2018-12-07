@extends('backpack::layout') 

@section('header')
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
		<li class="active">{{ trans('backpack::crud.list') }}</li>
	</ul>
</div>
@endsection 

@section('content')
<!-- Default portlet -->
<div class="row">
	<div class="col-lg-2 col-md-3 col-sm-4">
		<div class="portlet light portlet-fit portlet-datatable bordered">
			<div class="portlet-title py-1 my-0 hidden-print {{ $crud->hasAccess('create')?'with-border':'' }}">
				<div class="caption mr-auto">
					<i class="icon-notebook"></i>
					<span class="caption-subject font-green sbold uppercase">Settings</span>
				</div>
			</div>
			<div class="portlet-body">
				<ul class="nav flex-column">
				@include('partials.navigation-settings')
				</ul>
			</div>
		</div>
	</div>
	<!-- THE ACTUAL CONTENT -->
	<div class="col-lg-10 col-md-9 col-sm-8">
		<div class="portlet light portlet-fit portlet-datatable bordered">
			<div class="portlet-title py-1 my-0 hidden-print {{ $crud->hasAccess('create')?'with-border':'' }}">
				<div class="caption mr-auto">
					<i class="icon-notebook"></i>
					<span class="caption-subject font-green sbold uppercase">{{ $crud->entity_name_plural }}</span>
					<small class="d-none d-lg-inline">{{ trans('backpack::crud.all') }} <span>{{ $crud->entity_name_plural }}</span> {{ trans('backpack::crud.in_the_database') }}.</small>
				</div>
				<div class="actions">
					<div class="btn-group">@include('crud::inc.button_stack', ['stack' => 'top'])</div>
				</div>
			</div>
			{{-- Backpack List Filters --}} @if ($crud->filtersEnabled()) @include('crud::inc.filters_navbar') @endif
			<div class="portlet-body">

				<table id="crudTable" class="table table-striped table-bordered table-hover display responsive nowrap" cellspacing="0">
					<thead>
						<tr>
							{{-- Table columns --}} @foreach ($crud->columns as $column)
							<th data-orderable="{{ var_export($column['orderable'], true) }}" data-priority="{{ $column['priority'] }}">
								{{ $column['label'] }}
							</th>
							@endforeach @if ( $crud->buttons->where('stack', 'line')->count() )
							<th data-orderable="false" data-priority="{{ $crud->getActionsColumnPriority() }}">{{ trans('backpack::crud.actions') }}</th>
							@endif
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<tr>
							{{-- Table columns --}} @foreach ($crud->columns as $column)
							<th>{{ $column['label'] }}</th>
							@endforeach @if ( $crud->buttons->where('stack', 'line')->count() )
							<th>{{ trans('backpack::crud.actions') }}</th>
							@endif
						</tr>
					</tfoot>
				</table>

			</div>
			<!-- /.portlet-body -->
			@include('crud::inc.button_stack', ['stack' => 'bottom'])
		</div>
		<!-- /.portlet -->
	</div>

</div>

@endsection 

@section('after_styles')
<!-- DATA TABLES -->
<link href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.1/css/responsive.bootstrap4.min.css">

<link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/form.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/list.css') }}">

<!-- CRUD LIST CONTENT - crud_list_styles stack -->
@stack('crud_list_styles') 
@endsection

@section('after_scripts') 
@include('crud::inc.datatables_logic')

<script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
<script src="{{ asset('vendor/backpack/crud/js/form.js') }}"></script>
<script src="{{ asset('vendor/backpack/crud/js/list.js') }}"></script>

<!-- CRUD LIST CONTENT - crud_list_scripts stack -->
@stack('crud_list_scripts') 
@endsection
