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

 $column_name = $column['name'];
 $value = $entry->{$column_name};

 if(isset($column['translate']))
  $text = trans("{$column['translate']}.{$column_name}");
 else if(isset($column['translate_config']))
  $text = config("{$column['translate_config']}.{$column_name}");
 else
  $text = (array_key_exists('prefix', $column) ? $column['prefix'] : '').str_limit(strip_tags($value), array_key_exists('limit', $column) ? $column['limit'] : 50, "[...]").(array_key_exists('suffix', $column) ? $column['suffix'] : '');

@endphp
@if (isset($column['display_as_link']) && $column['display_as_link'])
 <td>
  <a href="{{ url("{$crud->route}/{$entry->getKey()}/{$action}") }}">{{ $text }}
  </a>
 </td>
@else
 <td>{{ $text }}</td>
@endif
