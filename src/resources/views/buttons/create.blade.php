@if ($crud->hasAccess('create'))
	<a href="{{ url($crud->route.'/create') }}" class="tool-action btn green-jungle btn-circle btn-sm" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-plus"></i> {{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span></a>
@endif