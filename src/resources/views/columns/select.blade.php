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
@endphp
<td>
@if (isset($column['display_as_link']))
	<a href="{{ $url }}">
	@if ($entry->{$column['entity']})
		{{ $entry->{$column['entity']}->{$column['attribute']} }}
	@endif
	</a>
@else
	@if ($entry->{$column['entity']})
		{{ $entry->{$column['entity']}->{$column['attribute']} }}
	@endif
@endif
</td>
