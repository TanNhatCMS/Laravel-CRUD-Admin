{{-- single relationships (1-1, 1-n) --}}
@php
 $action = 'edit';

 if(isset($column['action']))
  $action = $column['action'];

 if($action=='show')
 {
  $action = '';
 }

 if(isset($column['display_as_link']))
 {
  $url = url("{$crud->route}/{$entry->getKey()}/{$action}");

  if(isset($column['link_params']) && isset($column['link_route']))
  {
   $params = collect($column['link_params'])
   ->mapWithKeys(function($connected_model_attribute, $param_key) use ($entry)
   {
    return [ $param_key => $entry->$connected_model_attribute ];
   })->toArray();

   $url = url()->route($column['link_route'], $params);
  }
 }

 $attributes = $crud->getModelAttributeFromRelation($entry, $column['entity'], $column['attribute']);
 $column_attribute = '-';
 if (count($attributes)) {
     $column_attribute = implode(', ', $attributes);
 }
@endphp
<td>
@if (isset($column['display_as_link']))
	<a href="{{ $url }}">{{$column_attribute}}
	</a>
@else
	{{$column_attribute}}
@endif
</td>
