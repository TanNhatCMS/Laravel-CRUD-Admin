{{-- regular object attribute --}}
@php
 $action = 'edit';

 if(isset($column['action']))
  $action = $column['action'];

 if($action=='show')
 {
  $action = '';
 }

 $limit_text = 80;
 if(isset($column['limit_text']))
  $limit_text = $column['limit_text'];

 if(isset($column['translate']))
  $text = trans("{$column['translate']}.{$entry->{$column['name']}}");
 else if(isset($column['translate_config']))
  $text = config("{$column['translate_config']}.{$entry->{$column['name']}}");
 else
  $text = str_limit(strip_tags($entry->{$column['name']}), $limit_text, "[...]");
  
@endphp
@if (isset($column['display_as_link']) && $column['display_as_link'])
 <td>
  <a href="{{ url("{$crud->route}/{$entry->getKey()}/{$action}") }}">{{ $text }}
  </a>
 </td>
@else
 <td>{{ $text }}</td>
@endif
